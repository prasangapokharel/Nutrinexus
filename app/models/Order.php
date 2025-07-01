<?php
namespace App\Models;

use App\Core\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    
    // Flag to track transaction state
    private $transactionActive = false;
    
    /**
     * Create a new order with order items - FIXED TO MATCH DATABASE SCHEMA
     *
     * @param array $orderData Order details
     * @param array $cartItems Cart items to be added as order items
     * @return int|false Order ID on success, false on failure
     */
    public function createOrder($orderData, $cartItems)
    {
        // Start transaction
        $this->beginTransaction();
        
        try {
            error_log('=== ORDER CREATION START ===');
            error_log('Order data: ' . json_encode($orderData));
            error_log('Cart items count: ' . count($cartItems));
            
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();
            
            // Build address string from components
            $addressParts = [
                $orderData['address_line1'],
                $orderData['address_line2'] ?? '',
                $orderData['city'],
                $orderData['state'],
                $orderData['country'] ?? 'Nepal'
            ];
            $fullAddress = implode(', ', array_filter($addressParts));
            
            // Prepare order data for insertion - MATCHING EXISTING DATABASE SCHEMA
            $orderInsertData = [
                'invoice' => $invoiceNumber,
                'user_id' => $orderData['user_id'],
                'customer_name' => $orderData['recipient_name'], // Maps to customer_name
                'contact_no' => $orderData['phone'], // Maps to contact_no
                'payment_method_id' => $orderData['payment_method_id'],
                'status' => $orderData['order_status'] ?? 'pending',
                'address' => $fullAddress, // Single address field
                'order_notes' => $orderData['order_notes'] ?? '',
                'transaction_id' => $orderData['transaction_id'] ?? null,
                'total_amount' => $orderData['final_amount'], // Use final_amount as total_amount
                'delivery_fee' => 0.00, // Default delivery fee
                'payment_screenshot' => $orderData['payment_screenshot'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            error_log('Inserting order with data: ' . json_encode($orderInsertData));
            
            // Build SQL for order insertion
            $sql = "INSERT INTO {$this->table} (
                invoice, user_id, customer_name, contact_no, payment_method_id, 
                status, address, order_notes, transaction_id, total_amount, 
                delivery_fee, payment_screenshot, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $result = $this->db->query($sql)->bind([
                $orderInsertData['invoice'],
                $orderInsertData['user_id'],
                $orderInsertData['customer_name'],
                $orderInsertData['contact_no'],
                $orderInsertData['payment_method_id'],
                $orderInsertData['status'],
                $orderInsertData['address'],
                $orderInsertData['order_notes'],
                $orderInsertData['transaction_id'],
                $orderInsertData['total_amount'],
                $orderInsertData['delivery_fee'],
                $orderInsertData['payment_screenshot'],
                $orderInsertData['created_at'],
                $orderInsertData['updated_at']
            ])->execute();
            
            if (!$result) {
                throw new \Exception('Failed to create order record');
            }
            
            // Get the inserted order ID
            $orderId = $this->db->lastInsertId();
            
            if (!$orderId) {
                throw new \Exception('Failed to get order ID after insertion');
            }
            
            error_log('Order created with ID: ' . $orderId);
            
            // Insert order items - MATCHING EXISTING SCHEMA
            $orderItemsInserted = 0;
            foreach ($cartItems as $item) {
                $orderItemData = [
                    'order_id' => $orderId,
                    'product_id' => $item['product']['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['product']['price'],
                    'total' => $item['subtotal'],
                    'invoice' => $invoiceNumber
                ];
                
                error_log('Inserting order item: ' . json_encode($orderItemData));
                
                $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, total, invoice) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                
                $result = $this->db->query($sql)->bind([
                    $orderItemData['order_id'],
                    $orderItemData['product_id'],
                    $orderItemData['quantity'],
                    $orderItemData['price'],
                    $orderItemData['total'],
                    $orderItemData['invoice']
                ])->execute();
                
                if (!$result) {
                    throw new \Exception('Failed to create order item for product ID: ' . $item['product']['id']);
                }
                
                $orderItemsInserted++;
                error_log('Order item inserted for product ID: ' . $item['product']['id']);
            }
            
            error_log('Total order items inserted: ' . $orderItemsInserted);
            
            // Update product stock (if stock_quantity field exists)
            foreach ($cartItems as $item) {
                $sql = "UPDATE products SET stock_quantity = GREATEST(0, stock_quantity - ?) WHERE id = ?";
                $result = $this->db->query($sql)->bind([
                    $item['quantity'],
                    $item['product']['id']
                ])->execute();
                
                if (!$result) {
                    error_log('Warning: Could not update stock for product ID: ' . $item['product']['id']);
                    // Don't throw exception for stock update failure, just log it
                }
            }
            
            // Record coupon usage if coupon was applied
            if (!empty($orderData['coupon_code'])) {
                // Check if coupon_usage table exists and has the right structure
                $sql = "INSERT INTO coupon_usage (coupon_id, user_id, order_id, used_at) 
                        SELECT c.id, ?, ?, NOW() 
                        FROM coupons c 
                        WHERE c.code = ?";
                
                $result = $this->db->query($sql)->bind([
                    $orderData['user_id'],
                    $orderId,
                    $orderData['coupon_code']
                ])->execute();
                
                if (!$result) {
                    error_log('Warning: Could not record coupon usage for: ' . $orderData['coupon_code']);
                    // Don't throw exception for coupon usage recording failure
                }
                
                error_log('Coupon usage recorded: ' . $orderData['coupon_code']);
            }
            
            // Commit transaction
            $this->commit();
            
            error_log('=== ORDER CREATION SUCCESS ===');
            error_log('Order ID: ' . $orderId . ', Invoice: ' . $invoiceNumber);
            
            return $orderId;
            
        } catch (\Exception $e) {
            // Rollback transaction
            $this->rollback();
            
            error_log('=== ORDER CREATION FAILED ===');
            error_log('Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            return false;
        }
    }
    
    /**
     * Get order items for a specific order
     *
     * @param int $orderId
     * @return array
     */
    public function getOrderItems($orderId)
    {
        $sql = "SELECT oi.*, p.image, p.slug 
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
                ORDER BY oi.id";
        
        return $this->db->query($sql)->bind([$orderId])->all();
    }
    
    /**
     * Get order with items
     *
     * @param int $orderId
     * @return array|false
     */
    public function getOrderWithItems($orderId)
    {
        $order = $this->getOrderById($orderId);
        
        if (!$order) {
            return false;
        }
        
        $order['items'] = $this->getOrderItems($orderId);
        
        return $order;
    }
    
    /**
     * Generate a unique invoice number
     *
     * @return string
     */
    public function generateInvoiceNumber()
    {
        $prefix = 'NN';
        $date = date('Ymd');
        $random = mt_rand(1000, 9999);
        
        // Check if invoice number already exists
        $invoiceNumber = $prefix . $date . $random;
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE invoice = ?";
        $result = $this->db->query($sql)->bind([$invoiceNumber])->single();
        
        // If invoice exists, generate a new one
        if ($result && $result['count'] > 0) {
            $random = mt_rand(10000, 99999);
            $invoiceNumber = $prefix . $date . $random;
        }
        
        return $invoiceNumber;
    }
    
    /**
     * Get order by ID
     *
     * @param int $id
     * @return array|false
     */
    public function getOrderById($id)
    {
        $sql = "SELECT o.*, pm.name as payment_method_name 
                FROM {$this->table} o
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                WHERE o.id = ?";
        return $this->db->query($sql)->bind([$id])->single();
    }
    
    /**
     * Get orders by user ID
     *
     * @param int $userId
     * @return array
     */
    public function getOrdersByUserId($userId)
    {
        $sql = "SELECT o.*, pm.name as payment_method_name 
                FROM {$this->table} o
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                WHERE o.user_id = ?
                ORDER BY o.created_at DESC";
        return $this->db->query($sql)->bind([$userId])->all();
    }
    
    /**
     * Get order by invoice number
     *
     * @param string $invoice
     * @return array|false
     */
    public function getOrderByInvoice($invoice)
    {
        $sql = "SELECT o.*, pm.name as payment_method_name 
                FROM {$this->table} o
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                WHERE o.invoice = ?";
        return $this->db->query($sql)->bind([$invoice])->single();
    }
    
    /**
     * Get all orders with optional status filter
     *
     * @param string|null $status
     * @return array
     */
    public function getAllOrders($status = null)
    {
        $sql = "SELECT o.*, pm.name as payment_method_name, u.first_name, u.last_name, u.email
                FROM {$this->table} o
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                LEFT JOIN users u ON o.user_id = u.id";
        
        $params = [];
        
        if ($status) {
            $sql .= " WHERE o.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        return $this->db->query($sql)->bind($params)->all();
    }
    
    /**
     * Get recent orders
     *
     * @param int $limit
     * @return array
     */
    public function getRecentOrders($limit = 5)
    {
        $sql = "SELECT o.*, pm.name as payment_method_name, u.first_name, u.last_name
                FROM {$this->table} o
                LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC
                LIMIT ?";
        return $this->db->query($sql)->bind([$limit])->all();
    }
    
    /**
     * Get order count
     *
     * @return int
     */
    public function getOrderCount()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->query($sql)->single();
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Get total sales
     *
     * @return float
     */
    public function getTotalSales()
    {
        $sql = "SELECT SUM(total_amount) as total FROM {$this->table} WHERE status IN ('paid', 'delivered')";
        $result = $this->db->query($sql)->single();
        return $result ? (float)$result['total'] : 0;
    }
    
    /**
     * Check if there's an active transaction
     * 
     * @return bool
     */
    public function inTransaction()
    {
        return $this->transactionActive;
    }
    
    /**
     * Begin a transaction safely
     * 
     * @return bool
     */
    public function beginTransaction()
    {
        if ($this->transactionActive) {
            error_log('Attempted to start a transaction while one is already active. Rolling back existing transaction.');
            $this->rollback();
        }
        
        $result = $this->db->beginTransaction();
        
        if ($result) {
            $this->transactionActive = true;
        }
        
        return $result;
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool
     */
    public function commit()
    {
        if (!$this->transactionActive) {
            error_log('Attempted to commit when no transaction is active.');
            return false;
        }
        
        $result = $this->db->commit();
        
        if ($result) {
            $this->transactionActive = false;
        }
        
        return $result;
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool
     */
    public function rollback()
    {
        if (!$this->transactionActive) {
            error_log('Attempted to rollback when no transaction is active.');
            return false;
        }
        
        $result = $this->db->rollback();
        
        if ($result) {
            $this->transactionActive = false;
        }
        
        return $result;
    }
    
    /**
     * Update order status
     *
     * @param int $orderId
     * @param string $status
     * @return bool
     */
    public function updateOrderStatus($orderId, $status)
    {
        try {
            $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?";
            $result = $this->db->query($sql)->bind([$status, $orderId])->execute();
            
            if ($result) {
                error_log("Order status updated - ID: $orderId, Status: $status");
                return true;
            } else {
                error_log("Failed to update order status - ID: $orderId, Status: $status");
                return false;
            }
        } catch (\Exception $e) {
            error_log('Error updating order status: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get order statistics
     *
     * @return array
     */
    public function getOrderStats()
    {
        $stats = [];
        
        // Total orders
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->db->query($sql)->single();
        $stats['total_orders'] = $result ? (int)$result['total'] : 0;
        
        // Orders by status
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
        $results = $this->db->query($sql)->all();
        $stats['by_status'] = [];
        foreach ($results as $row) {
            $stats['by_status'][$row['status']] = (int)$row['count'];
        }
        
        // Total sales
        $stats['total_sales'] = $this->getTotalSales();
        
        return $stats;
    }
}