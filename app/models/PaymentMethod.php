<?php
namespace App\Models;

use App\Core\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';
    protected $primaryKey = 'id';

    /**
     * Get all active payment methods
     *
     * @return array
     */
    public function getAllActive()
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name";
        return $this->db->query($sql)->all();
    }

    /**
     * Get active payment methods (alias for getAllActive)
     *
     * @return array
     */
    public function getActive()
    {
        return $this->getAllActive();
    }

    /**
     * Get payment method by ID
     *
     * @param int $id
     * @return array|false
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->query($sql)->bind([$id])->single();
    }

    /**
     * Check if payment method is active
     *
     * @param int $id
     * @return bool
     */
    public function isActive($id)
    {
        $paymentMethod = $this->getById($id);
        return $paymentMethod && $paymentMethod['is_active'] == 1;
    }

    /**
     * Get payment method by name
     *
     * @param string $name
     * @return array|false
     */
    public function getByName($name)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = ?";
        return $this->db->query($sql)->bind([$name])->single();
    }
}