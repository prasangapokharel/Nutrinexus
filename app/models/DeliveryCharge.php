<?php

namespace App\Models;

use App\Core\Model;

class DeliveryCharge extends Model
{
    protected $table = 'delivery_charges';
    protected $primaryKey = 'id';

    /**
     * Get all delivery charges
     *
     * @return array
     */
    public function getAllCharges()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY location_name ASC";
        return $this->db->query($sql)->all();
    }

    /**
     * Get delivery charge by location name
     *
     * @param string $location
     * @return array|false
     */
    public function getChargeByLocation($location)
    {
        $sql = "SELECT * FROM {$this->table} WHERE location_name = ?";
        return $this->db->query($sql)->bind([$location])->single();
    }

    /**
     * Get free delivery charge
     *
     * @return array|false
     */
    public function getFreeDeliveryCharge()
    {
        $sql = "SELECT * FROM {$this->table} WHERE location_name = 'Free'";
        return $this->db->query($sql)->single();
    }
}
