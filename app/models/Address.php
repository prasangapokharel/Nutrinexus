<?php

namespace App\Models;

use App\Core\Model;

class Address extends Model
{
    protected $table = 'addresses';
    protected $primaryKey = 'id';

    /**
     * Get addresses by user ID
     *
     * @param int $userId
     * @return array
     */
    public function getByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
        return $this->db->query($sql)->bind([$userId])->all();
    }

    /**
     * Get default address for user
     *
     * @param int $userId
     * @return array|false
     */
    public function getDefaultAddress($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND is_default = 1 LIMIT 1";
        return $this->db->query($sql)->bind([$userId])->single();
    }

    /**
     * Get address by ID and user ID
     *
     * @param int $addressId
     * @param int $userId
     * @return array|false
     */
    public function getByIdAndUserId($addressId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND user_id = ?";
        return $this->db->query($sql)->bind([$addressId, $userId])->single();
    }

    /**
     * Create new address
     *
     * @param array $data
     * @return int|false
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (user_id, recipient_name, phone, address_line1, city, state, country, is_default) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['user_id'],
            $data['recipient_name'],
            $data['phone'],
            $data['address_line1'],
            $data['city'],
            $data['state'],
            $data['country'] ?? 'Nepal',
            $data['is_default'] ?? 0
        ];

        if ($this->db->query($sql)->bind($params)->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Update address
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                recipient_name = ?, phone = ?, address_line1 = ?, 
                city = ?, state = ?, country = ?, is_default = ?, 
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $params = [
            $data['recipient_name'],
            $data['phone'],
            $data['address_line1'],
            $data['city'],
            $data['state'],
            $data['country'] ?? 'Nepal',
            $data['is_default'] ?? 0,
            $id
        ];

        return $this->db->query($sql)->bind($params)->execute();
    }

    /**
     * Set address as default
     *
     * @param int $addressId
     * @param int $userId
     * @return bool
     */
    public function setAsDefault($addressId, $userId)
    {
        try {
            $this->db->beginTransaction();
            
            // Reset all addresses for this user
            $sql = "UPDATE {$this->table} SET is_default = 0 WHERE user_id = ?";
            $this->db->query($sql)->bind([$userId])->execute();
            
            // Set the specified address as default
            $sql = "UPDATE {$this->table} SET is_default = 1 WHERE id = ? AND user_id = ?";
            $result = $this->db->query($sql)->bind([$addressId, $userId])->execute();
            
            $this->db->commit();
            return $result;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Unset all default addresses for a user
     *
     * @param int $userId
     * @return bool
     */
    public function unsetDefaultAddresses($userId)
    {
        $sql = "UPDATE {$this->table} SET is_default = 0 WHERE user_id = ?";
        return $this->db->query($sql)->bind([$userId])->execute();
    }

    /**
     * Delete address by user - compatible with parent Model class
     *
     * @param int $addressId
     * @param int $userId
     * @return bool
     */
    public function deleteByUser($addressId, $userId)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? AND user_id = ?";
        return $this->db->query($sql)->bind([$addressId, $userId])->execute();
    }

    /**
     * Check if address exists for user
     *
     * @param int $addressId
     * @param int $userId
     * @return bool
     */
    public function exists($addressId, $userId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE id = ? AND user_id = ?";
        $result = $this->db->query($sql)->bind([$addressId, $userId])->single();
        return $result && $result['count'] > 0;
    }
}
