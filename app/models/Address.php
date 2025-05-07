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
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY is_default DESC";
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
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND is_default = 1";
        return $this->db->query($sql)->bind([$userId])->single();
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
}
