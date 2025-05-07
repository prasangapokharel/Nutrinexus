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
            // Start transaction
            $this->orderModel->beginTransaction();
            
            try {
                // Update order status
                $result = $this->orderModel->update($orderId, [
                    'status' => 'cancelled',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                if (!$result) {
                    throw new \Exception('Failed to update order status');
                }
                
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
                
                // Reverse referral earnings if order was paid
                if ($order['status'] === 'paid') {
                    $this->reverseReferralEarnings($orderId);
                }
                
                // Clear related caches
                $this->clearOrderCaches($userId, $orderId);
                
                // Commit transaction
                $this->orderModel->commit();
                
                $this->setFlash('success', 'Order cancelled successfully');
                
            } catch (\Exception $e) {
                // Rollback transaction
                $this->orderModel->rollback();
                
                // Log error
                error_log('Order cancellation error: ' . $e->getMessage());
                
                $this->setFlash('error', 'Failed to cancel order: ' . $e->getMessage());
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
        
        // Start transaction
        $this->orderModel->beginTransaction();
        
        try {
            // Update order status
            $result = $this->orderModel->update($orderId, [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$result) {
                throw new \Exception('Failed to update order status');
            }
            
            // Process referral earnings if status changed to paid
            if ($status === 'paid' && $order['status'] !== 'paid') {
                $this->processReferralEarnings($orderId);
            }
            
            // Reverse referral earnings if status changed from paid to cancelled
            if ($status === 'cancelled' && $order['status'] === 'paid') {
                $this->reverseReferralEarnings($orderId);
            }
            
            // Clear all related caches
            if ($order) {
                $this->clearOrderCaches($order['user_id'], $orderId);
            }
            
            // Commit transaction
            $this->orderModel->commit();
            
            $this->setFlash('success', 'Order status updated successfully');
            
        } catch (\Exception $e) {
            // Rollback transaction
            $this->orderModel->rollback();
            
            // Log error
            error_log('Order status update error: ' . $e->getMessage());
            
            $this->setFlash('error', 'Failed to update order status: ' . $e->getMessage());
        }
        
        $this->redirect('admin/orders/view/' . $orderId);
    }

    /**
     * Process referral earnings for an order
     *
     * @param int $orderId
     * @return void
     */
    private function processReferralEarnings($orderId)
    {
        // Get order details
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            error_log('Process referral earnings: Order not found - ID: ' . $orderId);
            return;
        }
        
        // Log order details after retrieving the order
        error_log("Processing referral earnings for Order ID: {$orderId}, Status: {$order['status']}, Amount: {$order['total_amount']}");
        
        // Only process for paid orders
        if ($order['status'] !== 'paid') {
            error_log('Process referral earnings: Order not paid - ID: ' . $orderId . ', Status: ' . $order['status']);
            return;
        }

        // Get user who placed the order
        $user = $this->userModel->find($order['user_id']);
        if (!$user) {
            error_log('Process referral earnings: User not found - ID: ' . $order['user_id']);
            return;
        }
        
        // Check if user was referred by someone
        if (empty($user['referred_by'])) {
            error_log('Process referral earnings: User has no referrer - User ID: ' . $user['id']);
            return;
        }
        
        $referrerId = $user['referred_by'];
        
        // Check if referrer exists
        $referrer = $this->userModel->find($referrerId);
        if (!$referrer) {
            error_log('Process referral earnings: Referrer not found - ID: ' . $referrerId);
            return;
        }

        // Check if referral earning already exists for this order
        $existingEarning = $this->referralEarningModel->findByOrderId($orderId);
        if ($existingEarning) {
            error_log('Process referral earnings: Earning already exists - Order ID: ' . $orderId);
            return;
        }

        // Calculate commission (5% of total_amount, excluding delivery fee)
        $commission = ($order['total_amount'] - $order['delivery_fee']) * 0.05;
        
        // Round to 2 decimal places
        $commission = round($commission, 2);
        
        if ($commission <= 0) {
            error_log('Process referral earnings: Commission is zero or negative - Order ID: ' . $orderId);
            return;
        }

        try {
            // Create referral earning record
            $earningData = [
                'user_id' => $referrerId,
                'order_id' => $orderId,
                'amount' => $commission,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $earningId = $this->referralEarningModel->create($earningData);
            
            if (!$earningId) {
                throw new \Exception('Failed to create referral earning record');
            }
            
            // Update referrer's balance
            $currentEarnings = (float)($referrer['referral_earnings'] ?? 0);
            $newEarnings = $currentEarnings + $commission;

            $result = $this->userModel->update($referrerId, [
                'referral_earnings' => $newEarnings,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$result) {
                throw new \Exception('Failed to update referrer balance');
            }

            // Log the update for debugging
            error_log("Updated referrer ID: {$referrerId} earnings from {$currentEarnings} to {$newEarnings}");
            
            // Record transaction
            $transactionData = [
                'user_id' => $referrerId,
                'amount' => $commission,
                'type' => 'referral_earning',
                'reference_id' => $earningId,
                'reference_type' => 'referral_earning',
                'description' => 'Referral commission from order #' . $orderId,
                'balance_after' => $newEarnings,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $transactionId = $this->transactionModel->create($transactionData);
            
            if (!$transactionId) {
                throw new \Exception('Failed to create transaction record');
            }
            
            // Create notification for referrer
            $notificationData = [
                'user_id' => $referrerId,
                'title' => 'New Referral Commission',
                'message' => 'You earned ₹' . number_format($commission, 2) . ' commission from a referral purchase.',
                'type' => 'referral_earning',
                'reference_id' => $earningId,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->notificationModel->create($notificationData);
            
            // Log success
            error_log('Referral earnings processed successfully - Order ID: ' . $orderId . ', Referrer ID: ' . $referrerId . ', Amount: ' . $commission);
            
        } catch (\Exception $e) {
            // Log error
            error_log('Error processing referral earnings: ' . $e->getMessage());
            throw $e; // Re-throw to be caught by the calling method's transaction
        }
    }

    /**
     * Reverse referral earnings for a cancelled order
     *
     * @param int $orderId
     * @return void
     */
    private function reverseReferralEarnings($orderId)
    {
        $referralEarning = $this->referralEarningModel->findByOrderId($orderId);
        
        if (!$referralEarning || $referralEarning['status'] === 'cancelled') {
            error_log('Reverse referral earnings: No active earning found - Order ID: ' . $orderId);
            return;
        }
        
        try {
            // Update earning status to cancelled
            $result = $this->referralEarningModel->update($referralEarning['id'], [
                'status' => 'cancelled',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$result) {
                throw new \Exception('Failed to update referral earning status');
            }
            
            // Get referrer
            $referrerId = $referralEarning['user_id'];
            $referrer = $this->userModel->find($referrerId);
            
            if (!$referrer) {
                throw new \Exception('Referrer not found');
            }
            
            // Deduct amount from referrer's balance
            $currentEarnings = (float)($referrer['referral_earnings'] ?? 0);
            $newEarnings = max(0, $currentEarnings - $referralEarning['amount']);
            
            $result = $this->userModel->update($referrerId, [
                'referral_earnings' => $newEarnings,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$result) {
                throw new \Exception('Failed to update referrer balance');
            }
            
            // Record transaction
            $transactionData = [
                'user_id' => $referrerId,
                'amount' => -$referralEarning['amount'], // Negative amount for deduction
                'type' => 'referral_cancelled',
                'reference_id' => $referralEarning['id'],
                'reference_type' => 'referral_earning',
                'description' => 'Referral commission reversed due to order cancellation',
                'balance_after' => $newEarnings,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $transactionId = $this->transactionModel->create($transactionData);
            
            if (!$transactionId) {
                throw new \Exception('Failed to create transaction record');
            }
            
            // Create notification
            $notificationData = [
                'user_id' => $referrerId,
                'title' => 'Referral Commission Cancelled',
                'message' => '₹' . number_format($referralEarning['amount'], 2) . ' commission has been reversed due to order cancellation.',
                'type' => 'referral_cancelled',
                'reference_id' => $referralEarning['id'],
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->notificationModel->create($notificationData);
            
            // Log success
            error_log('Referral earnings reversed successfully - Order ID: ' . $orderId . ', Referrer ID: ' . $referrerId . ', Amount: ' . $referralEarning['amount']);
            
        } catch (\Exception $e) {
            // Log error
            error_log('Error reversing referral earnings: ' . $e->getMessage());
            throw $e; // Re-throw to be caught by the calling method's transaction
        }
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