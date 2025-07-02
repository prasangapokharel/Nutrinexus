<?php

namespace App\Models;

use App\Core\Model;

class EsewaPayment extends Model
{
    protected $table = 'esewa_payments';
    protected $primaryKey = 'id';

    /**
     * Create eSewa payment record
     */
    public function createPayment($data)
    {
        $sql = "INSERT INTO {$this->table} (user_id, order_id, amount, reference_id, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        
        return $this->db->query($sql)->bind([
            $data['user_id'],
            $data['order_id'],
            $data['amount'],
            $data['reference_id'] ?? null,
            $data['status'] ?? 'pending'
        ])->execute();
    }

    /**
     * Update payment status by order ID
     */
    public function updateStatusByOrderId($orderId, $status, $transactionId = null)
    {
        $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW()";
        $params = [$status];
        
        if ($transactionId) {
            $sql .= ", transaction_id = ?";
            $params[] = $transactionId;
        }
        
        $sql .= " WHERE order_id = ?";
        $params[] = $orderId;
        
        return $this->db->query($sql)->bind($params)->execute();
    }

    /**
     * Get payment by order ID
     */
    public function getByOrderId($orderId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE order_id = ?";
        return $this->db->query($sql)->bind([$orderId])->single();
    }
}
