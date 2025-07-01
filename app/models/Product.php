<?php
namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    private $productImageModel;

    public function __construct()
    {
        parent::__construct();
        $this->productImageModel = new ProductImage();
    }

    /**
     * Create a new product
     *
     * @param array $data
     * @return int|bool
     */
    public function create($data)
    {
        $data['slug'] = $this->generateSlug($data['product_name']);
        $data['sales_count'] = 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $sql = "INSERT INTO {$this->table} (
            product_name, slug, description, price, sale_price, stock_quantity, 
            category, weight, serving, capsule, flavor, image, sales_count, is_featured, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['product_name'],
            $data['slug'],
            $data['description'],
            $data['price'],
            $data['sale_price'] ?? null,
            $data['stock_quantity'],
            $data['category'],
            $data['weight'] ?? null,
            $data['serving'] ?? null,
            isset($data['capsule']) ? (int)$data['capsule'] : 0,
            $data['flavor'] ?? null,
            $data['image'] ?? null, // Keep for backward compatibility
            $data['sales_count'],
            isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
            $data['created_at'],
            $data['updated_at']
        ];

        $result = $this->db->query($sql)->bind($params)->execute();
        
        if ($result) {
            $productId = $this->db->lastInsertId();
            
            // Add primary image if provided
            if (!empty($data['image'])) {
                $this->productImageModel->addImage($productId, $data['image'], true, 0);
            }
            
            return $productId;
        }
        
        return false;
    }

    /**
     * Update an existing product
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        // Regenerate slug if product name has changed
        if (isset($data['product_name'])) {
            $data['slug'] = $this->generateSlug($data['product_name']);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');

        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            if (in_array($key, [
                'product_name', 'slug', 'description', 'price', 'sale_price', 
                'stock_quantity', 'category', 'weight', 'serving', 'capsule', 
                'flavor', 'image', 'is_featured', 'updated_at'
            ])) {
                $fields[] = "{$key} = ?";
                $params[] = $value;
            }
        }
        $params[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->query($sql)->bind($params)->execute();
    }

    /**
     * Get all products with pagination
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getProducts($limit = 10, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT ? OFFSET ?";
        $products = $this->db->query($sql)->bind([$limit, $offset])->all();
        
        // Add images to each product
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
        }
        
        return $products;
    }

    /**
     * Get product count
     *
     * @return int
     */
    public function getProductCount()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->query($sql)->single();
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Search products
     *
     * @param string $keyword
     * @return array
     */
    public function searchProducts($keyword)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE product_name LIKE ? OR description LIKE ? OR category LIKE ?
                ORDER BY id DESC";
        
        $param = "%{$keyword}%";
        $products = $this->db->query($sql)->bind([$param, $param, $param])->all();
        
        // Add images to each product
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
        }
        
        return $products;
    }

    /**
     * Get products with low stock
     *
     * @param int $threshold
     * @return array
     */
    public function getLowStockProducts($threshold = 5)
    {
        $sql = "SELECT * FROM {$this->table} WHERE stock_quantity <= ? ORDER BY stock_quantity ASC";
        $products = $this->db->query($sql)->bind([$threshold])->all();
        
        // Add images to each product
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
        }
        
        return $products;
    }

    /**
     * Update product quantity
     *
     * @param int $id
     * @param int $stock_quantity
     * @return bool
     */
    public function updateQuantity($id, $stock_quantity)
    {
        $sql = "UPDATE {$this->table} SET stock_quantity = ?, updated_at = ? WHERE id = ?";
        return $this->db->query($sql)->bind([$stock_quantity, date('Y-m-d H:i:s'), $id])->execute();
    }

    /**
     * Get featured products
     *
     * @param int $limit
     * @return array
     */
    public function getFeaturedProducts($limit = 8)
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_featured = 1 ORDER BY id DESC LIMIT ?";
        $products = $this->db->query($sql)->bind([$limit])->all();
        
        // Add images to each product
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
        }
        
        return $products;
    }
    
    /**
     * Get products by category
     *
     * @param string $category
     * @param int $limit
     * @param int $offset
     * @param string $sort
     * @return array
     */
    public function getProductsByCategory($category, $limit = 10, $offset = 0, $sort = 'newest')
    {
        $orderBy = 'id DESC'; // default sorting (newest)
        
        switch ($sort) {
            case 'price-low':
                $orderBy = 'price ASC';
                break;
            case 'price-high':
                $orderBy = 'price DESC';
                break;
            case 'popular':
                $orderBy = 'sales_count DESC';
                break;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE category = ? ORDER BY {$orderBy} LIMIT ? OFFSET ?";
        $products = $this->db->query($sql)->bind([$category, $limit, $offset])->all();
        
        // Add images to each product
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
        }
        
        return $products;
    }
    
    /**
     * Get product count by category
     *
     * @param string $category
     * @return int
     */
    public function getProductCountByCategory($category)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE category = ?";
        $result = $this->db->query($sql)->bind([$category])->single();
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Find product by slug
     *
     * @param string $slug
     * @return array|false
     */
    public function findBySlug($slug)
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = ?";
        $product = $this->db->query($sql)->bind([$slug])->single();
        
        if ($product) {
            $product['images'] = $this->getProductImages($product['id']);
        }
        
        return $product;
    }

    /**
     * Generate slug from product name
     *
     * @param string $productName
     * @return string
     */
    public function generateSlug($productName)
    {
        // Convert to lowercase and replace spaces with hyphens
        $slug = strtolower(trim($productName));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Check if slug already exists
        $existingProduct = $this->findBySlug($slug);
        
        // If slug exists, append a unique identifier
        if ($existingProduct) {
            $slug .= '-' . uniqid();
        }
        
        return $slug;
    }

    /**
     * Find product by ID
     *
     * @param int $id
     * @return array|false
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $product = $this->db->query($sql)->bind([$id])->single();
        
        if ($product) {
            $product['images'] = $this->getProductImages($product['id']);
        }
        
        return $product;
    }

    /**
     * Delete product by ID
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        // Delete product images first
        $this->productImageModel->deleteByProductId($id);
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->query($sql)->bind([$id])->execute();
    }

    /**
     * Get all products
     *
     * @return array
     */
    public function all()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
        $products = $this->db->query($sql)->all();
        
        // Add images to each product
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
        }
        
        return $products;
    }

    /**
     * Get product images - CRITICAL METHOD FOR CART
     *
     * @param int $productId
     * @return array
     */
    public function getProductImages($productId)
    {
        return $this->productImageModel->getByProductId($productId);
    }

    /**
     * Get primary product image
     *
     * @param int $productId
     * @return array|false
     */
    public function getPrimaryProductImage($productId)
    {
        return $this->productImageModel->getPrimaryImage($productId);
    }

    /**
     * Find product by ID with images
     *
     * @param int $id
     * @return array|false
     */
    public function findWithImages($id)
    {
        $product = $this->find($id);
        
        if ($product) {
            $product['images'] = $this->getProductImages($id);
            $product['primary_image'] = $this->productImageModel->getPrimaryImage($id);
            
            // For backward compatibility, set the image field to primary image URL
            if ($product['primary_image']) {
                $product['image'] = $product['primary_image']['image_url'];
            }
        }
        
        return $product;
    }

    /**
     * Find product by slug with images
     *
     * @param string $slug
     * @return array|false
     */
    public function findBySlugWithImages($slug)
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = ?";
        $product = $this->db->query($sql)->bind([$slug])->single();
        
        if ($product) {
            $product['images'] = $this->getProductImages($product['id']);
            $product['primary_image'] = $this->productImageModel->getPrimaryImage($product['id']);
            
            // For backward compatibility
            if ($product['primary_image']) {
                $product['image'] = $product['primary_image']['image_url'];
            }
        }
        
        return $product;
    }

    /**
     * Get all products with images
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getProductsWithImages($limit = 10, $offset = 0)
    {
        $products = $this->getProducts($limit, $offset);
        
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
            $product['primary_image'] = $this->productImageModel->getPrimaryImage($product['id']);
            
            // For backward compatibility
            if ($product['primary_image']) {
                $product['image'] = $product['primary_image']['image_url'];
            }
        }
        
        return $products;
    }

    /**
     * Get related products
     *
     * @param int $productId
     * @param string $category
     * @param int $limit
     * @return array
     */
    public function getRelatedProducts($productId, $category, $limit = 4)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE category = ? AND id != ? 
                ORDER BY RAND() 
                LIMIT ?";
        $products = $this->db->query($sql)->bind([$category, $productId, $limit])->all();
        
        // Add images to each product
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
        }
        
        return $products;
    }

    /**
     * Get best selling products
     *
     * @param int $limit
     * @return array
     */
    public function getBestSellingProducts($limit = 8)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE sales_count > 0 
                ORDER BY sales_count DESC 
                LIMIT ?";
        $products = $this->db->query($sql)->bind([$limit])->all();
        
        // Add images to each product
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
        }
        
        return $products;
    }

    /**
     * Update product sales count
     *
     * @param int $id
     * @param int $quantity
     * @return bool
     */
    public function updateSalesCount($id, $quantity = 1)
    {
        $sql = "UPDATE {$this->table} SET sales_count = sales_count + ?, updated_at = ? WHERE id = ?";
        return $this->db->query($sql)->bind([$quantity, date('Y-m-d H:i:s'), $id])->execute();
    }

    /**
     * Get products by multiple categories
     *
     * @param array $categories
     * @param int $limit
     * @return array
     */
    public function getProductsByCategories($categories, $limit = 10)
    {
        if (empty($categories)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($categories) - 1) . '?';
        $sql = "SELECT * FROM {$this->table} WHERE category IN ({$placeholders}) ORDER BY id DESC LIMIT ?";
        
        $params = array_merge($categories, [$limit]);
        $products = $this->db->query($sql)->bind($params)->all();
        
        // Add images to each product
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
        }
        
        return $products;
    }

    /**
     * Get product statistics
     *
     * @return array
     */
    public function getProductStats()
    {
        $stats = [];
        
        // Total products
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->db->query($sql)->single();
        $stats['total_products'] = $result ? (int)$result['total'] : 0;
        
        // Featured products
        $sql = "SELECT COUNT(*) as featured FROM {$this->table} WHERE is_featured = 1";
        $result = $this->db->query($sql)->single();
        $stats['featured_products'] = $result ? (int)$result['featured'] : 0;
        
        // Low stock products
        $sql = "SELECT COUNT(*) as low_stock FROM {$this->table} WHERE stock_quantity <= 5";
        $result = $this->db->query($sql)->single();
        $stats['low_stock_products'] = $result ? (int)$result['low_stock'] : 0;
        
        // Out of stock products
        $sql = "SELECT COUNT(*) as out_of_stock FROM {$this->table} WHERE stock_quantity = 0";
        $result = $this->db->query($sql)->single();
        $stats['out_of_stock_products'] = $result ? (int)$result['out_of_stock'] : 0;
        
        // Categories
        $sql = "SELECT COUNT(DISTINCT category) as categories FROM {$this->table}";
        $result = $this->db->query($sql)->single();
        $stats['total_categories'] = $result ? (int)$result['categories'] : 0;
        
        return $stats;
    }

    /**
     * Get products with filters
     *
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @param string $sort
     * @return array
     */
    public function getProductsWithFilters($filters = [], $limit = 10, $offset = 0, $sort = 'newest')
    {
        $where = [];
        $params = [];
        
        // Category filter
        if (!empty($filters['category'])) {
            $where[] = "category = ?";
            $params[] = $filters['category'];
        }
        
        // Price range filter
        if (!empty($filters['min_price'])) {
            $where[] = "price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $where[] = "price <= ?";
            $params[] = $filters['max_price'];
        }
        
        // Stock filter
        if (!empty($filters['in_stock'])) {
            $where[] = "stock_quantity > 0";
        }
        
        // Featured filter
        if (!empty($filters['featured'])) {
            $where[] = "is_featured = 1";
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $where[] = "(product_name LIKE ? OR description LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Build ORDER BY clause
        $orderBy = 'id DESC'; // default
        switch ($sort) {
            case 'price-low':
                $orderBy = 'price ASC';
                break;
            case 'price-high':
                $orderBy = 'price DESC';
                break;
            case 'popular':
                $orderBy = 'sales_count DESC';
                break;
            case 'name':
                $orderBy = 'product_name ASC';
                break;
        }
        
        // Build SQL query
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY {$orderBy} LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $products = $this->db->query($sql)->bind($params)->all();
        
        // Add images to each product
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
        }
        
        return $products;
    }

    /**
     * Get filtered product count
     *
     * @param array $filters
     * @return int
     */
    public function getFilteredProductCount($filters = [])
    {
        $where = [];
        $params = [];
        
        // Category filter
        if (!empty($filters['category'])) {
            $where[] = "category = ?";
            $params[] = $filters['category'];
        }
        
        // Price range filter
        if (!empty($filters['min_price'])) {
            $where[] = "price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $where[] = "price <= ?";
            $params[] = $filters['max_price'];
        }
        
        // Stock filter
        if (!empty($filters['in_stock'])) {
            $where[] = "stock_quantity > 0";
        }
        
        // Featured filter
        if (!empty($filters['featured'])) {
            $where[] = "is_featured = 1";
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $where[] = "(product_name LIKE ? OR description LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Build SQL query
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = $this->db->query($sql)->bind($params)->single();
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Get products for cart with images - SPECIFIC FOR CART FUNCTIONALITY
     *
     * @param array $productIds
     * @return array
     */
    public function getProductsForCart($productIds)
    {
        if (empty($productIds)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        $sql = "SELECT * FROM {$this->table} WHERE id IN ({$placeholders})";
        
        $products = $this->db->query($sql)->bind($productIds)->all();
        
        // Add images to each product
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
        }
        
        return $products;
    }

    /**
     * Bulk update product quantities (for order processing)
     *
     * @param array $updates Array of ['product_id' => quantity_to_subtract]
     * @return bool
     */
    public function bulkUpdateQuantities($updates)
    {
        $success = true;
        
        foreach ($updates as $productId => $quantityToSubtract) {
            $sql = "UPDATE {$this->table} 
                    SET stock_quantity = GREATEST(0, stock_quantity - ?), 
                        sales_count = sales_count + ?,
                        updated_at = ? 
                    WHERE id = ?";
            
            $result = $this->db->query($sql)->bind([
                $quantityToSubtract, 
                $quantityToSubtract, 
                date('Y-m-d H:i:s'), 
                $productId
            ])->execute();
            
            if (!$result) {
                $success = false;
            }
        }
        
        return $success;
    }
}