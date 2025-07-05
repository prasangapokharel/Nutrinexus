<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Wishlist;
use App\Helpers\CacheHelper;

// Import phpFastCache
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Drivers\Files\Config as FilesConfig;

// Import Spatie Async
use Spatie\Async\Pool;

/**
 * Product Controller with Enhanced Caching and Async Processing
 * Handles product-related functionality with phpFastCache and Spatie Async
 */
class ProductController extends Controller
{
    /**
     * @var Product
     */
    private $productModel;
    private $productImageModel;
    
    /**
     * @var Review
     */
    private $reviewModel;
    
    /**
     * @var Wishlist
     */
    private $wishlistModel;
    
    /**
     * @var CacheHelper
     */
    private $cache;
    
    /**
     * @var Pool|null
     */
    private $asyncPool;
    private $hasAsync = false;
    
    // phpFastCache instance
    private $fastCache = null;
    private $hasFastCache = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->productImageModel = new ProductImage();
        $this->reviewModel = new Review();
        $this->wishlistModel = new Wishlist();
        $this->cache = CacheHelper::getInstance();
        
        // Initialize phpFastCache
        $this->initializeFastCache();
        
        // Initialize Spatie Async Pool with fallback
        $this->initializeAsync();
    }

    /**
     * Initialize phpFastCache
     */
    private function initializeFastCache()
    {
        try {
            if (class_exists('\\Phpfastcache\\CacheManager')) {
                // Configure phpFastCache
                $cacheDir = defined('CACHE_DIR') ? CACHE_DIR : __DIR__ . '/../../storage/cache/phpfastcache';
                
                // Ensure cache directory exists
                if (!is_dir($cacheDir)) {
                    mkdir($cacheDir, 0755, true);
                }
                
                // Setup configuration
                CacheManager::setDefaultConfig(new ConfigurationOption([
                    'path' => $cacheDir,
                    'itemDetailedDate' => true,
                    'defaultTtl' => 3600,
                    'securityKey' => 'nutrinexus_products_cache',
                    'htaccess' => true,
                    'defaultKeyHashFunction' => 'md5',
                    'defaultFileNameHashFunction' => 'md5',
                ]));
                
                // Get Files driver instance
                $this->fastCache = CacheManager::getInstance('files');
                $this->hasFastCache = true;
                
                error_log('phpFastCache initialized successfully for ProductController');
            } else {
                error_log('phpFastCache not available in ProductController');
                $this->hasFastCache = false;
            }
        } catch (\Exception $e) {
            error_log('Failed to initialize phpFastCache in ProductController: ' . $e->getMessage());
            $this->hasFastCache = false;
        }
    }

    /**
     * Initialize Spatie Async
     */
    private function initializeAsync()
    {
        try {
            if (class_exists('\\Spatie\\Async\\Pool')) {
                $this->asyncPool = Pool::create();
                $this->hasAsync = true;
                error_log('Spatie Async initialized successfully for ProductController');
            } else {
                error_log('Spatie\\Async\\Pool class not found in ProductController. Async processing disabled.');
                $this->hasAsync = false;
            }
        } catch (\Exception $e) {
            error_log('Failed to create async pool in ProductController: ' . $e->getMessage());
            $this->hasAsync = false;
        }
    }

    /**
     * Enhanced cache method with phpFastCache, fallback to regular cache
     * 
     * @param string $key Cache key
     * @param callable $callback Function to execute on cache miss
     * @param int $lifetime Cache lifetime in seconds
     * @return mixed
     */
    private function getFromEnhancedCache($key, callable $callback, int $lifetime = 3600)
    {
        // Try phpFastCache first if available
        if ($this->hasFastCache && $this->fastCache) {
            try {
                $cacheItem = $this->fastCache->getItem($key);
                
                if ($cacheItem->isHit()) {
                    $data = $cacheItem->get();
                    if (is_array($data)) {
                        $data['cache_source'] = 'phpfastcache';
                        $data['cache_hit'] = true;
                    }
                    return $data;
                }
                
                // Cache miss - execute callback
                $data = $callback();
                
                // Store in phpFastCache
                $cacheItem->set($data)->expiresAfter($lifetime);
                $this->fastCache->save($cacheItem);
                
                if (is_array($data)) {
                    $data['cache_source'] = 'phpfastcache_new';
                    $data['cache_hit'] = false;
                }
                return $data;
                
            } catch (\Exception $e) {
                error_log('phpFastCache error in ProductController: ' . $e->getMessage());
                // Continue to regular cache
            }
        }
        
        // Fall back to regular cache
        $data = $this->cache->get($key);
        
        if ($data === null) {
            // Regular cache miss - execute callback
            $data = $callback();
            
            // Store in regular cache
            $this->cache->set($key, $data, $lifetime);
            
            if (is_array($data)) {
                $data['cache_source'] = 'app_cache_new';
                $data['cache_hit'] = false;
            }
        } else {
            // Mark that this came from the regular cache
            if (is_array($data)) {
                $data['cache_source'] = 'app_cache';
                $data['cache_hit'] = true;
            }
        }
        
        return $data;
    }

    /**
     * Get the URL for a product's image with fallback to default
     * 
     * @param array $product The product data array
     * @return string The image URL
     */
    public function getProductImageUrl($product)
    {
        // First check for direct image in the product array
        if (!empty($product['image'])) {
            return $product['image'];
        }
        
        // Then check for product['product']['image'] structure
        if (!empty($product['product']['image'])) {
            return $product['product']['image'];
        }
        
        // Check for primary image from product images
        if (!empty($product['id'])) {
            $primaryImage = $this->productImageModel->getPrimaryImage($product['id']);
            if ($primaryImage) {
                return $primaryImage['image_url'];
            }
        }
        
        // Fallback to default image
        return \App\Core\View::asset('images/products/default.jpg');
    }

    /**
     * Display all products with enhanced caching and async processing
     */
    public function index()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        // Generate cache key based on pagination parameters
        $cacheKey = 'products_page_' . md5(json_encode([
            'page' => $page,
            'limit' => $limit
        ]));
        
        // Try to get data from enhanced cache
        $viewData = $this->getFromEnhancedCache($cacheKey, function() use ($limit, $offset) {
            if ($this->hasAsync) {
                return $this->getProductsPageDataAsync($limit, $offset);
            } else {
                return $this->getProductsPageDataSync($limit, $offset);
            }
        }, 1800); // 30 minutes cache
        
        $this->view('products/index', $viewData);
    }

    /**
     * Get products page data asynchronously
     */
    private function getProductsPageDataAsync($limit, $offset)
    {
        try {
            $pool = $this->asyncPool;
            
            // Start async tasks
            $productsTask = $pool->add(function() use ($limit, $offset) {
                return $this->productModel->getProductsWithImages($limit, $offset);
            });
            
            $totalProductsTask = $pool->add(function() {
                return $this->productModel->getProductCount();
            });
            
            // Wait for all tasks to complete
            $pool->wait();
            
            // Get results
            $products = $productsTask->getOutput();
            $totalProducts = $totalProductsTask->getOutput();
            
            $totalPages = ceil($totalProducts / $limit);
            
            // Check if products are in user's wishlist
            if (Session::has('user_id')) {
                foreach ($products as &$product) {
                    $product['in_wishlist'] = $this->wishlistModel->isInWishlist(Session::get('user_id'), $product['id']);
                }
            }
            
            return [
                'products' => $products,
                'currentPage' => (int)($_GET['page'] ?? 1),
                'totalPages' => $totalPages,
                'title' => 'All Products',
                'cached_at' => date('Y-m-d H:i:s'),
                'load_method' => 'async'
            ];
            
        } catch (\Exception $e) {
            error_log('Async products page data failed: ' . $e->getMessage());
            return $this->getProductsPageDataSync($limit, $offset);
        }
    }

    /**
     * Get products page data synchronously (fallback)
     */
    private function getProductsPageDataSync($limit, $offset)
    {
        $products = $this->productModel->getProductsWithImages($limit, $offset);
        $totalProducts = $this->productModel->getProductCount();
        $totalPages = ceil($totalProducts / $limit);
        
        // Check if products are in user's wishlist
        if (Session::has('user_id')) {
            foreach ($products as &$product) {
                $product['in_wishlist'] = $this->wishlistModel->isInWishlist(Session::get('user_id'), $product['id']);
            }
        }
        
        return [
            'products' => $products,
            'currentPage' => (int)($_GET['page'] ?? 1),
            'totalPages' => $totalPages,
            'title' => 'All Products',
            'cached_at' => date('Y-m-d H:i:s'),
            'load_method' => 'sync'
        ];
    }

    /**
     * Display single product with enhanced caching and async processing
     * 
     * @param string|int $slug Product slug or ID
     */
    public function viewProduct($slug = null)
    {
        if (!$slug) {
            $this->setFlash('error', 'Product not found');
            $this->redirect('products');
            return;
        }
        
        // Generate cache key based on slug/id
        $cacheKey = 'product_view_' . md5($slug);
        
        // Try to get data from enhanced cache
        $viewData = $this->getFromEnhancedCache($cacheKey, function() use ($slug) {
            if ($this->hasAsync) {
                return $this->getProductViewDataAsync($slug);
            } else {
                return $this->getProductViewDataSync($slug);
            }
        }, 3600); // 1 hour cache
        
        if (!$viewData || !isset($viewData['product'])) {
            $this->setFlash('error', 'Product not found');
            $this->redirect('products');
            return;
        }
        
        // Add dynamic data that shouldn't be cached
        if (Session::has('user_id')) {
            if ($this->hasAsync) {
                $this->addUserSpecificDataAsync($viewData);
            } else {
                $this->addUserSpecificDataSync($viewData);
            }
        } else {
            $viewData['inWishlist'] = false;
            $viewData['hasReviewed'] = false;
        }
        
        // Generate QR code for product sharing
        $viewData['qrCode'] = $this->generateProductQRCode($viewData['product']['id'], $viewData['product']['slug'] ?? '');
        
        $this->view('products/view', $viewData);
    }

    /**
     * Get product view data asynchronously
     */
    private function getProductViewDataAsync($slug)
    {
        try {
            $pool = $this->asyncPool;
            
            // Find product first
            $product = $this->productModel->findBySlugWithImages($slug);
            
            // If not found by slug, try by ID (for backward compatibility)
            if (!$product && is_numeric($slug)) {
                $product = $this->productModel->findWithImages($slug);
            }
            
            if (!$product) {
                return null;
            }
            
            // Start async tasks for related data
            $reviewsTask = $pool->add(function() use ($product) {
                return $this->reviewModel->getByProductId($product['id']);
            });
            
            $ratingTask = $pool->add(function() use ($product) {
                return $this->reviewModel->getAverageRating($product['id']);
            });
            
            $reviewCountTask = $pool->add(function() use ($product) {
                return $this->reviewModel->getReviewCount($product['id']);
            });
            
            $relatedProductsTask = $pool->add(function() use ($product) {
                return $this->getLocalRecommendations($product['id']);
            });
            
            // Wait for all tasks to complete
            $pool->wait();
            
            // Get results
            $reviews = $reviewsTask->getOutput();
            $averageRating = $ratingTask->getOutput();
            $reviewCount = $reviewCountTask->getOutput();
            $relatedProducts = $relatedProductsTask->getOutput();
            
            return [
                'product' => $product,
                'reviews' => $reviews,
                'averageRating' => $averageRating,
                'reviewCount' => $reviewCount,
                'relatedProducts' => $relatedProducts,
                'title' => $product['product_name'],
                'cached_at' => date('Y-m-d H:i:s'),
                'load_method' => 'async'
            ];
            
        } catch (\Exception $e) {
            error_log('Async product view data failed: ' . $e->getMessage());
            return $this->getProductViewDataSync($slug);
        }
    }

    /**
     * Get product view data synchronously (fallback)
     */
    private function getProductViewDataSync($slug)
    {
        // Try to find product by slug first
        $product = $this->productModel->findBySlugWithImages($slug);
        
        // If not found by slug, try by ID (for backward compatibility)
        if (!$product && is_numeric($slug)) {
            $product = $this->productModel->findWithImages($slug);
        }
        
        if (!$product) {
            return null;
        }
        
        $reviews = $this->reviewModel->getByProductId($product['id']);
        $averageRating = $this->reviewModel->getAverageRating($product['id']);
        $reviewCount = $this->reviewModel->getReviewCount($product['id']);
        $relatedProducts = $this->getLocalRecommendations($product['id']);
        
        return [
            'product' => $product,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'reviewCount' => $reviewCount,
            'relatedProducts' => $relatedProducts,
            'title' => $product['product_name'],
            'cached_at' => date('Y-m-d H:i:s'),
            'load_method' => 'sync'
        ];
    }

    /**
     * Add user-specific data asynchronously
     */
    private function addUserSpecificDataAsync(&$viewData)
    {
        try {
            $pool = $this->asyncPool;
            
            $inWishlistTask = $pool->add(function() use ($viewData) {
                return $this->wishlistModel->isInWishlist(Session::get('user_id'), $viewData['product']['id']);
            });
            
            $hasReviewedTask = $pool->add(function() use ($viewData) {
                return $this->reviewModel->hasUserReviewed(Session::get('user_id'), $viewData['product']['id']);
            });
            
            // Wait for tasks to complete
            $pool->wait();
            
            $viewData['inWishlist'] = $inWishlistTask->getOutput();
            $viewData['hasReviewed'] = $hasReviewedTask->getOutput();
            
        } catch (\Exception $e) {
            error_log('Async user-specific data failed: ' . $e->getMessage());
            $this->addUserSpecificDataSync($viewData);
        }
    }

    /**
     * Add user-specific data synchronously (fallback)
     */
    private function addUserSpecificDataSync(&$viewData)
    {
        $viewData['inWishlist'] = $this->wishlistModel->isInWishlist(Session::get('user_id'), $viewData['product']['id']);
        $viewData['hasReviewed'] = $this->reviewModel->hasUserReviewed(Session::get('user_id'), $viewData['product']['id']);
    }

    /**
     * Generate QR code for product sharing
     * 
     * @param int $productId
     * @param string $slug
     * @return string|null Base64 encoded QR code image
     */
    private function generateProductQRCode($productId, $slug = '')
    {
        // Check if Bacon QR Code library is available
        if (!class_exists('BaconQrCode\Writer')) {
            error_log('BaconQrCode library not found. Please run: composer require bacon/bacon-qr-code');
            return null;
        }
        
        try {
            // Generate the product share URL
            $baseUrl = isset($_SERVER['HTTP_HOST']) ? 
                ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST']) : 
                'https://example.com';
            
            $shareUrl = $baseUrl . \App\Core\View::url('products/view/' . ($slug ?: $productId));
            
            // Create QR code using SVG backend for better compatibility
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            
            $writer = new \BaconQrCode\Writer($renderer);
            
            // Generate QR code as SVG
            $qrCode = $writer->writeString($shareUrl);
            
            // Return the SVG as a base64 encoded string for embedding in HTML
            return 'data:image/svg+xml;base64,' . base64_encode($qrCode);
        } catch (\Exception $e) {
            // Log the error
            error_log('QR code generation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Search products with enhanced caching and async processing
     */
    public function search()
    {
        $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        if (empty($keyword)) {
            $this->setFlash('error', 'Please enter a search keyword');
            $this->redirect('products');
            return;
        }
        
        // Generate cache key based on search keyword
        $cacheKey = 'product_search_' . md5($keyword);
        
        // Try to get data from enhanced cache
        $viewData = $this->getFromEnhancedCache($cacheKey, function() use ($keyword) {
            if ($this->hasAsync) {
                return $this->getSearchDataAsync($keyword);
            } else {
                return $this->getSearchDataSync($keyword);
            }
        }, 900); // 15 minutes cache for search results
        
        // Add dynamic data that shouldn't be cached
        if (Session::has('user_id')) {
            $this->addWishlistStatusToProducts($viewData['products']);
        }
        
        $this->view('products/search', $viewData);
    }

    /**
     * Get search data asynchronously
     */
    private function getSearchDataAsync($keyword)
    {
        try {
            $pool = $this->asyncPool;
            
            $productsTask = $pool->add(function() use ($keyword) {
                $products = $this->productModel->searchProducts($keyword);
                
                // Add images to each product
                foreach ($products as &$product) {
                    $product['images'] = $this->productImageModel->getByProductId($product['id']);
                    $product['primary_image'] = $this->productImageModel->getPrimaryImage($product['id']);
                    
                    if ($product['primary_image']) {
                        $product['image'] = $product['primary_image']['image_url'];
                    }
                }
                
                return $products;
            });
            
            // Wait for task to complete
            $pool->wait();
            
            $products = $productsTask->getOutput();
            
            return [
                'products' => $products,
                'keyword' => $keyword,
                'title' => 'Search Results: ' . $keyword,
                'cached_at' => date('Y-m-d H:i:s'),
                'load_method' => 'async'
            ];
            
        } catch (\Exception $e) {
            error_log('Async search data failed: ' . $e->getMessage());
            return $this->getSearchDataSync($keyword);
        }
    }

    /**
     * Get search data synchronously (fallback)
     */
    private function getSearchDataSync($keyword)
    {
        $products = $this->productModel->searchProducts($keyword);
        
        // Add images to each product
        foreach ($products as &$product) {
            $product['images'] = $this->productImageModel->getByProductId($product['id']);
            $product['primary_image'] = $this->productImageModel->getPrimaryImage($product['id']);
            
            if ($product['primary_image']) {
                $product['image'] = $product['primary_image']['image_url'];
            }
        }
        
        return [
            'products' => $products,
            'keyword' => $keyword,
            'title' => 'Search Results: ' . $keyword,
            'cached_at' => date('Y-m-d H:i:s'),
            'load_method' => 'sync'
        ];
    }

    /**
     * Add wishlist status to products array
     */
    private function addWishlistStatusToProducts(&$products)
    {
        if ($this->hasAsync && !empty($products)) {
            try {
                $pool = $this->asyncPool;
                $wishlistTasks = [];
                
                foreach ($products as $index => $product) {
                    $wishlistTasks[$index] = $pool->add(function() use ($product) {
                        return [
                            'index' => $product['id'],
                            'in_wishlist' => $this->wishlistModel->isInWishlist(Session::get('user_id'), $product['id'])
                        ];
                    });
                }
                
                // Wait for all tasks to complete
                $pool->wait();
                
                // Process results
                foreach ($wishlistTasks as $index => $task) {
                    $result = $task->getOutput();
                    $products[$index]['in_wishlist'] = $result['in_wishlist'];
                }
                
            } catch (\Exception $e) {
                error_log('Async wishlist status failed: ' . $e->getMessage());
                // Fall back to sync
                foreach ($products as &$product) {
                    $product['in_wishlist'] = $this->wishlistModel->isInWishlist(Session::get('user_id'), $product['id']);
                }
            }
        } else {
            // Standard approach without async
            foreach ($products as &$product) {
                $product['in_wishlist'] = $this->wishlistModel->isInWishlist(Session::get('user_id'), $product['id']);
            }
        }
    }

    /**
     * Search products by flavor with enhanced caching
     */
    public function searchByFlavor()
    {
        $flavor = isset($_GET['flavor']) ? trim($_GET['flavor']) : '';
        
        if (empty($flavor)) {
            $this->setFlash('error', 'Please specify a flavor');
            $this->redirect('products');
            return;
        }
        
        // Generate cache key based on flavor
        $cacheKey = 'product_flavor_search_' . md5($flavor);
        
        // Try to get data from enhanced cache
        $viewData = $this->getFromEnhancedCache($cacheKey, function() use ($flavor) {
            if ($this->hasAsync) {
                return $this->getFlavorSearchDataAsync($flavor);
            } else {
                return $this->getFlavorSearchDataSync($flavor);
            }
        }, 900); // 15 minutes cache
        
        // Add dynamic data that shouldn't be cached
        if (Session::has('user_id')) {
            $this->addWishlistStatusToProducts($viewData['products']);
        }
        
        $this->view('products/flavor', $viewData);
    }

    /**
     * Get flavor search data asynchronously
     */
    private function getFlavorSearchDataAsync($flavor)
    {
        try {
            $pool = $this->asyncPool;
            
            $productsTask = $pool->add(function() use ($flavor) {
                $sql = "SELECT * FROM products WHERE flavor LIKE ? ORDER BY id DESC";
                return $this->productModel->query($sql, ['%' . $flavor . '%']);
            });
            
            // Wait for task to complete
            $pool->wait();
            
            $products = $productsTask->getOutput();
            
            return [
                'products' => $products,
                'flavor' => $flavor,
                'title' => 'Products with ' . $flavor . ' Flavor',
                'cached_at' => date('Y-m-d H:i:s'),
                'load_method' => 'async'
            ];
            
        } catch (\Exception $e) {
            error_log('Async flavor search data failed: ' . $e->getMessage());
            return $this->getFlavorSearchDataSync($flavor);
        }
    }

    /**
     * Get flavor search data synchronously (fallback)
     */
    private function getFlavorSearchDataSync($flavor)
    {
        $sql = "SELECT * FROM products WHERE flavor LIKE ? ORDER BY id DESC";
        $products = $this->productModel->query($sql, ['%' . $flavor . '%']);
        
        return [
            'products' => $products,
            'flavor' => $flavor,
            'title' => 'Products with ' . $flavor . ' Flavor',
            'cached_at' => date('Y-m-d H:i:s'),
            'load_method' => 'sync'
        ];
    }

    /**
     * Filter products by capsule type with enhanced caching
     */
    public function filterByCapsule($isCapsule = 1)
    {
        // Generate cache key based on capsule filter
        $cacheKey = 'product_capsule_filter_' . md5($isCapsule);
        
        // Try to get data from enhanced cache
        $viewData = $this->getFromEnhancedCache($cacheKey, function() use ($isCapsule) {
            if ($this->hasAsync) {
                return $this->getCapsuleFilterDataAsync($isCapsule);
            } else {
                return $this->getCapsuleFilterDataSync($isCapsule);
            }
        }, 3600); // 1 hour cache
        
        // Add dynamic data that shouldn't be cached
        if (Session::has('user_id')) {
            $this->addWishlistStatusToProducts($viewData['products']);
        }
        
        $this->view('products/capsule', $viewData);
    }

    /**
     * Get capsule filter data asynchronously
     */
    private function getCapsuleFilterDataAsync($isCapsule)
    {
        try {
            $pool = $this->asyncPool;
            
            $productsTask = $pool->add(function() use ($isCapsule) {
                $sql = "SELECT * FROM products WHERE capsule = ? ORDER BY id DESC";
                return $this->productModel->query($sql, [$isCapsule]);
            });
            
            // Wait for task to complete
            $pool->wait();
            
            $products = $productsTask->getOutput();
            
            return [
                'products' => $products,
                'isCapsule' => $isCapsule,
                'title' => $isCapsule ? 'Capsule Products' : 'Non-Capsule Products',
                'cached_at' => date('Y-m-d H:i:s'),
                'load_method' => 'async'
            ];
            
        } catch (\Exception $e) {
            error_log('Async capsule filter data failed: ' . $e->getMessage());
            return $this->getCapsuleFilterDataSync($isCapsule);
        }
    }

    /**
     * Get capsule filter data synchronously (fallback)
     */
    private function getCapsuleFilterDataSync($isCapsule)
    {
        $sql = "SELECT * FROM products WHERE capsule = ? ORDER BY id DESC";
        $products = $this->productModel->query($sql, [$isCapsule]);
        
        return [
            'products' => $products,
            'isCapsule' => $isCapsule,
            'title' => $isCapsule ? 'Capsule Products' : 'Non-Capsule Products',
            'cached_at' => date('Y-m-d H:i:s'),
            'load_method' => 'sync'
        ];
    }

    /**
     * Submit a product review with async processing and cache invalidation
     *
     * @return void
     */
    public function submitReview()
    {
        // Check if user is logged in
        if (!Session::has('user_id')) {
            $this->setFlash('error', 'Please login to submit a review');
            $this->redirect('auth/login');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            $this->redirect('products');
            return;
        }
        
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $review = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
        
        // Validate input with async processing
        try {
            if ($this->hasAsync) {
                $this->validateReviewAsync($productId, $rating, $review);
            } else {
                $this->validateReviewSync($productId, $rating, $review);
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Validation failed due to an error: ' . $e->getMessage());
            $this->redirect('products/view/' . ($this->productModel->find($productId)['slug'] ?? $productId));
            return;
        }
    }

    /**
     * Validate review asynchronously
     */
    private function validateReviewAsync($productId, $rating, $review)
    {
        try {
            $pool = $this->asyncPool;
            
            $productTask = $pool->add(function() use ($productId) {
                return $this->productModel->find($productId);
            });
            
            $hasReviewedTask = $pool->add(function() use ($productId) {
                return $this->reviewModel->hasUserReviewed(Session::get('user_id'), $productId);
            });
            
            // Wait for tasks to complete
            $pool->wait();
            
            $product = $productTask->getOutput();
            $hasReviewed = $hasReviewedTask->getOutput();
            
            $this->processReviewSubmission($productId, $rating, $review, $product, $hasReviewed);
            
        } catch (\Exception $e) {
            error_log('Async review validation failed: ' . $e->getMessage());
            $this->validateReviewSync($productId, $rating, $review);
        }
    }

    /**
     * Validate review synchronously (fallback)
     */
    private function validateReviewSync($productId, $rating, $review)
    {
        $product = $this->productModel->find($productId);
        $hasReviewed = $this->reviewModel->hasUserReviewed(Session::get('user_id'), $productId);
        
        $this->processReviewSubmission($productId, $rating, $review, $product, $hasReviewed);
    }

    /**
     * Process review submission
     */
    private function processReviewSubmission($productId, $rating, $review, $product, $hasReviewed)
    {
        // Validate input
        $errors = [];
        
        if (!$productId || !$product) {
            $errors['product_id'] = 'Invalid product';
        }
        
        if ($hasReviewed) {
            $errors['general'] = 'You have already reviewed this product';
        }
        
        if ($rating < 1 || $rating > 5) {
            $errors['rating'] = 'Rating must be between 1 and 5';
        }
        
        if (empty($review) || strlen($review) < 10) {
            $errors['review'] = 'Review must be at least 10 characters long';
        }
        
        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', array_values($errors)));
            $this->redirect('products/view/' . ($product['slug'] ?? $productId));
            return;
        }
        
        // Add review
        $data = [
            'product_id' => $productId,
            'user_id' => Session::get('user_id'),
            'rating' => $rating,
            'review' => $review
        ];
        
        try {
            $result = $this->reviewModel->create($data);
            if ($result) {
                // Invalidate cache for this product asynchronously
                if ($this->hasAsync) {
                    $this->asyncPool->add(function() use ($productId, $product) {
                        $this->invalidateProductCache($productId, $product['slug'] ?? '');
                        return true;
                    });
                } else {
                    // Synchronous cache invalidation
                    $this->invalidateProductCache($productId, $product['slug'] ?? '');
                }
                
                $this->setFlash('success', 'Your review has been submitted successfully');
            } else {
                $this->setFlash('error', 'Failed to submit your review due to a database error');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'An error occurred while submitting your review: ' . $e->getMessage());
        }
        
        $this->redirect('products/view/' . ($product['slug'] ?? $productId));
    }

    /**
     * Display products by category with enhanced caching and async processing
     * 
     * @param string $category Category name
     */
    public function category($category = null)
    {
        if (!$category) {
            $this->setFlash('error', 'Category not specified');
            $this->redirect('products');
            return;
        }
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        // Generate cache key based on category and pagination
        $cacheKey = 'product_category_' . md5(json_encode([
            'category' => $category,
            'page' => $page,
            'limit' => $limit
        ]));
        
        // Try to get data from enhanced cache
        $viewData = $this->getFromEnhancedCache($cacheKey, function() use ($category, $limit, $offset) {
            if ($this->hasAsync) {
                return $this->getCategoryDataAsync($category, $limit, $offset);
            } else {
                return $this->getCategoryDataSync($category, $limit, $offset);
            }
        }, 3600); // 1 hour cache
        
        // Add dynamic data that shouldn't be cached
        if (Session::has('user_id')) {
            $this->addWishlistStatusToProducts($viewData['products']);
        }
        
        $this->view('products/category', $viewData);
    }

    /**
     * Get category data asynchronously
     */
    private function getCategoryDataAsync($category, $limit, $offset)
    {
        try {
            $pool = $this->asyncPool;
            
            $productsTask = $pool->add(function() use ($category, $limit, $offset) {
                $products = $this->productModel->getProductsByCategory($category, $limit, $offset);
                
                // Add images to each product
                foreach ($products as &$product) {
                    $product['images'] = $this->productImageModel->getByProductId($product['id']);
                    $product['primary_image'] = $this->productImageModel->getPrimaryImage($product['id']);
                    
                    if ($product['primary_image']) {
                        $product['image'] = $product['primary_image']['image_url'];
                    }
                }
                
                return $products;
            });
            
            $totalProductsTask = $pool->add(function() use ($category) {
                return $this->productModel->getProductCountByCategory($category);
            });
            
            // Wait for tasks to complete
            $pool->wait();
            
            $products = $productsTask->getOutput();
            $totalProducts = $totalProductsTask->getOutput();
            $totalPages = ceil($totalProducts / $limit);
            
            return [
                'products' => $products,
                'category' => $category,
                'currentPage' => (int)($_GET['page'] ?? 1),
                'totalPages' => $totalPages,
                'title' => $category . ' Products',
                'cached_at' => date('Y-m-d H:i:s'),
                'load_method' => 'async'
            ];
            
        } catch (\Exception $e) {
            error_log('Async category data failed: ' . $e->getMessage());
            return $this->getCategoryDataSync($category, $limit, $offset);
        }
    }

    /**
     * Get category data synchronously (fallback)
     */
    private function getCategoryDataSync($category, $limit, $offset)
    {
        $products = $this->productModel->getProductsByCategory($category, $limit, $offset);
        
        // Add images to each product
        foreach ($products as &$product) {
            $product['images'] = $this->productImageModel->getByProductId($product['id']);
            $product['primary_image'] = $this->productImageModel->getPrimaryImage($product['id']);
            
            if ($product['primary_image']) {
                $product['image'] = $product['primary_image']['image_url'];
            }
        }
        
        $totalProducts = $this->productModel->getProductCountByCategory($category);
        $totalPages = ceil($totalProducts / $limit);
        
        return [
            'products' => $products,
            'category' => $category,
            'currentPage' => (int)($_GET['page'] ?? 1),
            'totalPages' => $totalPages,
            'title' => $category . ' Products',
            'cached_at' => date('Y-m-d H:i:s'),
            'load_method' => 'sync'
        ];
    }

    /**
     * Fetch product recommendations from external API with enhanced caching
     * 
     * @param int $productId
     * @return array
     */
    public function getRecommendations($productId)
    {
        // Generate cache key
        $cacheKey = 'product_recommendations_' . md5($productId);
        
        // Try to get from enhanced cache
        $recommendations = $this->getFromEnhancedCache($cacheKey, function() use ($productId) {
            if ($this->hasAsync) {
                return $this->getRecommendationsAsync($productId);
            } else {
                return $this->getRecommendationsSync($productId);
            }
        }, 21600); // 6 hours cache
        
        return $recommendations;
    }

    /**
     * Get recommendations asynchronously
     */
    private function getRecommendationsAsync($productId)
    {
        try {
            $pool = $this->asyncPool;
            
            $guzzleTask = $pool->add(function() use ($productId) {
                return $this->fetchWithGuzzle('recommendations', ['product_id' => $productId]);
            });
            
            $curlTask = $pool->add(function() use ($productId) {
                return $this->fetchWithCurl('recommendations', ['product_id' => $productId]);
            });
            
            $localTask = $pool->add(function() use ($productId) {
                return $this->getLocalRecommendations($productId);
            });
            
            // Wait for tasks to complete
            $pool->wait();
            
            // Get results and use the first successful one
            $guzzleResult = $guzzleTask->getOutput();
            $curlResult = $curlTask->getOutput();
            $localResult = $localTask->getOutput();
            
            // Use the first available result
            if (!empty($guzzleResult)) {
                return $guzzleResult;
            } elseif (!empty($curlResult)) {
                return $curlResult;
            } elseif (!empty($localResult)) {
                return $localResult;
            } else {
                return [];
            }
            
        } catch (\Exception $e) {
            error_log('Async recommendations failed: ' . $e->getMessage());
            return $this->getRecommendationsSync($productId);
        }
    }

    /**
     * Get recommendations synchronously (fallback)
     */
    private function getRecommendationsSync($productId)
    {
        $recommendations = $this->fetchWithGuzzle('recommendations', ['product_id' => $productId]);
        
        if ($recommendations === null) {
            // Fallback to cURL if Guzzle fails
            $recommendations = $this->fetchWithCurl('recommendations', ['product_id' => $productId]);
        }
        
        if (empty($recommendations)) {
            // If both methods fail or return empty, use local fallback
            $recommendations = $this->getLocalRecommendations($productId);
        }
        
        return $recommendations;
    }

    /**
     * Get local product recommendations based on category with enhanced caching
     * 
     * @param int $productId
     * @param int $limit
     * @return array
     */
    private function getLocalRecommendations($productId, $limit = 4)
    {
        // Generate cache key
        $cacheKey = 'local_recommendations_' . md5(json_encode([
            'product_id' => $productId,
            'limit' => $limit
        ]));
        
        // Try to get from enhanced cache
        $recommendations = $this->getFromEnhancedCache($cacheKey, function() use ($productId, $limit) {
            if ($this->hasAsync) {
                return $this->getLocalRecommendationsAsync($productId, $limit);
            } else {
                return $this->getLocalRecommendationsSync($productId, $limit);
            }
        }, 10800); // 3 hours cache
        
        return $recommendations;
    }

    /**
     * Get local recommendations asynchronously
     */
    private function getLocalRecommendationsAsync($productId, $limit)
    {
        try {
            $pool = $this->asyncPool;
            
            $productTask = $pool->add(function() use ($productId) {
                return $this->productModel->find($productId);
            });
            
            // Wait for product task to complete
            $pool->wait();
            
            $product = $productTask->getOutput();
            
            if (!$product) {
                return [];
            }
            
            $category = $product['category'] ?? '';
            
            if (empty($category)) {
                return [];
            }
            
            // Now fetch related products
            $relatedTask = $pool->add(function() use ($productId, $category, $limit) {
                $results = $this->productModel->getRelatedProducts($productId, $category, $limit);
                
                // Add images to each product
                foreach ($results as &$relatedProduct) {
                    $relatedProduct['images'] = $this->productImageModel->getByProductId($relatedProduct['id']);
                    $relatedProduct['primary_image'] = $this->productImageModel->getPrimaryImage($relatedProduct['id']);
                    
                    if ($relatedProduct['primary_image']) {
                        $relatedProduct['image'] = $relatedProduct['primary_image']['image_url'];
                    }
                }
                
                return $results;
            });
            
            // Wait for related products task to complete
            $pool->wait();
            
            return $relatedTask->getOutput();
            
        } catch (\Exception $e) {
            error_log('Async local recommendations failed: ' . $e->getMessage());
            return $this->getLocalRecommendationsSync($productId, $limit);
        }
    }

    /**
     * Get local recommendations synchronously (fallback)
     */
    private function getLocalRecommendationsSync($productId, $limit)
    {
        $product = $this->productModel->find($productId);
        
        if (!$product) {
            return [];
        }
        
        $category = $product['category'] ?? '';
        
        if (empty($category)) {
            return [];
        }
        
        $results = $this->productModel->getRelatedProducts($productId, $category, $limit);
        
        // Add images to each product
        foreach ($results as &$relatedProduct) {
            $relatedProduct['images'] = $this->productImageModel->getByProductId($relatedProduct['id']);
            $relatedProduct['primary_image'] = $this->productImageModel->getPrimaryImage($relatedProduct['id']);
            
            if ($relatedProduct['primary_image']) {
                $relatedProduct['image'] = $relatedProduct['primary_image']['image_url'];
            }
        }
        
        return is_array($results) ? $results : [];
    }

    /**
     * Get similar products based on flavor with enhanced caching
     * 
     * @param int $productId
     * @param int $limit
     * @return array
     */
    public function getSimilarFlavorProducts($productId, $limit = 4)
    {
        // Generate cache key
        $cacheKey = 'similar_flavor_products_' . md5(json_encode([
            'product_id' => $productId,
            'limit' => $limit
        ]));
        
        // Try to get from enhanced cache
        $similarProducts = $this->getFromEnhancedCache($cacheKey, function() use ($productId, $limit) {
            if ($this->hasAsync) {
                return $this->getSimilarFlavorProductsAsync($productId, $limit);
            } else {
                return $this->getSimilarFlavorProductsSync($productId, $limit);
            }
        }, 10800); // 3 hours cache
        
        return $similarProducts;
    }

    /**
     * Get similar flavor products asynchronously
     */
    private function getSimilarFlavorProductsAsync($productId, $limit)
    {
        try {
            $pool = $this->asyncPool;
            
            $productTask = $pool->add(function() use ($productId) {
                return $this->productModel->find($productId);
            });
            
            // Wait for product task to complete
            $pool->wait();
            
            $product = $productTask->getOutput();
            
            if (!$product || empty($product['flavor'])) {
                return [];
            }
            
            // Now fetch similar flavor products
            $similarTask = $pool->add(function() use ($product, $productId, $limit) {
                $sql = "SELECT * FROM products WHERE flavor LIKE ? AND id != ? ORDER BY RAND() LIMIT ?";
                return $this->productModel->query($sql, ['%' . $product['flavor'] . '%', $productId, $limit]);
            });
            
            // Wait for similar products task to complete
            $pool->wait();
            
            return $similarTask->getOutput();
            
        } catch (\Exception $e) {
            error_log('Async similar flavor products failed: ' . $e->getMessage());
            return $this->getSimilarFlavorProductsSync($productId, $limit);
        }
    }

    /**
     * Get similar flavor products synchronously (fallback)
     */
    private function getSimilarFlavorProductsSync($productId, $limit)
    {
        $product = $this->productModel->find($productId);
        
        if (!$product || empty($product['flavor'])) {
            return [];
        }
        
        $sql = "SELECT * FROM products WHERE flavor LIKE ? AND id != ? ORDER BY RAND() LIMIT ?";
        $results = $this->productModel->query($sql, ['%' . $product['flavor'] . '%', $productId, $limit]);
        
        return is_array($results) ? $results : [];
    }

    /**
     * Fetch trending products from external API with enhanced caching
     * 
     * @param int $limit
     * @return array
     */
    public function getTrendingProducts($limit = 8)
    {
        // Generate cache key
        $cacheKey = 'trending_products_' . md5($limit);
        
        // Try to get from enhanced cache
        $trending = $this->getFromEnhancedCache($cacheKey, function() use ($limit) {
            if ($this->hasAsync) {
                return $this->getTrendingProductsAsync($limit);
            } else {
                return $this->getTrendingProductsSync($limit);
            }
        }, 10800); // 3 hours cache
        
        return $trending;
    }

    /**
     * Get trending products asynchronously
     */
    private function getTrendingProductsAsync($limit)
    {
        try {
            $pool = $this->asyncPool;
            
            $guzzleTask = $pool->add(function() use ($limit) {
                return $this->fetchWithGuzzle('trending', ['limit' => $limit]);
            });
            
            $curlTask = $pool->add(function() use ($limit) {
                return $this->fetchWithCurl('trending', ['limit' => $limit]);
            });
            
            $featuredTask = $pool->add(function() use ($limit) {
                return $this->productModel->getFeaturedProducts($limit);
            });
            
            // Wait for tasks to complete
            $pool->wait();
            
            // Get results and use the first successful one
            $guzzleResult = $guzzleTask->getOutput();
            $curlResult = $curlTask->getOutput();
            $featuredResult = $featuredTask->getOutput();
            
            // Use the first available result
            if (!empty($guzzleResult)) {
                return $guzzleResult;
            } elseif (!empty($curlResult)) {
                return $curlResult;
            } elseif (!empty($featuredResult)) {
                return $featuredResult;
            } else {
                return [];
            }
            
        } catch (\Exception $e) {
            error_log('Async trending products failed: ' . $e->getMessage());
            return $this->getTrendingProductsSync($limit);
        }
    }

    /**
     * Get trending products synchronously (fallback)
     */
    private function getTrendingProductsSync($limit)
    {
        $trending = $this->fetchWithGuzzle('trending', ['limit' => $limit]);
        
        if ($trending === null) {
            // Fallback to cURL if Guzzle fails
            $trending = $this->fetchWithCurl('trending', ['limit' => $limit]);
        }
        
        if (empty($trending)) {
            // If both methods fail or return empty, use local fallback
            $trending = $this->productModel->getFeaturedProducts($limit);
        }
        
        return $trending;
    }

    /**
     * API endpoint to get product information with enhanced caching
     * 
     * @param int $id
     * @return void
     */
    public function apiGetProduct($id = null)
    {
        if (!$id) {
            echo json_encode(['error' => 'Product ID is required']);
            return;
        }
        
        // Generate cache key
        $cacheKey = 'api_product_' . md5($id);
        
        // Try to get from enhanced cache
        $response = $this->getFromEnhancedCache($cacheKey, function() use ($id) {
            if ($this->hasAsync) {
                return $this->getApiProductDataAsync($id);
            } else {
                return $this->getApiProductDataSync($id);
            }
        }, 3600); // 1 hour cache
        
        // Return product data as JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Get API product data asynchronously
     */
    private function getApiProductDataAsync($id)
    {
        try {
            $pool = $this->asyncPool;
            
            $productTask = $pool->add(function() use ($id) {
                return $this->productModel->find($id);
            });
            
            // Wait for task to complete
            $pool->wait();
            
            $product = $productTask->getOutput();
            
            if (!$product) {
                return ['error' => 'Product not found'];
            }
            
            return [
                'success' => true,
                'product' => $product,
                'cached_at' => date('Y-m-d H:i:s'),
                'load_method' => 'async'
            ];
            
        } catch (\Exception $e) {
            error_log('Async API product data failed: ' . $e->getMessage());
            return $this->getApiProductDataSync($id);
        }
    }

    /**
     * Get API product data synchronously (fallback)
     */
    private function getApiProductDataSync($id)
    {
        $product = $this->productModel->find($id);
        
        if (!$product) {
            return ['error' => 'Product not found'];
        }
        
        return [
            'success' => true,
            'product' => $product,
            'cached_at' => date('Y-m-d H:i:s'),
            'load_method' => 'sync'
        ];
    }

    /**
     * Sync product data with external API and invalidate cache
     * 
     * @return bool
     */
    public function syncProducts()
    {
        // Only allow admin users to sync products
        if (!Session::has('user_id') || Session::get('user_role') !== 'admin') {
            $this->setFlash('error', 'You do not have permission to perform this action');
            $this->redirect('products');
            return false;
        }
        
        // Use async to try multiple syncing methods in parallel if available
        if ($this->hasAsync) {
            $synced = $this->syncProductsAsync();
        } else {
            $synced = $this->syncProductsSync();
        }
        
        if ($synced) {
            // Clear all product-related caches asynchronously
            if ($this->hasAsync) {
                $this->asyncPool->add(function() {
                    $this->clearAllProductCache();
                    return true;
                });
            } else {
                // Synchronous cache clearing
                $this->clearAllProductCache();
            }
            
            $this->setFlash('success', 'Products synchronized successfully');
        } else {
            $this->setFlash('error', 'Failed to synchronize products');
        }
        
        $this->redirect('admin/products');
        return $synced;
    }

    /**
     * Sync products asynchronously
     */
    private function syncProductsAsync()
    {
        try {
            $pool = $this->asyncPool;
            
            $guzzleTask = $pool->add(function() {
                return $this->syncWithGuzzle();
            });
            
            $curlTask = $pool->add(function() {
                return $this->syncWithCurl();
            });
            
            // Wait for tasks to complete
            $pool->wait();
            
            // Get results and use the first successful one
            $guzzleResult = $guzzleTask->getOutput();
            $curlResult = $curlTask->getOutput();
            
            return $guzzleResult || $curlResult;
            
        } catch (\Exception $e) {
            error_log('Async product sync failed: ' . $e->getMessage());
            return $this->syncProductsSync();
        }
    }

    /**
     * Sync products synchronously (fallback)
     */
    private function syncProductsSync()
    {
        $synced = $this->syncWithGuzzle();
        
        if (!$synced) {
            // Fallback to cURL if Guzzle fails
            $synced = $this->syncWithCurl();
        }
        
        return $synced;
    }

    /**
     * Clear all enhanced caches
     */
    public function clearProductCache()
    {
        // Only allow admin users to clear cache
        if (!Session::has('user_id') || Session::get('user_role') !== 'admin') {
            $this->setFlash('error', 'You do not have permission to perform this action');
            $this->redirect('products');
            return;
        }
        
        $clearedCaches = [];
        
        // Clear regular cache
        $this->cache->clear();
        $clearedCaches[] = 'App Cache';
        
        // Clear phpFastCache if available
        if ($this->hasFastCache && $this->fastCache) {
            try {
                $this->fastCache->clear();
                $clearedCaches[] = 'phpFastCache';
            } catch (\Exception $e) {
                error_log('Failed to clear phpFastCache: ' . $e->getMessage());
            }
        }
        
        $this->setFlash('success', 'Cleared caches: ' . implode(', ', $clearedCaches));
        $this->redirect('admin/products');
    }

    /**
     * Invalidate cache for a specific product
     * 
     * @param int $productId
     * @param string $slug
     */
    private function invalidateProductCache($productId, $slug = '')
    {
        $keysToDelete = [
            'product_view_' . md5($slug),
            'product_view_' . md5($productId),
            'api_product_' . md5($productId),
            'product_recommendations_' . md5($productId),
            'local_recommendations_' . md5(json_encode(['product_id' => $productId])),
            'similar_flavor_products_' . md5(json_encode(['product_id' => $productId]))
        ];
        
        // Clear from both caches
        foreach ($keysToDelete as $key) {
            // Clear from regular cache
            $this->cache->delete($key);
            
            // Clear from phpFastCache if available
            if ($this->hasFastCache && $this->fastCache) {
                try {
                    $this->fastCache->deleteItem($key);
                } catch (\Exception $e) {
                    error_log('Failed to delete from phpFastCache: ' . $e->getMessage());
                }
            }
        }
        
        // Clear category and product listing caches
        $this->clearListingCaches();
    }

    /**
     * Clear all product listing related caches
     */
    private function clearListingCaches()
    {
        $listingKeys = [
            'home_page_data',
            'products_page_',
            'product_search_',
            'product_flavor_search_',
            'product_capsule_filter_',
            'product_category_',
            'trending_products_'
        ];
        
        foreach ($listingKeys as $keyPrefix) {
            // For regular cache, we need to clear specific keys or all
            $this->cache->delete($keyPrefix);
            
            // For phpFastCache, clear items with prefix
            if ($this->hasFastCache && $this->fastCache) {
                try {
                    // phpFastCache doesn't have a direct way to delete by prefix
                    // So we'll clear all cache for simplicity
                    $this->fastCache->clear();
                    break; // No need to continue if we clear all
                } catch (\Exception $e) {
                    error_log('Failed to clear phpFastCache listings: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Clear all product-related caches
     */
    private function clearAllProductCache()
    {
        // Clear regular cache
        $this->cache->clear();
        
        // Clear phpFastCache if available
        if ($this->hasFastCache && $this->fastCache) {
            try {
                $this->fastCache->clear();
            } catch (\Exception $e) {
                error_log('Failed to clear all phpFastCache: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get cache statistics for products
     */
    public function getCacheStats()
    {
        // Only allow admin users to view cache stats
        if (!Session::has('user_id') || Session::get('user_role') !== 'admin') {
            $this->setFlash('error', 'You do not have permission to perform this action');
            $this->redirect('products');
            return;
        }
        
        $stats = [
            'phpfastcache' => ['available' => $this->hasFastCache, 'stats' => null],
            'spatie_async' => ['available' => $this->hasAsync, 'stats' => null],
            'app_cache' => ['available' => true, 'stats' => null]
        ];
        
        // Get phpFastCache stats
        if ($this->hasFastCache && $this->fastCache) {
            try {
                $stats['phpfastcache']['stats'] = $this->fastCache->getStats();
            } catch (\Exception $e) {
                $stats['phpfastcache']['error'] = $e->getMessage();
            }
        }
        
        // Get app cache stats
        try {
            $stats['app_cache']['stats'] = [
                'class' => get_class($this->cache),
                'available' => true
            ];
        } catch (\Exception $e) {
            $stats['app_cache']['error'] = $e->getMessage();
        }
        
        $this->view('admin/product-cache-stats', [
            'title' => 'Product Cache Statistics',
            'stats' => $stats,
        ]);
    }

    /**
     * Fetch data using Guzzle HTTP client
     * 
     * @param string $endpoint
     * @param array $params
     * @return array|null
     */
    private function fetchWithGuzzle($endpoint, $params = [])
    {
        // Check if Guzzle is available
        if (!class_exists('GuzzleHttp\Client')) {
            return null;
        }
        
        try {
            // Create a Guzzle client
            $client = new \GuzzleHttp\Client([
                'base_uri' => 'https://api.nutrinexus.com/v1/',
                'timeout' => 10.0,
                'verify' => false, // Disable SSL verification for development
            ]);
            
            // Make the request
            $response = $client->request('GET', $endpoint, [
                'query' => $params,
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->getApiKey(),
                ],
            ]);
            
            // Parse the response
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $data = json_decode($response->getBody()->getContents(), true);
                return $data['data'] ?? [];
            }
            
            return null;
        } catch (\Exception $e) {
            // Log the error
            error_log('Guzzle request error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Fetch data using cURL (fallback method)
     * 
     * @param string $endpoint
     * @param array $params
     * @return array|null
     */
    private function fetchWithCurl($endpoint, $params = [])
    {
        // Check if cURL is available
        if (!function_exists('curl_init')) {
            return null;
        }
        
        try {
            // Build the URL with query parameters
            $url = 'https://api.nutrinexus.com/v1/' . $endpoint;
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
            
            // Initialize cURL
            $ch = curl_init($url);
            
            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Authorization: Bearer ' . $this->getApiKey(),
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for development
            
            // Execute the request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Close cURL
            curl_close($ch);
            
            // Parse the response
            if ($httpCode >= 200 && $httpCode < 300) {
                $data = json_decode($response, true);
                return $data['data'] ?? [];
            }
            
            return null;
        } catch (\Exception $e) {
            // Log the error
            error_log('cURL request error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get API key from configuration
     * 
     * @return string
     */
    private function getApiKey()
    {
        // Return API key from configuration or environment variable
        // Use defined() to check if the constant exists
        return defined('API_KEY') ? API_KEY : 'default-api-key';
    }
    
    /**
     * Sync products using Guzzle
     * 
     * @return bool
     */
    private function syncWithGuzzle()
    {
        // Check if Guzzle is available
        if (!class_exists('GuzzleHttp\Client')) {
            return false;
        }
        
        try {
            // Create a Guzzle client
            $client = new \GuzzleHttp\Client([
                'base_uri' => 'https://api.nutrinexus.com/v1/',
                'timeout' => 30.0,
                'verify' => false, // Disable SSL verification for development
            ]);
            
            // Make the request
            $response = $client->request('GET', 'products/sync', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->getApiKey(),
                ],
            ]);
            
            // Parse the response
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $data = json_decode($response->getBody()->getContents(), true);
                
                if (isset($data['products']) && is_array($data['products'])) {
                    // Process each product
                    foreach ($data['products'] as $productData) {
                        $this->processProductSync($productData);
                    }
                    
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            // Log the error
            error_log('Guzzle sync error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sync products using cURL
     * 
     * @return bool
     */
    private function syncWithCurl()
    {
        // Check if cURL is available
        if (!function_exists('curl_init')) {
            return false;
        }
        
        try {
            // Initialize cURL
            $ch = curl_init('https://api.nutrinexus.com/v1/products/sync');
            
            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Authorization: Bearer ' . $this->getApiKey(),
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for development
            
            // Execute the request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Close cURL
            curl_close($ch);
            
            // Parse the response
            if ($httpCode >= 200 && $httpCode < 300) {
                $data = json_decode($response, true);
                
                if (isset($data['products']) && is_array($data['products'])) {
                    // Process each product
                    foreach ($data['products'] as $productData) {
                        $this->processProductSync($productData);
                    }
                    
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            // Log the error
            error_log('cURL sync error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process product data during sync
     * 
     * @param array $productData
     * @return void
     */
    private function processProductSync($productData)
    {
        // Check if product exists
        $existingProduct = $this->productModel->findBySlug($productData['slug'] ?? '');
        
        if ($existingProduct) {
            // Update existing product
            $this->productModel->update($existingProduct['id'], [
                'product_name' => $productData['name'] ?? $existingProduct['product_name'],
                'price' => $productData['price'] ?? $existingProduct['price'],
                'stock_quantity' => $productData['stock'] ?? $existingProduct['stock_quantity'],
                'description' => $productData['description'] ?? $existingProduct['description'],
                'category' => $productData['category'] ?? $existingProduct['category'],
                'weight' => $productData['weight'] ?? $existingProduct['weight'],
                'serving' => $productData['serving'] ?? $existingProduct['serving'],
                'flavor' => $productData['flavor'] ?? $existingProduct['flavor'],
                'capsule' => $productData['capsule'] ?? $existingProduct['capsule']
            ]);
            
            // Invalidate cache for this product
            $this->invalidateProductCache($existingProduct['id'], $existingProduct['slug']);
        } else {
            // Create new product
            $result = $this->productModel->create([
                'product_name' => $productData['name'] ?? '',
                'slug' => $productData['slug'] ?? '',
                'price' => $productData['price'] ?? 0,
                'stock_quantity' => $productData['stock'] ?? 0,
                'description' => $productData['description'] ?? '',
                'category' => $productData['category'] ?? '',
                'weight' => $productData['weight'] ?? null,
                'serving' => $productData['serving'] ?? null,
                'flavor' => $productData['flavor'] ?? null,
                'capsule' => $productData['capsule'] ?? 0
            ]);
            
            // Clear listing caches
            if ($result) {
                $this->clearListingCaches();
            }
        }
    }
}