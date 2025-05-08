<?php
namespace App\Models;

use App\Core\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    
    /**
     * Generate a unique invoice number
     *
     * @return string
     */
    public function generateInvoiceNumber()
    {
        $prefix = 'NN';
        $date = date('Ymd');
        $random = mt_rand(1000, 9999);
        
        return $prefix . $date . $random;
    }
    
    /**
     * Get order by ID
     *
     * @param int $id
     * @return array|false
     */
    public function getOrderById($id)
    {
        $sql = "SELECT o.*, pm.name as payment_method_name 
                FROM {$this->table} o
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                WHERE o.id = ?";
        return $this->db->query($sql)->bind([$id])->single();
    }
    
    /**
     * Get orders by user ID
     *
     * @param int $userId
     * @return array
     */
    public function getOrdersByUserId($userId)
    {
        $sql = "SELECT o.*, pm.name as payment_method_name 
                FROM {$this->table} o
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                WHERE o.user_id = ?
                ORDER BY o.created_at DESC";
        return $this->db->query($sql)->bind([$userId])->all();
    }
    
    /**
     * Get order by invoice number
     *
     * @param string $invoice
     * @return array|false
     */
    public function getOrderByInvoice($invoice)
    {
        $sql = "SELECT o.*, pm.name as payment_method_name 
                FROM {$this->table} o
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                WHERE o.invoice = ?";
        return $this->db->query($sql)->bind([$invoice])->single();
    }
    
    /**
     * Get all orders with optional status filter
     *
     * @param string|null $status
     * @return array
     */
    public function getAllOrders($status = null)
    {
        $sql = "SELECT o.*, pm.name as payment_method_name, u.first_name, u.last_name, u.email
                FROM {$this->table} o
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                LEFT JOIN users u ON o.user_id = u.id";
        
        $params = [];
        
        if ($status) {
            $sql .= " WHERE o.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        return $this->db->query($sql)->bind($params)->all();
    }
    
    /**
     * Get recent orders
     *
     * @param int $limit
     * @return array
     */
    public function getRecentOrders($limit = 5)
    {
        $sql = "SELECT o.*, pm.name as payment_method_name, u.first_name, u.last_name
                FROM {$this->table} o
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC
                LIMIT ?";
        return $this->db->query($sql)->bind([$limit])->all();
    }
    
    /**
     * Get order count
     *
     * @return int
     */
    public function getOrderCount()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->query($sql)->single();
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Get total sales
     *
     * @return float
     */
    public function getTotalSales()
    {
        $sql = "SELECT SUM(total_amount) as total FROM {$this->table} WHERE status = 'paid'";
        $result = $this->db->query($sql)->single();
        return $result ? (float)$result['total'] : 0;
    }
    
    /**
     * Update order status and process referral if needed
     *
     * @param int $orderId
     * @param string $status
     * @return bool
     */
    public function updateStatusAndProcessReferral($orderId, $status)
    {
        // Start transaction
        $this->beginTransaction();
        
        try {
            // First, get the current order to check its status
            $order = $this->getOrderById($orderId);
            
            if (!$order) {
                error_log("Order not found: $orderId");
                $this->rollback();
                return false;
            }
            
            // Update the order status
            $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?";
            $result = $this->db->query($sql)->bind([$status, $orderId])->execute();
            
            if (!$result) {
                error_log("Failed to update order status: $orderId to $status");
                $this->rollback();
                return false;
            }
            
            // Process referral earnings based on status change
            if ($status === 'paid' && $order['status'] !== 'paid') {
                // Order is now paid, process referral earnings
                $this->processReferralEarnings($orderId);
                error_log("Processed referral earnings for order: $orderId");
            } elseif ($status === 'cancelled' && $order['status'] === 'paid') {
                // Order was paid but now cancelled, reverse referral earnings
                $this->reverseReferralEarnings($orderId);
                error_log("Reversed referral earnings for order: $orderId");
            }
            
            // Commit transaction
            $this->commit();
            return true;
        } catch (\Exception $e) {
            // Log the error
            error_log('Error updating order status: ' . $e->getMessage());
            
            // Rollback transaction
            $this->rollback();
            return false;
        }
    }
    
    /**
     * Process referral earnings for a paid order
     *
     * @param int $orderId
     * @return void
     */
    private function processReferralEarnings($orderId)
    {
        // Get order details
        $order = $this->getOrderById($orderId);
        if (!$order) {
            error_log("Process referral earnings: Order not found - ID: $orderId");
            return;
        }
        
        // Log order details for debugging
        error_log("Processing referral for Order ID: $orderId, Status: {$order['status']}, Amount: {$order['total_amount']}");
        
        // Get user who placed the order
        $userModel = new User();
        $user = $userModel->find($order['user_id']);
        
        if (!$user) {
            error_log("Process referral earnings: User not found - ID: {$order['user_id']}");
            return;
        }
        
        // Log user details for debugging
        error_log("Order placed by User ID: {$user['id']}, Name: {$user['first_name']} {$user['last_name']}, Referred by: " . ($user['referred_by'] ?? 'None'));
        
        // Check if user was referred by someone
        if (empty($user['referred_by'])) {
            error_log("Process referral earnings: User has no referrer - User ID: {$user['id']}");
            return;
        }
        
        $referrerId = $user['referred_by'];
        
        // Get referrer details
        $referrer = $userModel->find($referrerId);
        if (!$referrer) {
            error_log("Process referral earnings: Referrer not found - ID: $referrerId");
            return;
        }
        
        // Log referrer details for debugging
        error_log("Referrer found - ID: $referrerId, Name: {$referrer['first_name']} {$referrer['last_name']}");
        
        // Check if referral earning already exists for this order
        $referralEarningModel = new ReferralEarning();
        $existingEarning = $referralEarningModel->findByOrderId($orderId);
        
        if ($existingEarning) {
            error_log("Process referral earnings: Earning already exists - Order ID: $orderId, Earning ID: {$existingEarning['id']}");
            return;
        }
        
        // Calculate commission (5% of total_amount, excluding delivery fee)
        $deliveryFee = isset($order['delivery_fee']) ? (float)$order['delivery_fee'] : 0;
        $orderTotal = (float)$order['total_amount'];
        $commission = ($orderTotal - $deliveryFee) * 0.05;
        
        // Round to 2 decimal places
        $commission = round($commission, 2);
        
        // Log commission calculation for debugging
        error_log("Commission calculation: Order Total: $orderTotal, Delivery Fee: $deliveryFee, Commission Rate: 5%, Final Commission: $commission");
        
        if ($commission <= 0) {
            error_log("Process referral earnings: Commission is zero or negative - Order ID: $orderId");
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
            
            // Log the data being inserted
            error_log("Inserting referral earning: " . json_encode($earningData));
            
            $earningId = $referralEarningModel->create($earningData);
            
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
            
            $result = $userModel->update($referrerId, $updateData);
            
            if (!$result) {
                throw new \Exception("Failed to update referrer balance for user: $referrerId");
            }
            
            error_log("Updated referrer ID: $referrerId earnings from $currentEarnings to $newEarnings");
            
            // Record transaction
            $transactionModel = new Transaction();
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
            
            $transactionId = $transactionModel->create($transactionData);
            
            if (!$transactionId) {
                error_log("Failed to create transaction record for referral earning: $earningId");
            } else {
                error_log("Created transaction record - ID: $transactionId");
            }
            
            // Create notification for referrer
            $notificationModel = new Notification();
            $notificationData = [
                'user_id' => $referrerId,
                'title' => 'New Referral Commission',
                'message' => 'You earned ₹' . number_format($commission, 2) . ' commission from a referral purchase.',
                'type' => 'referral_earning',
                'reference_id' => $earningId,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $notificationId = $notificationModel->create($notificationData);
            
            if (!$notificationId) {
                error_log("Failed to create notification for referral earning: $earningId");
            } else {
                error_log("Created notification - ID: $notificationId");
            }
            
            error_log("Referral earnings processed successfully - Order ID: $orderId, Referrer ID: $referrerId, Amount: $commission");
            
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
        $referralEarningModel = new ReferralEarning();
        $earning = $referralEarningModel->findByOrderId($orderId);
        
        if (!$earning) {
            error_log("Reverse referral earnings: No earning found - Order ID: $orderId");
            return;
        }
        
        if ($earning['status'] === 'cancelled') {
            error_log("Reverse referral earnings: Earning already cancelled - Order ID: $orderId");
            return;
        }
        
        try {
            // Update earning status to cancelled
            $result = $referralEarningModel->update($earning['id'], [
                'status' => 'cancelled',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$result) {
                throw new \Exception("Failed to update referral earning status for ID: {$earning['id']}");
            }
            
            error_log("Updated referral earning status to cancelled - ID: {$earning['id']}");
            
            // Deduct amount from referrer's balance
            $userModel = new User();
            $referrer = $userModel->find($earning['user_id']);
            
            if (!$referrer) {
                throw new \Exception("Reverse referral earnings: Referrer not found - ID: {$earning['user_id']}");
            }
            
            $currentEarnings = (float)($referrer['referral_earnings'] ?? 0);
            $newEarnings = max(0, $currentEarnings - $earning['amount']);
            
            $result = $userModel->update($earning['user_id'], [
                'referral_earnings' => $newEarnings,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$result) {
                throw new \Exception("Failed to update referrer balance for user: {$earning['user_id']}");
            }
            
            error_log("Updated referrer ID: {$earning['user_id']} earnings from $currentEarnings to $newEarnings (deduction)");
            
            // Record transaction
            $transactionModel = new Transaction();
            $transactionData = [
                'user_id' => $earning['user_id'],
                'amount' => -$earning['amount'], // Negative amount for deduction
                'type' => 'referral_cancelled',
                'reference_id' => $earning['id'],
                'reference_type' => 'referral_earning',
                'description' => 'Referral commission reversed due to order cancellation',
                'balance_after' => $newEarnings,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $transactionId = $transactionModel->create($transactionData);
            
            if (!$transactionId) {
                error_log("Failed to create transaction record for reversed referral: {$earning['id']}");
            } else {
                error_log("Created transaction record for reversal - ID: $transactionId");
            }
            
            // Create notification
            $notificationModel = new Notification();
            $notificationData = [
                'user_id' => $earning['user_id'],
                'title' => 'Referral Commission Cancelled',
                'message' => '₹' . number_format($earning['amount'], 2) . ' commission has been reversed due to order cancellation.',
                'type' => 'referral_cancelled',
                'reference_id' => $earning['id'],
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $notificationId = $notificationModel->create($notificationData);
            
            if (!$notificationId) {
                error_log("Failed to create notification for reversed referral: {$earning['id']}");
            } else {
                error_log("Created notification for reversal - ID: $notificationId");
            }
            
            error_log("Referral earnings reversed successfully - Order ID: $orderId, Referrer ID: {$earning['user_id']}, Amount: {$earning['amount']}");
            
        } catch (\Exception $e) {
            // Log error
            error_log('Error reversing referral earnings: ' . $e->getMessage());
            throw $e; // Re-throw to be caught by the calling method's transaction
        }
    }
    
    /**
     * Process all pending referrals for paid orders
     * This can be used to fix missing referral earnings
     *
     * @return array Statistics about processed referrals
     */
    public function processAllPendingReferrals()
    {
        $stats = [
            'processed' => 0,
            'skipped' => 0,
            'failed' => 0,
            'total' => 0
        ];
        
        // Get all paid orders
        $sql = "SELECT id FROM {$this->table} WHERE status = 'paid'";
        $paidOrders = $this->db->query($sql)->all();
        
        $stats['total'] = count($paidOrders);
        
        $referralEarningModel = new ReferralEarning();
        
        foreach ($paidOrders as $order) {
            $orderId = $order['id'];
            
            // Check if referral earning already exists
            $existingEarning = $referralEarningModel->findByOrderId($orderId);
            
            if ($existingEarning) {
                $stats['skipped']++;
                error_log("Skipping order $orderId - Referral earning already exists");
                continue;
            }
            
            try {
                // Process referral earnings for this order
                $this->processReferralEarnings($orderId);
                $stats['processed']++;
                error_log("Successfully processed referral for order $orderId");
            } catch (\Exception $e) {
                $stats['failed']++;
                error_log("Failed to process referral for order $orderId: " . $e->getMessage());
            }
        }
        
        return $stats;
    }
    
    /**
     * Fix referral earnings for a specific user's orders
     *
     * @param int $userId The user ID whose orders need to be processed
     * @return array Statistics about processed referrals
     */
    public function fixReferralsForUser($userId)
    {
        $stats = [
            'processed' => 0,
            'skipped' => 0,
            'failed' => 0,
            'total' => 0
        ];
        
        // Get all paid orders for this user
        $sql = "SELECT id FROM {$this->table} WHERE user_id = ? AND status = 'paid'";
        $paidOrders = $this->db->query($sql)->bind([$userId])->all();
        
        $stats['total'] = count($paidOrders);
        
        $referralEarningModel = new ReferralEarning();
        
        foreach ($paidOrders as $order) {
            $orderId = $order['id'];
            
            // Check if referral earning already exists
            $existingEarning = $referralEarningModel->findByOrderId($orderId);
            
            if ($existingEarning) {
                $stats['skipped']++;
                error_log("Skipping order $orderId - Referral earning already exists");
                continue;
            }
            
            try {
                // Process referral earnings for this order
                $this->processReferralEarnings($orderId);
                $stats['processed']++;
                error_log("Successfully processed referral for order $orderId");
            } catch (\Exception $e) {
                $stats['failed']++;
                error_log("Failed to process referral for order $orderId: " . $e->getMessage());
            }
        }
        
        return $stats;
    }
}