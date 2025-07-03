<?php

namespace App\Models;

use App\Core\Model;

class Withdrawal extends Model
{
    protected $table = 'withdrawals';
    protected $primaryKey = 'id';
    
    /**
     * Get all withdrawals with user details
     *
     * @return array
     */
    public function getAllWithUserDetails()
    {
        $sql = "SELECT w.*, u.username, u.email, u.referral_earnings
                FROM {$this->table} w
                JOIN users u ON w.user_id = u.id
                ORDER BY w.created_at DESC";
        return $this->db->query($sql)->all();
    }
    
    /**
     * Get withdrawal with user details by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getWithUserDetails($id)
    {
        $sql = "SELECT w.*, u.username, u.email, u.first_name, u.last_name, u.referral_earnings, u.phone
                FROM {$this->table} w
                JOIN users u ON w.user_id = u.id
                WHERE w.id = ?";
        return $this->db->query($sql)->bind([$id])->single();
    }
    
    /**
     * Get withdrawals by user ID
     *
     * @param int $userId
     * @return array
     */
    public function getByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC";
        return $this->db->query($sql)->bind([$userId])->all();
    }
    
    /**
     * Get recent withdrawals by user ID
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentByUserId($userId, $limit = 5)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->query($sql)->bind([$userId, $limit])->all();
    }
    
    /**
     * Get user withdrawal statistics
     *
     * @param int $userId
     * @return array
     */
    public function getUserStats($userId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_count,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_completed_amount,
                    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as total_pending_amount,
                    AVG(CASE WHEN status = 'completed' THEN amount ELSE NULL END) as avg_withdrawal_amount
                FROM {$this->table} 
                WHERE user_id = ?";
        
        $result = $this->db->query($sql)->bind([$userId])->single();
        
        return $result ?: [
            'total_requests' => 0,
            'pending_count' => 0,
            'processing_count' => 0,
            'completed_count' => 0,
            'rejected_count' => 0,
            'total_completed_amount' => 0,
            'total_pending_amount' => 0,
            'avg_withdrawal_amount' => 0
        ];
    }
    
    /**
     * Get pending withdrawals count
     *
     * @return int
     */
    public function getPendingCount()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'pending'";
        $result = $this->db->query($sql)->single();
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Get total pending withdrawal amount
     *
     * @return float
     */
    public function getPendingTotal()
    {
        $sql = "SELECT SUM(amount) as total FROM {$this->table} WHERE status = 'pending'";
        $result = $this->db->query($sql)->single();
        return $result ? (float)$result['total'] : 0;
    }
    
    /**
     * Get total pending withdrawal amount for a specific user
     *
     * @param int $userId
     * @return float
     */
    public function getPendingTotalByUserId($userId)
    {
        $sql = "SELECT SUM(amount) as total FROM {$this->table} WHERE user_id = ? AND status = 'pending'";
        $result = $this->db->query($sql)->bind([$userId])->single();
        return $result && $result['total'] ? (float)$result['total'] : 0;
    }
    
    /**
     * Get total completed withdrawal amount for a specific user
     *
     * @param int $userId
     * @return float
     */
    public function getCompletedTotalByUserId($userId)
    {
        $sql = "SELECT SUM(amount) as total FROM {$this->table} WHERE user_id = ? AND status = 'completed'";
        $result = $this->db->query($sql)->bind([$userId])->single();
        return $result && $result['total'] ? (float)$result['total'] : 0;
    }
}
