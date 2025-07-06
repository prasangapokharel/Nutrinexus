<?php

namespace App\Models;

use App\Core\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';

    /**
     * Get all orders with payment method details
     */
    public function getAllOrders($status = null)
    {
        $sql = "SELECT o.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       u.email as customer_email,
                       pm.name as payment_method
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id";
        
        $params = [];
        
        if ($status) {
            $sql .= " WHERE o.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        return $this->db->query($sql)->bind($params)->all();
    }

    /**
     * Get order by ID with all details including payment method
     */
    public function getOrderById($id)
    {
        $sql = "SELECT o.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       u.email as customer_email,
                       u.phone,
                       pm.name as payment_method,
                       kp.pidx as khalti_pidx,
                       kp.transaction_id as khalti_transaction_id,
                       ep.reference_id as esewa_reference_id,
                       ep.transaction_id as esewa_transaction_id
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                LEFT JOIN khalti_payments kp ON o.id = kp.order_id
                LEFT JOIN esewa_payments ep ON o.id = ep.order_id
                WHERE o.id = ?";
        
        return $this->db->query($sql)->bind([$id])->single();
    }

    /**
     * Get order by invoice number with all details including payment method
     * This is the missing function that was causing the error
     */
    public function getOrderByInvoice($invoice)
    {
        $sql = "SELECT o.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       u.email as customer_email,
                       u.phone,
                       pm.name as payment_method,
                       kp.pidx as khalti_pidx,
                       kp.transaction_id as khalti_transaction_id,
                       ep.reference_id as esewa_reference_id,
                       ep.transaction_id as esewa_transaction_id
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                LEFT JOIN khalti_payments kp ON o.id = kp.order_id
                LEFT JOIN esewa_payments ep ON o.id = ep.order_id
                WHERE o.invoice = ?";
        
        return $this->db->query($sql)->bind([$invoice])->single();
    }

    /**
     * Get order with items for success page
     */
    public function getOrderWithItems($id)
    {
        $order = $this->getOrderById($id);
        
        if ($order) {
            // Get order items
            $sql = "SELECT oi.*, p.product_name, p.price as product_price
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?";
            
            $items = $this->db->query($sql)->bind([$id])->all();
            $order['items'] = $items;
        }
        
        return $order;
    }

    /**
     * Get order with details including user and payment info
     */
    public function getOrderWithDetails($id)
    {
        return $this->getOrderById($id);
    }

    /**
     * Get orders by user ID
     */
    public function getOrdersByUserId($userId)
    {
        $sql = "SELECT o.*, pm.name as payment_method
                FROM {$this->table} o
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                WHERE o.user_id = ?
                ORDER BY o.created_at DESC";
        
        return $this->db->query($sql)->bind([$userId])->all();
    }

    /**
     * Get recent orders for dashboard
     */
    public function getRecentOrders($limit = 5)
    {
        $sql = "SELECT o.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       pm.name as payment_method
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                ORDER BY o.created_at DESC
                LIMIT ?";
        
        return $this->db->query($sql)->bind([$limit])->all();
    }

    /**
     * Update order status
     */
    public function updateOrderStatus($id, $status)
    {
        $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql)->bind([$status, $id])->execute();
    }

    /**
     * Create new order with items - FIXED VERSION
     */
    public function createOrder($orderData, $cartItems)
    {
        try {
            error_log('=== ORDER CREATION START ===');
            error_log('Order data: ' . json_encode($orderData));
            error_log('Cart items count: ' . count($cartItems));
            // Start transaction
            $this->db->beginTransaction();
            // Generate invoice number
            $invoice = 'NTX' . date('Ymd') . rand(1000, 9999);
            
            // Prepare full address
            $fullAddress = $orderData['address_line1'];
            if (!empty($orderData['address_line2'])) {
                $fullAddress .= ', ' . $orderData['address_line2'];
            }
            $fullAddress .= ', ' . $orderData['city'] . ', ' . $orderData['state'] . ' ' . $orderData['postal_code'];

            // Insert order - MATCHING ACTUAL DATABASE SCHEMA
            $sql = "INSERT INTO {$this->table} (
                        invoice, user_id, customer_name, contact_no, payment_method_id, 
                        status, address, order_notes, transaction_id, total_amount, 
                        delivery_fee, payment_screenshot, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $result = $this->db->query($sql)->bind([
                $invoice,
                $orderData['user_id'],
                $orderData['recipient_name'],
                $orderData['phone'],
                $orderData['payment_method_id'],
                'pending',
                $fullAddress,
                $orderData['order_notes'] ?? '',
                $orderData['transaction_id'] ?? '',
                $orderData['final_amount'],
                0, // delivery_fee
                $orderData['payment_screenshot'] ?? ''
            ])->execute();

            if (!$result) {
                throw new \Exception('Failed to create order');
            }

            $orderId = $this->db->lastInsertId();
            error_log('Order created with ID: ' . $orderId);

            // Insert order items - MATCHING ACTUAL DATABASE SCHEMA
            $itemSql = "INSERT INTO order_items (order_id, product_id, quantity, price, total, invoice) 
                        VALUES (?, ?, ?, ?, ?, ?)";

            foreach ($cartItems as $item) {
                $product = $item['product'];
                $quantity = $item['quantity'];
                $price = $product['price'];
                $total = $price * $quantity;

                $itemResult = $this->db->query($itemSql)->bind([
                    $orderId,
                    $product['id'],
                    $quantity,
                    $price,
                    $total,
                    $invoice
                ])->execute();

                if (!$itemResult) {
                    throw new \Exception('Failed to create order item for product: ' . $product['product_name']);
                }

                error_log('Order item created for product: ' . $product['product_name']);
            }

            // Commit transaction
            $this->db->commit();
            error_log('=== ORDER CREATION SUCCESS ===');
            
            return $orderId;

        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->rollback();
            error_log('=== ORDER CREATION FAILED ===');
            error_log('Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            throw $e;
        }
    }

    /**
     * Get order count
     */
    public function getOrderCount()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->query($sql)->single();
        return $result['count'] ?? 0;
    }

    /**
     * Get count by status
     */
    public function getCountByStatus($status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = ?";
        $result = $this->db->query($sql)->bind([$status])->single();
        return $result['count'] ?? 0;
    }

    /**
     * Get total sales/revenue
     */
    public function getTotalSales()
    {
        $sql = "SELECT SUM(total_amount) as total FROM {$this->table} WHERE status = 'paid'";
        $result = $this->db->query($sql)->single();
        return $result['total'] ?? 0;
    }

    /**
     * Get total revenue
     */
    public function getTotalRevenue()
    {
        return $this->getTotalSales();
    }

    /**
     * Get total count
     */
    public function getTotalCount()
    {
        return $this->getOrderCount();
    }

    /**
     * Get all orders with details (alias for getAllOrders)
     */
    public function getAllWithDetails()
    {
        return $this->getAllOrders();
    }

    /**
     * Get orders by user ID (alias)
     */
    public function getByUserId($userId)
    {
        return $this->getOrdersByUserId($userId);
    }

    /**
     * Update order
     */
    public function updateOrder($id, $data)
    {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        
        return $this->db->query($sql)->bind($values)->execute();
    }

    /**
     * Delete order
     */
    public function deleteOrder($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->query($sql)->bind([$id])->execute();
    }

    /**
     * Get orders with pagination
     */
    public function getOrdersWithPagination($page = 1, $limit = 20, $status = null)
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT o.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       u.email as customer_email,
                       pm.name as payment_method
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id";
        
        $params = [];
        
        if ($status) {
            $sql .= " WHERE o.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->query($sql)->bind($params)->all();
    }

    /**
     * Search orders
     */
    public function searchOrders($searchTerm, $status = null)
    {
        $sql = "SELECT o.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       u.email as customer_email,
                       pm.name as payment_method
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                WHERE (o.invoice LIKE ? OR o.customer_name LIKE ? OR u.email LIKE ?)";
        
        $params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
        
        if ($status) {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        return $this->db->query($sql)->bind($params)->all();
    }

    /**
     * Get monthly sales data
     */
    public function getMonthlySales($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        
        $sql = "SELECT 
                    MONTH(created_at) as month,
                    SUM(total_amount) as total_sales,
                    COUNT(*) as order_count
                FROM {$this->table} 
                WHERE YEAR(created_at) = ? AND status = 'paid'
                GROUP BY MONTH(created_at)
                ORDER BY MONTH(created_at)";
        
        return $this->db->query($sql)->bind([$year])->all();
    }

    /**
     * Get order statistics
     */
    public function getOrderStatistics()
    {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    SUM(total_amount) as total_amount
                FROM {$this->table}
                GROUP BY status";
        
        return $this->db->query($sql)->all();
    }

    /**
 * Get orders by creation date
 *
 * @param string $date
 * @return array
 */
public function getOrdersByDate(string $date): array
{
    $sql = "SELECT o.*, u.id as user_id, u.phone as contact_no
            FROM {$this->table} o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE DATE(o.created_at) >= ?
            ORDER BY o.created_at DESC";
    return $this->db->query($sql)->bind([$date])->all();
}

/**
 * Get the latest product purchased by a user
 *
 * @param int $userId
 * @return array|null
 */
public function getLatestProductByUser(int $userId): ?array
{
    $sql = "SELECT p.*
            FROM {$this->table} o
            INNER JOIN order_items oi ON o.id = oi.order_id
            INNER JOIN products p ON oi.product_id = p.id
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
            LIMIT 1";
    return $this->db->query($sql)->bind([$userId])->single() ?: null;
}

/**
 * Get the latest product for a specific order
 *
 * @param int $orderId
 * @return array|null
 */
public function getLatestProduct(int $orderId): ?array
{
    $sql = "SELECT p.*
            FROM order_items oi
            INNER JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
            ORDER BY oi.id DESC
            LIMIT 1";
    return $this->db->query($sql)->bind([$orderId])->single() ?: null;
}
}