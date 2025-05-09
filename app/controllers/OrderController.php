<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\ReferralEarning;
use App\Models\Transaction;
use App\Models\Notification;
use App\Models\Setting;

/**
 * Order Controller
 * Handles order management
 */
class OrderController extends Controller
{
    private $productModel;
    private $orderModel;
    private $orderItemModel;
    private $userModel;
    private $referralEarningModel;
    private $transactionModel;
    private $notificationModel;
    private $settingModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->userModel = new User();
        $this->referralEarningModel = new ReferralEarning();
        $this->transactionModel = new Transaction();
        $this->notificationModel = new Notification();
        $this->settingModel = new Setting();
    }

    /**
     * Display user's orders
     *
     * @return void
     */
    public function index()
    {
        $this->requireLogin();
        
        $userId = Session::get('user_id');
        $orders = $this->orderModel->getOrdersByUserId($userId);
        
        $this->view('orders/index', [
            'orders' => $orders,
            'title' => 'My Orders'
        ]);
    }

    /**
     * View order details
     *
     * @param int $id
     * @return void
     */
    public function viewOrder($id = null)
    {
        $this->requireLogin();
        
        if (!$id) {
            $this->redirect('orders');
            return;
        }
        
        $userId = Session::get('user_id');
        $order = $this->orderModel->getOrderById($id);
        
        if (!$order || $order['user_id'] != $userId) {
            $this->setFlash('error', 'Order not found.');
            $this->redirect('orders');
            return;
        }
        
        $orderItems = $this->orderItemModel->getByOrderId($id);
        
        $this->view('orders/view', [
            'order' => $order,
            'orderItems' => $orderItems,
            'title' => 'Order Details'
        ]);
    }

    /**
     * Track order
     *
     * @return void
     */
    public function track()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $invoice = $this->post('invoice');
            
            if (empty($invoice)) {
                $this->setFlash('error', 'Please enter an order number.');
                $this->redirect('orders/track');
                return;
            }
            
            $order = $this->orderModel->getOrderByInvoice($invoice);
            
            if (!$order) {
                $this->setFlash('error', 'Order not found.');
                $this->redirect('orders/track');
                return;
            }
            
            $orderItems = $this->orderItemModel->getByOrderId($order['id']);
            
            $this->view('orders/track-result', [
                'order' => $order,
                'orderItems' => $orderItems,
                'title' => 'Order Tracking'
            ]);
        } else {
            $this->view('orders/track', [
                'title' => 'Track Order'
            ]);
        }
    }

    /**
     * Cancel order
     *
     * @param int $id
     * @return void
     */
    public function cancel($id = null)
    {
        $this->requireLogin();
        
        if (!$id) {
            $this->redirect('orders');
            return;
        }
        
        $userId = Session::get('user_id');
        $order = $this->orderModel->getOrderById($id);
        
        if (!$order || $order['user_id'] != $userId) {
            $this->setFlash('error', 'Order not found.');
            $this->redirect('orders');
            return;
        }
        
        // Only allow cancellation of pending or unpaid orders
        if (!in_array($order['status'], ['pending', 'unpaid'])) {
            $this->setFlash('error', 'This order cannot be cancelled.');
            $this->redirect('orders/view/' . $id);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Start transaction
            $this->orderModel->beginTransaction();
            
            try {
                // Update order status
                $result = $this->orderModel->update($id, [
                    'status' => 'cancelled',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                if (!$result) {
                    throw new \Exception('Failed to cancel order.');
                }
                
                // Restore product quantities
                $orderItems = $this->orderItemModel->getByOrderId($id);
                
                foreach ($orderItems as $item) {
                    $this->productModel->updateQuantity($item['product_id'], $item['stock_quantity']);
                }
                
                // Commit transaction
                $this->orderModel->commit();
                
                $this->setFlash('success', 'Order cancelled successfully.');
                $this->redirect('orders/view/' . $id);
                
            } catch (\Exception $e) {
                // Rollback transaction
                $this->orderModel->rollback();
                
                // Log error
                error_log('Order cancellation error: ' . $e->getMessage());
                
                // Set flash message
                $this->setFlash('error', 'Failed to cancel order. Please try again.');
                
                // Redirect back to order details
                $this->redirect('orders/view/' . $id);
            }
        } else {
            $this->view('orders/cancel', [
                'order' => $order,
                'title' => 'Cancel Order'
            ]);
        }
    }

    /**
     * Update order status (admin only)
     *
     * @param int $id
     * @return void
     */
    public function updateStatus($id = null)
    {
        $this->requireAdmin();
        
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/orders');
            return;
        }
        
        $status = $this->post('status');
        
        if (!in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'unpaid', 'paid'])) {
            $this->setFlash('error', 'Invalid status.');
            $this->redirect('admin/orders');
            return;
        }
        
        // Start transaction
        $this->orderModel->beginTransaction();
        
        try {
            // Get current order status
            $order = $this->orderModel->getOrderById($id);
            
            if (!$order) {
                throw new \Exception('Order not found.');
            }
            
            $oldStatus = $order['status'];
            
            // Update order status
            $result = $this->orderModel->update($id, [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$result) {
                throw new \Exception('Failed to update order status.');
            }
            
            // If status changed to paid, process referral earnings
            if ($status === 'paid' && $oldStatus !== 'paid') {
                $this->processReferralEarnings($id);
            }
            
            // If status changed from paid to cancelled, reverse referral earnings
            if ($status === 'cancelled' && $oldStatus === 'paid') {
                $this->reverseReferralEarnings($id);
            }
            
            // Commit transaction
            $this->orderModel->commit();
            
            $this->setFlash('success', 'Order status updated successfully.');
            
        } catch (\Exception $e) {
            // Rollback transaction
            $this->orderModel->rollback();
            
            // Log error
            error_log('Order status update error: ' . $e->getMessage());
            
            // Set flash message
            $this->setFlash('error', 'Failed to update order status. ' . $e->getMessage());
        }
        
        $this->redirect('admin/orders');
    }

    /**
     * Process referral earnings for an order
     *
     * @param int $orderId
     * @return bool
     */
    public function processReferralEarnings($orderId)
    {
        // Get order details
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            error_log("Process referral earnings: Order not found - ID: $orderId");
            return false;
        }
        
        // Only process for paid orders
        if ($order['status'] !== 'paid') {
            error_log("Process referral earnings: Order not paid - ID: $orderId, Status: {$order['status']}");
            return false;
        }
        
        // Get user who placed the order
        $user = $this->userModel->find($order['user_id']);
        if (!$user) {
            error_log("Process referral earnings: User not found - ID: {$order['user_id']}");
            return false;
        }
        
        // Log user details for debugging
        error_log("Order placed by User ID: {$user['id']}, Name: {$user['first_name']} {$user['last_name']}, Referred by: " . ($user['referred_by'] ?? 'None'));
        
        // Check if user was referred by someone
        if (empty($user['referred_by'])) {
            error_log("Process referral earnings: User has no referrer - User ID: {$user['id']}");
            return false;
        }
        
        $referrerId = $user['referred_by'];
        
        // Get referrer details
        $referrer = $this->userModel->find($referrerId);
        if (!$referrer) {
            error_log("Process referral earnings: Referrer not found - ID: $referrerId");
            return false;
        }
        
        // Log referrer details for debugging
        error_log("Referrer found - ID: $referrerId, Name: {$referrer['first_name']} {$referrer['last_name']}");
        
        // Check if referral earning already exists for this order
        $existingEarning = $this->referralEarningModel->findByOrderId($orderId);
        if ($existingEarning) {
            error_log("Process referral earnings: Earning already exists - Order ID: $orderId, Earning ID: {$existingEarning['id']}");
            return false;
        }
        
        // Get commission rate from settings or use default 5%
        $commissionRate = 5;
        if (method_exists($this->settingModel, 'get')) {
            $commissionRate = $this->settingModel->get('commission_rate', 5);
        }
        
        // Calculate commission (commission_rate% of total_amount, excluding delivery fee)
        $deliveryFee = isset($order['delivery_fee']) ? (float)$order['delivery_fee'] : 0;
        $orderTotal = (float)$order['total_amount'];
        $commission = ($orderTotal - $deliveryFee) * ($commissionRate / 100);
        
        // Round to 2 decimal places
        $commission = round($commission, 2);
        
        // Log commission calculation for debugging
        error_log("Commission calculation: Order Total: $orderTotal, Delivery Fee: $deliveryFee, Commission Rate: $commissionRate%, Final Commission: $commission");
        
        if ($commission <= 0) {
            error_log("Process referral earnings: Commission is zero or negative - Order ID: $orderId");
            return false;
        }
        
        // Start transaction
        $this->referralEarningModel->beginTransaction();
        
        try {
            // Create referral earning record with paid status
            $earningData = [
                'user_id' => $referrerId,
                'order_id' => $orderId,
                'amount' => $commission,
                'status' => 'paid', // Set to paid immediately
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Log the data being inserted
            error_log("Inserting referral earning: " . json_encode($earningData));
            
            $earningId = $this->referralEarningModel->create($earningData);
            
            if (!$earningId) {
                throw new \Exception("Failed to create referral earning record for order: $orderId");
            }
            
            error_log("Created referral earning record - ID: $earningId");
            
            // Update referrer's balance
            $currentEarnings = (float)($referrer['referral_earnings'] ?? 0);
            $newEarnings = $currentEarnings + $commission;
            
            // Log the balance update
            error_log("Updating referrer balance - Current: $currentEarnings, New: $newEarnings");
            
            $updateData = [
                'referral_earnings' => $newEarnings,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->userModel->update($referrerId, $updateData);
            
            if (!$result) {
                throw new \Exception("Failed to update referrer balance for user: $referrerId");
            }
            
            error_log("Updated referrer ID: $referrerId earnings from $currentEarnings to $newEarnings");
            
            // Record transaction
            $transactionData = [
                'user_id' => $referrerId,
                'amount' => $commission,
                'type' => 'referral_earning',
                'reference_id' => $earningId,
                'reference_type' => 'referral_earning',
                'description' => "Referral commission from order #{$order['invoice']}",
                'balance_after' => $newEarnings,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $transactionId = $this->transactionModel->create($transactionData);
            
            if (!$transactionId) {
                error_log("Failed to create transaction record for referral earning: $earningId");
            } else {
                error_log("Created transaction record - ID: $transactionId");
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
            
            $notificationId = $this->notificationModel->create($notificationData);
            
            if (!$notificationId) {
                error_log("Failed to create notification for referral earning: $earningId");
            } else {
                error_log("Created notification - ID: $notificationId");
            }
            
            // Commit transaction
            $this->referralEarningModel->commit();
            
            error_log("Referral earnings processed successfully - Order ID: $orderId, Referrer ID: $referrerId, Amount: $commission");
            return true;
            
        } catch (\Exception $e) {
            // Rollback transaction
            $this->referralEarningModel->rollback();
            
            // Log error
            error_log('Error processing referral earnings: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reverse referral earnings for a cancelled order
     *
     * @param int $orderId
     * @return bool
     */
    public function reverseReferralEarnings($orderId)
    {
        $referralEarning = $this->referralEarningModel->findByOrderId($orderId);
        
        if (!$referralEarning) {
            error_log("Reverse referral earnings: No earning found - Order ID: $orderId");
            return false;
        }
        
        if ($referralEarning['status'] === 'cancelled') {
            error_log("Reverse referral earnings: Earning already cancelled - Order ID: $orderId");
            return false;
        }
        
        // Start transaction
        $this->referralEarningModel->beginTransaction();
        
        try {
            // Update earning status to cancelled
            $result = $this->referralEarningModel->update($referralEarning['id'], [
                'status' => 'cancelled',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$result) {
                throw new \Exception("Failed to update referral earning status for ID: {$referralEarning['id']}");
            }
            
            error_log("Updated referral earning status to cancelled - ID: {$referralEarning['id']}");
            
            // Only deduct from referrer's balance if the earning was previously paid
            if ($referralEarning['status'] === 'paid') {
                // Deduct amount from referrer's balance
                $referrer = $this->userModel->find($referralEarning['user_id']);
                
                if (!$referrer) {
                    throw new \Exception("Reverse referral earnings: Referrer not found - ID: {$referralEarning['user_id']}");
                }
                
                $currentEarnings = (float)($referrer['referral_earnings'] ?? 0);
                $newEarnings = max(0, $currentEarnings - $referralEarning['amount']);
                
                $result = $this->userModel->update($referralEarning['user_id'], [
                    'referral_earnings' => $newEarnings,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                if (!$result) {
                    throw new \Exception("Failed to update referrer balance for user: {$referralEarning['user_id']}");
                }
                
                error_log("Updated referrer ID: {$referralEarning['user_id']} earnings from $currentEarnings to $newEarnings (deduction)");
                
                // Record transaction
                $transactionData = [
                    'user_id' => $referralEarning['user_id'],
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
                    error_log("Failed to create transaction record for reversed referral: {$referralEarning['id']}");
                } else {
                    error_log("Created transaction record for reversal - ID: $transactionId");
                }
                
                // Create notification
                $notificationData = [
                    'user_id' => $referralEarning['user_id'],
                    'title' => 'Referral Commission Cancelled',
                    'message' => '₹' . number_format($referralEarning['amount'], 2) . ' commission has been reversed due to order cancellation.',
                    'type' => 'referral_cancelled',
                    'reference_id' => $referralEarning['id'],
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $notificationId = $this->notificationModel->create($notificationData);
                
                if (!$notificationId) {
                    error_log("Failed to create notification for reversed referral: {$referralEarning['id']}");
                } else {
                    error_log("Created notification for reversal - ID: $notificationId");
                }
            }
            
            // Commit transaction
            $this->referralEarningModel->commit();
            
            error_log("Referral earnings reversed successfully - Order ID: $orderId, Referrer ID: {$referralEarning['user_id']}, Amount: {$referralEarning['amount']}");
            return true;
            
        } catch (\Exception $e) {
            // Rollback transaction
            $this->referralEarningModel->rollback();
            
            // Log error
            error_log('Error reversing referral earnings: ' . $e->getMessage());
            return false;
        }
    }
}