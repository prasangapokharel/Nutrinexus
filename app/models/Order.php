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
        $this->db->beginTransaction();
        
        try {
            // First, get the current order to check its status
            $order = $this->getOrderById($orderId);
            
            if (!$order) {
                $this->db->rollback();
                return false;
            }
            
            // Update the order status
            $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?";
            $result = $this->db->query($sql)->bind([$status, $orderId])->execute();
            
            if (!$result) {
                $this->db->rollback();
                return false;
            }
            
            // Process referral earnings based on status change
            if ($status === 'paid' && $order['status'] !== 'paid') {
                // Order is now paid, process referral earnings
                $this->processReferralEarnings($orderId);
            } elseif ($status === 'cancelled' && $order['status'] === 'paid') {
                // Order was paid but now cancelled, reverse referral earnings
                $this->reverseReferralEarnings($orderId);
            }
            
            // Commit transaction
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            // Log the error
            error_log('Error updating order status: ' . $e->getMessage());
            
            // Rollback transaction
            $this->db->rollback();
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
        $order = $this->getOrderById($orderId);
        if (!$order) {
            return;
        }
        
        $userModel = new User();
        $user = $userModel->find($order['user_id']);
        
        if (!$user || empty($user['referred_by'])) {
            return;
        }
        
        $referrerId = $user['referred_by'];
        $referralEarningModel = new ReferralEarning();
        
        // Check if referral earning already exists for this order
        if ($referralEarningModel->findByOrderId($orderId)) {
            return;
        }
        
        // Calculate commission (5% of total_amount, excluding delivery fee)
        $commission = ($order['total_amount'] - ($order['delivery_fee'] ?? 0)) * 0.05;
        
        // Create referral earning record
        $earningData = [
            'user_id' => $referrerId,
            'order_id' => $orderId,
            'amount' => $commission,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $earningId = $referralEarningModel->create($earningData);
        
        if ($earningId) {
            // Update referrer's balance
            $this->updateReferrerBalance($referrerId, $commission);
            
            // Record transaction
            $this->recordReferralTransaction($referrerId, $commission, $earningId, $orderId);
            
            // Create notification for referrer
            $this->createReferralNotification($referrerId, $commission, $earningId);
        }
    }
    
    /**
     * Update referrer's balance
     *
     * @param int $referrerId
     * @param float $amount
     * @return bool
     */
    private function updateReferrerBalance($referrerId, $amount)
    {
        $userModel = new User();
        $referrer = $userModel->find($referrerId);
        
        if (!$referrer) {
            return false;
        }
        
        $currentEarnings = (float)($referrer['referral_earnings'] ?? 0);
        $newEarnings = $currentEarnings + $amount;
        
        return $userModel->update($referrerId, ['referral_earnings' => $newEarnings]);
    }
    
    /**
     * Record referral transaction
     *
     * @param int $userId
     * @param float $amount
     * @param int $earningId
     * @param int $orderId
     * @return void
     */
    private function recordReferralTransaction($userId, $amount, $earningId, $orderId)
    {
        $transactionModel = new Transaction();
        
        $userModel = new User();
        $user = $userModel->find($userId);
        $currentBalance = (float)($user['referral_earnings'] ?? 0);
        
        $transactionData = [
            'user_id' => $userId,
            'amount' => $amount,
            'type' => 'referral_earning',
            'reference_id' => $earningId,
            'reference_type' => 'referral_earning',
            'description' => 'Referral commission from order #' . $orderId,
            'balance_after' => $currentBalance,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $transactionModel->create($transactionData);
    }
    
    /**
     * Create notification for referrer
     *
     * @param int $userId
     * @param float $amount
     * @param int $earningId
     * @return void
     */
    private function createReferralNotification($userId, $amount, $earningId)
    {
        $notificationModel = new Notification();
        
        $notificationData = [
            'user_id' => $userId,
            'title' => 'New Referral Commission',
            'message' => 'You earned ₹' . number_format($amount, 2) . ' commission from a referral purchase.',
            'type' => 'referral_earning',
            'reference_id' => $earningId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $notificationModel->create($notificationData);
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
        
        if (!$earning || $earning['status'] === 'cancelled') {
            return;
        }
        
        // Update earning status to cancelled
        $referralEarningModel->update($earning['id'], [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Deduct amount from user's balance
        $userModel = new User();
        $user = $userModel->find($earning['user_id']);
        
        if ($user) {
            $currentEarnings = (float)($user['referral_earnings'] ?? 0);
            $newEarnings = max(0, $currentEarnings - $earning['amount']);
            $userModel->update($earning['user_id'], ['referral_earnings' => $newEarnings]);
            
            // Record transaction
            $transactionModel = new Transaction();
            $transactionData = [
                'user_id' => $earning['user_id'],
                'amount' => -$earning['amount'],
                'type' => 'referral_cancelled',
                'reference_id' => $earning['id'],
                'reference_type' => 'referral_earning',
                'description' => 'Referral commission reversed due to order cancellation',
                'balance_after' => $newEarnings,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $transactionModel->create($transactionData);
            
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
            $notificationModel->create($notificationData);
        }
    }
}
