<?php
namespace App\Models;

use App\Core\Model;
use App\Helpers\CacheHelper;
use Spatie\Async\Pool;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    private $cache;
    private $asyncPool;

    public function __construct()
    {
        parent::__construct();
        $this->cache = CacheHelper::getInstance();
        
        // Initialize Spatie Async Pool if available
        if (class_exists('\\Spatie\\Async\\Pool')) {
            try {
                $this->asyncPool = Pool::create();
            } catch (\Exception $e) {
                error_log('Failed to create async pool in Product: ' . $e->getMessage());
                $this->asyncPool = null;
            }
        } else {
            $this->asyncPool = null;
        }
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
            $data['sale_price'],
            $data['stock_quantity'],
            $data['category'],
            $data['weight'] ?? null,
            $data['serving'] ?? null,
            isset($data['capsule']) ? $data['capsule'] : 0,
            $data['flavor'] ?? null,
            $data['image'],
            $data['sales_count'],
            $data['is_featured'],
            $data['created_at'],
            $data['updated_at']
        ];

        $result = $this->db->query($sql)->bind($params)->execute();
        
        // Clear home page cache when a new product is created
        if ($result) {
            $this->clearCacheAsync('home_page_data');
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
        $result = $this->db->query($sql)->bind($params)->execute();
        
        // Clear home page cache when a product is updated
        if ($result) {
            $this->clearCacheAsync('home_page_data');
            
            // Also clear category-specific cache if category is updated
            if (isset($data['category'])) {
                $this->clearCacheAsync('category_' . strtolower(str_replace(' ', '_', $data['category'])));
            }
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
        $cacheKey = $this->cache->generateKey('products_paginated', ['limit' => $limit, 'offset' => $offset]);
        
        // Try to get from cache first
        $products = $this->cache->get($cacheKey);
        
        if ($products === null) {
            // Cache miss - fetch from database
            $sql = "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT ? OFFSET ?";
            $products = $this->db->query($sql)->bind([$limit, $offset])->all();
            
            // Store in cache for 30 minutes
            $this->cache->set($cacheKey, $products, 1800);
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
        $cacheKey = $this->cache->generateKey('product_count');
        
        // Try to get from cache first
        $count = $this->cache->get($cacheKey);
        
        if ($count === null) {
            // Cache miss - fetch from database
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            $result = $this->db->query($sql)->single();
            $count = $result ? (int)$result['count'] : 0;
            
            // Store in cache for 1 hour
            $this->cache->set($cacheKey, $count, 3600);
        }
        
        return $count;
    }

    /**
     * Search products
     *
     * @param string $keyword
     * @return array
     */
    public function searchProducts($keyword)
    {
        $cacheKey = $this->cache->generateKey('product_search', ['keyword' => $keyword]);
        
        // Try to get from cache first
        $results = $this->cache->get($cacheKey);
        
        if ($results === null) {
            // Cache miss - fetch from database
            $sql = "SELECT * FROM {$this->table} 
                    WHERE product_name LIKE ? OR description LIKE ? OR category LIKE ?
                    ORDER BY id DESC";
            
            $param = "%{$keyword}%";
            $results = $this->db->query($sql)->bind([$param, $param, $param])->all();
            
            // Store in cache for 15 minutes
            $this->cache->set($cacheKey, $results, 900);
        }
        
        return $results;
    }

    /**
     * Get products with low stock
     *
     * @param int $threshold
     * @return array
     */
    public function getLowStockProducts($threshold = 5)
    {
        $cacheKey = $this->cache->generateKey('low_stock_products', ['threshold' => $threshold]);
        
        // Try to get from cache first
        $products = $this->cache->get($cacheKey);
        
        if ($products === null) {
            // Cache miss - fetch from database
            $sql = "SELECT * FROM {$this->table} WHERE stock_quantity <= ? ORDER BY stock_quantity ASC";
            $products = $this->db->query($sql)->bind([$threshold])->all();
            
            // Store in cache for 15 minutes (shorter time as stock changes frequently)
            $this->cache->set($cacheKey, $products, 900);
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
        $sql = "UPDATE {$this->table} SET stock_quantity = ? WHERE id = ?";
        $result = $this->db->query($sql)->bind([$stock_quantity, $id])->execute();
        
        // Clear relevant caches when product quantity is updated
        if ($result) {
            $this->clearCacheAsync('home_page_data');
            $this->clearCacheAsync('low_stock_products');
            
            // Clear product-specific cache
            $this->clearCacheAsync('product_' . $id);
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
        $cacheKey = $this->cache->generateKey('category_products', [
            'category' => $category,
            'limit' => $limit,
            'offset' => $offset,
            'sort' => $sort
        ]);
        
        // Try to get from cache first
        $products = $this->cache->get($cacheKey);
        
        if ($products === null) {
            // Cache miss - fetch from database
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
            
            // Store in cache for 30 minutes
            $this->cache->set($cacheKey, $products, 1800);
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
        $cacheKey = $this->cache->generateKey('category_product_count', ['category' => $category]);
        
        // Try to get from cache first
        $count = $this->cache->get($cacheKey);
        
        if ($count === null) {
            // Cache miss - fetch from database
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE category = ?";
            $result = $this->db->query($sql)->bind([$category])->single();
            $count = $result ? (int)$result['count'] : 0;
            
            // Store in cache for 1 hour
            $this->cache->set($cacheKey, $count, 3600);
        }
        
        return $count;
    }

    /**
     * Find product by slug
     *
     * @param string $slug
     * @return array|false
     */
    public function findBySlug($slug)
    {
        $cacheKey = $this->cache->generateKey('product_slug', ['slug' => $slug]);
        
        // Try to get from cache first
        $product = $this->cache->get($cacheKey);
        
        if ($product === null) {
            // Cache miss - fetch from database
            $sql = "SELECT * FROM {$this->table} WHERE slug = ?";
            $product = $this->db->query($sql)->bind([$slug])->single();
            
            // Store in cache for 1 hour
            if ($product) {
                $this->cache->set($cacheKey, $product, 3600);
            }
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
        $cacheKey = $this->cache->generateKey('product_' . $id);
        
        // Try to get from cache first
        $product = $this->cache->get($cacheKey);
        
        if ($product === null) {
            // Cache miss - fetch from database
            $sql = "SELECT * FROM {$this->table} WHERE id = ?";
            $product = $this->db->query($sql)->bind([$id])->single();
            
            // Store in cache for 1 hour
            if ($product) {
                $this->cache->set($cacheKey, $product, 3600);
            }
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
        // Get product details before deletion to clear category cache
        $product = $this->find($id);
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql)->bind([$id])->execute();
        
        // Clear relevant caches when a product is deleted
        if ($result) {
            $this->clearCacheAsync('home_page_data');
            $this->clearCacheAsync('product_count');
            $this->clearCacheAsync('product_' . $id);
            
            // Clear category-specific cache if product had a category
            if ($product && isset($product['category'])) {
                $this->clearCacheAsync('category_' . strtolower(str_replace(' ', '_', $product['category'])));
                $this->clearCacheAsync('category_product_count');
            }
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
        $cacheKey = $this->cache->generateKey('all_products');
        
        // Try to get from cache first
        $products = $this->cache->get($cacheKey);
        
        if ($products === null) {
            // Cache miss - fetch from database
            $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
            $products = $this->db->query($sql)->all();
            
            // Store in cache for 30 minutes
            $this->cache->set($cacheKey, $products, 1800);
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
        $cacheKey = $this->cache->generateKey('related_products', [
            'product_id' => $productId,
            'category' => $category,
            'limit' => $limit
        ]);
        
        // Try to get from cache first
        $relatedProducts = $this->cache->get($cacheKey);
        
        if ($relatedProducts === null) {
            // Cache miss - fetch from database using async if available
            if ($this->asyncPool) {
                try {
                    $promise = $this->asyncPool->add(function() use ($productId, $category, $limit) {
                        $sql = "SELECT * FROM {$this->table} 
                                WHERE category = ? AND id != ? 
                                ORDER BY RAND() 
                                LIMIT ?";
                        return $this->db->query($sql)->bind([$category, $productId, $limit])->all();
                    });
                    
                    // Wait for async task to complete
                    $this->asyncPool->wait();
                    
                    // Get result
                    $relatedProducts = $promise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($productId, $category, $limit) {
                        error_log('Error in async related products fetch: ' . $e->getMessage());
                        // Fall back to synchronous request
                        $sql = "SELECT * FROM {$this->table} 
                                WHERE category = ? AND id != ? 
                                ORDER BY RAND() 
                                LIMIT ?";
                        return $this->db->query($sql)->bind([$category, $productId, $limit])->all();
                    });
                } catch (\Exception $e) {
                    error_log('Async processing error in related products: ' . $e->getMessage());
                    // Fall back to synchronous request
                    $sql = "SELECT * FROM {$this->table} 
                            WHERE category = ? AND id != ? 
                            ORDER BY RAND() 
                            LIMIT ?";
                    $relatedProducts = $this->db->query($sql)->bind([$category, $productId, $limit])->all();
                }
            } else {
                // Standard approach without async
                $sql = "SELECT * FROM {$this->table} 
                        WHERE category = ? AND id != ? 
                        ORDER BY RAND() 
                        LIMIT ?";
                $relatedProducts = $this->db->query($sql)->bind([$category, $productId, $limit])->all();
            }
            
            // Store in cache for 1 hour
            $this->cache->set($cacheKey, $relatedProducts, 3600);
        }
        
        return $relatedProducts;
    }
    
    /**
     * Get best selling products
     *
     * @param int $limit
     * @return array
     */
    public function getBestSellingProducts($limit = 8)
    {
        $cacheKey = $this->cache->generateKey('best_selling_products', ['limit' => $limit]);
        
        // Try to get from cache first
        $products = $this->cache->get($cacheKey);
        
        if ($products === null) {
            // Cache miss - fetch from database using async if available
            if ($this->asyncPool) {
                try {
                    $promise = $this->asyncPool->add(function() use ($limit) {
                        $sql = "SELECT * FROM {$this->table} 
                                WHERE sales_count > 0 
                                ORDER BY sales_count DESC 
                                LIMIT ?";
                        return $this->db->query($sql)->bind([$limit])->all();
                    });
                    
                    // Wait for async task to complete
                    $this->asyncPool->wait();
                    
                    // Get result
                    $products = $promise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($limit) {
                        error_log('Error in async best selling products fetch: ' . $e->getMessage());
                        // Fall back to synchronous request
                        $sql = "SELECT * FROM {$this->table} 
                                WHERE sales_count > 0 
                                ORDER BY sales_count DESC 
                                LIMIT ?";
                        return $this->db->query($sql)->bind([$limit])->all();
                    });
                } catch (\Exception $e) {
                    error_log('Async processing error in best selling products: ' . $e->getMessage());
                    // Fall back to synchronous request
                    $sql = "SELECT * FROM {$this->table} 
                            WHERE sales_count > 0 
                            ORDER BY sales_count DESC 
                            LIMIT ?";
                    $products = $this->db->query($sql)->bind([$limit])->all();
                }
            } else {
                // Standard approach without async
                $sql = "SELECT * FROM {$this->table} 
                        WHERE sales_count > 0 
                        ORDER BY sales_count DESC 
                        LIMIT ?";
                $products = $this->db->query($sql)->bind([$limit])->all();
            }
            
            // Store in cache for 1 hour
            $this->cache->set($cacheKey, $products, 3600);
        }
        
        return $products;
    }
    
    /**
     * Update sales count for a product
     *
     * @param int $id
     * @param int $quantity
     * @return bool
     */
    public function updateSalesCount($id, $quantity = 1)
    {
        $sql = "UPDATE {$this->table} SET sales_count = sales_count + ? WHERE id = ?";
        $result = $this->db->query($sql)->bind([$quantity, $id])->execute();
        
        // Clear relevant caches when sales count is updated
        if ($result) {
            $this->clearCacheAsync('best_selling_products');
            $this->clearCacheAsync('product_' . $id);
        }
        
        return $result;
    }
    
    /**
     * Clear cache asynchronously if possible
     *
     * @param string $key
     * @return void
     */
    private function clearCacheAsync($key)
    {
        if ($this->asyncPool) {
            try {
                $this->asyncPool->add(function() use ($key) {
                    $this->cache->delete($key);
                    return true;
                });
            } catch (\Exception $e) {
                error_log('Error in async cache clearing: ' . $e->getMessage());
                // Fall back to synchronous cache clearing
                $this->cache->delete($key);
            }
        } else {
            // Standard approach without async
            $this->cache->delete($key);
        }
    }
}
