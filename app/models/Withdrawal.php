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
