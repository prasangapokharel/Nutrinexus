<?php
namespace App\Models;

use App\Core\Model;
use App\Helpers\CacheHelper;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    private $cache;

    public function __construct()
    {
        parent::__construct();
        $this->cache = CacheHelper::getInstance();
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
            category, image, sales_count, is_featured, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['product_name'],
            $data['slug'],
            $data['description'],
            $data['price'],
            $data['sale_price'],
            $data['stock_quantity'],
            $data['category'],
            $data['image'],
            $data['sales_count'],
            $data['is_featured'],
            $data['created_at'],
            $data['updated_at']
        ];

        $result = $this->db->query($sql)->bind($params)->execute();
        
        // Clear home page cache when a new product is created
        if ($result) {
            $this->cache->delete('home_page_data');
        }
        
        return $result ? $this->db->lastInsertId() : false;
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
            if (in_array($key, ['product_name', 'slug', 'description', 'price', 'sale_price', 'stock_quantity', 'category', 'image', 'is_featured', 'updated_at'])) {
                $fields[] = "{$key} = ?";
                $params[] = $value;
            }
        }
        $params[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $result = $this->db->query($sql)->bind($params)->execute();
        
        // Clear home page cache when a product is updated
        if ($result) {
            $this->cache->delete('home_page_data');
        }
        
        return $result;
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
        return $this->db->query($sql)->bind([$limit, $offset])->all();
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
        return $this->db->query($sql)->bind([$param, $param, $param])->all();
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
        return $this->db->query($sql)->bind([$threshold])->all();
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
        $sql = "UPDATE {$this->table} SET stock_quantity = ? WHERE id = ?";
        $result = $this->db->query($sql)->bind([$stock_quantity, $id])->execute();
        
        // Clear home page cache when product quantity is updated
        if ($result) {
            $this->cache->delete('home_page_data');
        }
        
        return $result;
    }

    /**
     * Get featured products
     *
     * @param int $limit
     * @return array
     */
    public function getFeaturedProducts($limit = 8)
    {
        $cacheKey = $this->cache->generateKey('featured_products', ['limit' => $limit]);
        
        // Try to get from cache first
        $featuredProducts = $this->cache->get($cacheKey);
        
        if ($featuredProducts === null) {
            // Cache miss - fetch from database
            $sql = "SELECT * FROM {$this->table} WHERE is_featured = 1 ORDER BY id DESC LIMIT ?";
            $featuredProducts = $this->db->query($sql)->bind([$limit])->all();
            
            // Store in cache for 1 hour
            $this->cache->set($cacheKey, $featuredProducts, 3600);
        }
        
        return $featuredProducts;
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
        return $this->db->query($sql)->bind([$category, $limit, $offset])->all();
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
        return $this->db->query($sql)->bind([$slug])->single();
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
        return $this->db->query($sql)->bind([$id])->single();
    }

    /**
     * Delete product by ID
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql)->bind([$id])->execute();
        
        // Clear home page cache when a product is deleted
        if ($result) {
            $this->cache->delete('home_page_data');
        }
        
        return $result;
    }

    /**
     * Get all products
     *
     * @return array
     */
    public function all()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
        return $this->db->query($sql)->all();
    }
}
