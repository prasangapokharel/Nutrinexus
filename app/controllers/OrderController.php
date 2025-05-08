<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ReferralEarning;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Notification;
use App\Helpers\CacheHelper;

class OrderController extends Controller
{
    private $orderModel;
    private $orderItemModel;
    private $productModel;
    private $userModel;
    private $referralEarningModel;
    private $transactionModel;
    private $notificationModel;
    private $cache;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->productModel = new Product();
        $this->userModel = new User();
        $this->referralEarningModel = new ReferralEarning();
        $this->transactionModel = new Transaction();
        $this->notificationModel = new Notification();
        
        // Initialize cache
        $this->cache = CacheHelper::getInstance();
        
        // Check if user is logged in
        $this->requireLogin();
    }

    /**
     * Display user's orders
     */
    public function index()
    {
        $userId = Session::get('user_id');
        
        // Generate cache key
        $cacheKey = $this->cache->generateKey('user_orders', ['user_id' => $userId]);
        
        // Try to get from cache
        $viewData = $this->cache->get($cacheKey);
        
        if ($viewData === null) {
            $orders = $this->orderModel->getOrdersByUserId($userId);
            
            $viewData = [
                'orders' => $orders,
                'title' => 'My Orders',
                'cached_at' => date('Y-m-d H:i:s')
            ];
            
            // Store in cache for 15 minutes
            $this->cache->set($cacheKey, $viewData, 900);
        }
        
        $this->view('orders/index', $viewData);
    }

    /**
     * Display order details
     * Renamed from view to viewOrder to avoid conflict with parent class
     */
    public function viewOrder($orderId = null)
    {
        if (!$orderId) {
            $this->redirect('orders');
        }
        
        $userId = Session::get('user_id');
        
        // Generate cache key
        $cacheKey = $this->cache->generateKey('order_details', [
            'user_id' => $userId,
            'order_id' => $orderId
        ]);
        
        // Try to get from cache
        $viewData = $this->cache->get($cacheKey);
        
        if ($viewData === null) {
            $order = $this->orderModel->getOrderById($orderId);
            
            // Check if order belongs to user
            if (!$order || ($order['user_id'] ?? 0) != $userId) {
                $this->redirect('orders');
            }
            
            $orderItems = $this->orderItemModel->getByOrderId($orderId);
            
            $viewData = [
                'order' => $order,
                'orderItems' => $orderItems,
                'title' => 'Order Details',
                'cached_at' => date('Y-m-d H:i:s')
            ];
            
            // Store in cache for 30 minutes
            $this->cache->set($cacheKey, $viewData, 1800);
        }
        
        $this->view('orders/view', $viewData);
    }

    /**
     * Cancel order
     */
    public function cancel($orderId = null)
    {
        if (!$orderId) {
            $this->redirect('orders');
        }
        
        $userId = Session::get('user_id');
        $order = $this->orderModel->getOrderById($orderId);
        
        // Check if order belongs to user and can be cancelled
        if (!$order || ($order['user_id'] ?? 0) != $userId || $order['status'] !== 'unpaid') {
            $this->setFlash('error', 'Order cannot be cancelled');
            $this->redirect('orders');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Update order status using the model method that handles referrals
            $result = $this->orderModel->updateStatusAndProcessReferral($orderId, 'cancelled');
            
            if ($result) {
                // Get order items
                $orderItems = $this->orderItemModel->getByOrderId($orderId);
                
                // Restore product quantities
                foreach ($orderItems as $item) {
                    $product = $this->productModel->find($item['product_id']);
                    if ($product) {
                        $newQuantity = $product['stock_quantity'] + $item['quantity'];
                        $this->productModel->updateQuantity($item['product_id'], $newQuantity);
                    }
                }
                
                // Clear related caches
                $this->clearOrderCaches($userId, $orderId);
                
                $this->setFlash('success', 'Order cancelled successfully');
            } else {
                $this->setFlash('error', 'Failed to cancel order');
            }
            
            $this->redirect('orders');
        } else {
            $this->view('orders/cancel', [
                'order' => $order,
                'title' => 'Cancel Order'
            ]);
        }
    }

    /**
     * Track order
     */
    public function track()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $invoice = trim($_POST['invoice'] ?? '');
            
            if (empty($invoice)) {
                $this->setFlash('error', 'Please enter an order number');
                $this->view('orders/track', [
                    'title' => 'Track Order'
                ]);
                return;
            }
            
            // Generate cache key
            $cacheKey = $this->cache->generateKey('track_order', ['invoice' => $invoice]);
            
            // Try to get from cache
            $viewData = $this->cache->get($cacheKey);
            
            if ($viewData === null) {
                $order = $this->orderModel->getOrderByInvoice($invoice);
                
                if (!$order) {
                    $this->setFlash('error', 'Order not found');
                    $this->view('orders/track', [
                        'invoice' => $invoice,
                        'title' => 'Track Order'
                    ]);
                    return;
                }
                
                $orderItems = $this->orderItemModel->getByOrderId($order['id']);
                
                $viewData = [
                    'order' => $order,
                    'orderItems' => $orderItems,
                    'title' => 'Order Tracking',
                    'cached_at' => date('Y-m-d H:i:s')
                ];
                
                // Store in cache for 5 minutes (short time as tracking info changes frequently)
                $this->cache->set($cacheKey, $viewData, 300);
            }
            
            $this->view('orders/track_result', $viewData);
        } else {
            $this->view('orders/track', [
                'title' => 'Track Order'
            ]);
        }
    }

    /**
     * Update order status (admin only)
     */
    public function updateStatus($orderId = null)
    {
        // Check if user is admin
        if (!Session::has('user_id') || Session::get('user_role') !== 'admin') {
            $this->setFlash('error', 'You do not have permission to perform this action');
            $this->redirect('admin/login');
        }
        
        if (!$orderId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/orders');
        }
        
        $status = $_POST['status'] ?? '';
        
        if (empty($status)) {
            $this->setFlash('error', 'Status is required');
            $this->redirect('admin/orders/view/' . $orderId);
        }
        
        // Get current order
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            $this->setFlash('error', 'Order not found');
            $this->redirect('admin/orders');
        }
        
        // Update order status using the model method that handles referrals
        $result = $this->orderModel->updateStatusAndProcessReferral($orderId, $status);
        
        if ($result) {
            // Clear all related caches
            if ($order) {
                $this->clearOrderCaches($order['user_id'], $orderId);
            }
            
            $this->setFlash('success', 'Order status updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update order status');
        }
        
        $this->redirect('admin/orders/view/' . $orderId);
    }

    /**
     * Clear order-related caches
     *
     * @param int $userId
     * @param int $orderId
     * @return void
     */
    private function clearOrderCaches($userId, $orderId)
    {
        // Clear user's orders list cache
        $this->cache->delete($this->cache->generateKey('user_orders', ['user_id' => $userId]));
        
        // Clear specific order details cache
        $this->cache->delete($this->cache->generateKey('order_details', [
            'user_id' => $userId,
            'order_id' => $orderId
        ]));
        
        // Clear order tracking cache
        $order = $this->orderModel->getOrderById($orderId);
        if ($order) {
            $this->cache->delete($this->cache->generateKey('track_order', ['invoice' => $order['invoice']]));
        }
    }
}