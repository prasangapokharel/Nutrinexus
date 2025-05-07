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
        // Using 'is_active' column which exists in the payment_methods table
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name";
        return $this->db->query($sql)->all();
    }
}
