<?php
namespace App\Models;

use App\Core\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';
    protected $primaryKey = 'id';

    /**
     * Get all items for an order
     *
     * @param int $orderId
     * @return array
     */
    public function getByOrderId($orderId)
    {
        $sql = "SELECT oi.*, p.product_name, p.image 
                FROM {$this->table} oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?";
        return $this->db->query($sql)->bind([$orderId])->all();
    }

    /**
     * Create a new order item
     *
     * @param array $data
     * @return int|false
     */
    public function createOrderItem($data)
    {
        return $this->create($data);
    }

    /**
     * Get best selling products
     *
     * @param int $limit
     * @return array
     */
    public function getBestSellingProducts($limit = 4)
    {
        $sql = "SELECT p.*, SUM(oi.quantity) as total_sold 
                FROM {$this->table} oi
                JOIN products p ON oi.product_id = p.id
                GROUP BY oi.product_id
                ORDER BY total_sold DESC
                LIMIT ?";
        return $this->db->query($sql)->bind([$limit])->all();
    }

    /**
     * Get total sales for a product
     *
     * @param int $productId
     * @return int
     */
    public function getTotalSalesForProduct($productId)
    {
        $sql = "SELECT SUM(quantity) as total_sold 
                FROM {$this->table} 
                WHERE product_id = ?";
        $result = $this->db->query($sql)->bind([$productId])->single();
        return $result ? (int)$result['total_sold'] : 0;
    }

    /**
     * Get total revenue
     *
     * @return float
     */
    public function getTotalRevenue()
    {
        $sql = "SELECT SUM(total) as total_revenue FROM {$this->table}";
        $result = $this->db->query($sql)->single();
        return $result ? (float)$result['total_revenue'] : 0;
    }
}
