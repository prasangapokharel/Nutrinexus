<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Product;
use App\Models\Review;
use App\Models\Wishlist;
use App\Helpers\CacheHelper;

/**
 * Product Controller
 * Handles product-related functionality
 */
class ProductController extends Controller
{
    /**
     * @var Product
     */
    private $productModel;
    
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
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->reviewModel = new Review();
        $this->wishlistModel = new Wishlist();
        $this->cache = CacheHelper::getInstance();
    }

    /**
     * Display all products with caching
     */
    public function index()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        // Generate cache key based on pagination parameters
        $cacheKey = $this->cache->generateKey('products_page', [
            'page' => $page,
            'limit' => $limit
        ]);
        
        // Try to get data from cache
        $viewData = $this->cache->get($cacheKey);
        
        if ($viewData === null) {
            // Cache miss - fetch from database
            $products = $this->productModel->getProducts($limit, $offset);
            $totalProducts = $this->productModel->getProductCount();
            $totalPages = ceil($totalProducts / $limit);
            
            // Check if products are in user's wishlist
            if (Session::has('user_id')) {
                foreach ($products as &$product) {
                    $product['in_wishlist'] = $this->wishlistModel->isInWishlist(Session::get('user_id'), $product['id']);
                }
            }
            
            $viewData = [
                'products' => $products,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'title' => 'All Products',
                'cached_at' => date('Y-m-d H:i:s')
            ];
            
            // Store in cache for 30 minutes
            $this->cache->set($cacheKey, $viewData, 1800);
        }
        
        $this->view('products/index', $viewData);
    }

    /**
     * Display single product with caching
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
        $cacheKey = $this->cache->generateKey('product_view', ['slug' => $slug]);
        
        // Try to get data from cache
        $viewData = $this->cache->get($cacheKey);
        
        if ($viewData === null) {
            // Cache miss - fetch from database
            
            // Try to find product by slug first
            $product = $this->productModel->findBySlug($slug);
            
            // If not found by slug, try by ID (for backward compatibility)
            if (!$product && is_numeric($slug)) {
                $product = $this->productModel->find($slug);
            }
            
            if (!$product) {
                $this->setFlash('error', 'Product not found');
                $this->redirect('products');
                return;
            }
            
            // Get reviews
            $reviews = $this->reviewModel->getByProductId($product['id']);
            $averageRating = $this->reviewModel->getAverageRating($product['id']);
            $reviewCount = $this->reviewModel->getReviewCount($product['id']);
            
            // Get related products
            $relatedProducts = $this->getLocalRecommendations($product['id']);
            
            $viewData = [
                'product' => $product,
                'reviews' => $reviews,
                'averageRating' => $averageRating,
                'reviewCount' => $reviewCount,
                'relatedProducts' => $relatedProducts,
                'title' => $product['product_name'],
                'cached_at' => date('Y-m-d H:i:s')
            ];
            
            // Store in cache for 1 hour
            $this->cache->set($cacheKey, $viewData, 3600);
        }
        
        // Add dynamic data that shouldn't be cached
        // Check if product is in user's wishlist
        $inWishlist = false;
        if (Session::has('user_id')) {
            $inWishlist = $this->wishlistModel->isInWishlist(Session::get('user_id'), $viewData['product']['id']);
        }
        
        // Check if user has reviewed this product
        $hasReviewed = false;
        if (Session::has('user_id')) {
            $hasReviewed = $this->reviewModel->hasUserReviewed(Session::get('user_id'), $viewData['product']['id']);
        }
        
        $viewData['inWishlist'] = $inWishlist;
        $viewData['hasReviewed'] = $hasReviewed;
        
        $this->view('products/view', $viewData);
    }

    /**
     * Search products with caching
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
        $cacheKey = $this->cache->generateKey('product_search', ['keyword' => $keyword]);
        
        // Try to get data from cache
        $viewData = $this->cache->get($cacheKey);
        
        if ($viewData === null) {
            // Cache miss - fetch from database
            $products = $this->productModel->searchProducts($keyword);
            
            $viewData = [
                'products' => $products,
                'keyword' => $keyword,
                'title' => 'Search Results: ' . $keyword,
                'cached_at' => date('Y-m-d H:i:s')
            ];
            
            // Store in cache for 15 minutes (shorter time for search results)
            $this->cache->set($cacheKey, $viewData, 900);
        }
        
        // Add dynamic data that shouldn't be cached
        // Check if products are in user's wishlist
        if (Session::has('user_id')) {
            foreach ($viewData['products'] as &$product) {
                $product['in_wishlist'] = $this->wishlistModel->isInWishlist(Session::get('user_id'), $product['id']);
            }
        }
        
        $this->view('products/search', $viewData);
    }

    /**
     * Submit a product review and invalidate cache
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
        $review = isset($_POST['review']) ? trim($_POST['review']) : '';
        
        // Validate input
        $errors = [];
        
        if (!$productId) {
            $errors['product_id'] = 'Invalid product';
        } else {
            $product = $this->productModel->find($productId);
            if (!$product) {
                $errors['product_id'] = 'Product not found';
            }
        }
        
        if ($rating < 1 || $rating > 5) {
            $errors['rating'] = 'Rating must be between 1 and 5';
        }
        
        if (empty($review)) {
            $errors['review'] = 'Review is required';
        }
        
        // Check if user has already reviewed this product
        if ($this->reviewModel->hasUserReviewed(Session::get('user_id'), $productId)) {
            $errors['general'] = 'You have already reviewed this product';
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
                // Invalidate cache for this product
                $this->invalidateProductCache($productId, $product['slug'] ?? '');
                
                $this->setFlash('success', 'Your review has been submitted successfully');
            } else {
                $this->setFlash('error', 'Failed to submit your review');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        
        $this->redirect('products/view/' . ($product['slug'] ?? $productId));
    }
    
    /**
     * Display products by category with caching
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
        $cacheKey = $this->cache->generateKey('product_category', [
            'category' => $category,
            'page' => $page,
            'limit' => $limit
        ]);
        
        // Try to get data from cache
        $viewData = $this->cache->get($cacheKey);
        
        if ($viewData === null) {
            // Cache miss - fetch from database
            
            // Get products by category
            $sql = "SELECT * FROM products WHERE category = ? ORDER BY id DESC LIMIT ? OFFSET ?";
            $products = $this->productModel->query($sql, [$category, $limit, $offset]);
            
            // Get total count for pagination
            $sql = "SELECT COUNT(*) as count FROM products WHERE category = ?";
            $result = $this->productModel->querySingle($sql, [$category]);
            $totalProducts = $result ? (int)$result['count'] : 0;
            $totalPages = ceil($totalProducts / $limit);
            
            $viewData = [
                'products' => $products,
                'category' => $category,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'title' => $category . ' Products',
                'cached_at' => date('Y-m-d H:i:s')
            ];
            
            // Store in cache for 1 hour
            $this->cache->set($cacheKey, $viewData, 3600);
        }
        
        // Add dynamic data that shouldn't be cached
        // Check if products are in user's wishlist
        if (Session::has('user_id')) {
            foreach ($viewData['products'] as &$product) {
                $product['in_wishlist'] = $this->wishlistModel->isInWishlist(Session::get('user_id'), $product['id']);
            }
        }
        
        $this->view('products/category', $viewData);
    }

    /**
     * Fetch product recommendations from external API with caching
     * 
     * @param int $productId
     * @return array
     */
    public function getRecommendations($productId)
    {
        // Generate cache key
        $cacheKey = $this->cache->generateKey('product_recommendations', ['product_id' => $productId]);
        
        // Try to get from cache
        $recommendations = $this->cache->get($cacheKey);
        
        if ($recommendations === null) {
            // Cache miss - fetch from API or database
            
            // Try to get recommendations using Guzzle first, then fall back to cURL
            $recommendations = $this->fetchWithGuzzle('recommendations', ['product_id' => $productId]);
            
            if ($recommendations === null) {
                // Fallback to cURL if Guzzle fails
                $recommendations = $this->fetchWithCurl('recommendations', ['product_id' => $productId]);
            }
            
            if (empty($recommendations)) {
                // If both methods fail or return empty, use local fallback
                $recommendations = $this->getLocalRecommendations($productId);
            }
            
            // Store in cache for 6 hours
            $this->cache->set($cacheKey, $recommendations, 21600);
        }
        
        return $recommendations;
    }
    
    /**
     * Get local product recommendations based on category with caching
     * 
     * @param int $productId
     * @param int $limit
     * @return array
     */
    private function getLocalRecommendations($productId, $limit = 4)
    {
        // Generate cache key
        $cacheKey = $this->cache->generateKey('local_recommendations', [
            'product_id' => $productId,
            'limit' => $limit
        ]);
        
        // Try to get from cache
        $recommendations = $this->cache->get($cacheKey);
        
        if ($recommendations === null) {
            // Cache miss - fetch from database
            $product = $this->productModel->find($productId);
            
            if (!$product) {
                return [];
            }
            
            $category = $product['category'] ?? '';
            
            if (empty($category)) {
                return [];
            }
            
            $sql = "SELECT * FROM products WHERE category = ? AND id != ? ORDER BY RAND() LIMIT ?";
            $results = $this->productModel->query($sql, [$category, $productId, $limit]);
            
            // Ensure we return an array
            $recommendations = is_array($results) ? $results : [];
            
            // Store in cache for 3 hours
            $this->cache->set($cacheKey, $recommendations, 10800);
        }
        
        return $recommendations;
    }
    
    /**
     * Fetch trending products from external API with caching
     * 
     * @param int $limit
     * @return array
     */
    public function getTrendingProducts($limit = 8)
    {
        // Generate cache key
        $cacheKey = $this->cache->generateKey('trending_products', ['limit' => $limit]);
        
        // Try to get from cache
        $trending = $this->cache->get($cacheKey);
        
        if ($trending === null) {
            // Cache miss - fetch from API or database
            
            // Try to get trending products using Guzzle first, then fall back to cURL
            $trending = $this->fetchWithGuzzle('trending', ['limit' => $limit]);
            
            if ($trending === null) {
                // Fallback to cURL if Guzzle fails
                $trending = $this->fetchWithCurl('trending', ['limit' => $limit]);
            }
            
            if (empty($trending)) {
                // If both methods fail or return empty, use local fallback
                $trending = $this->productModel->getFeaturedProducts($limit);
            }
            
            // Store in cache for 3 hours
            $this->cache->set($cacheKey, $trending, 10800);
        }
        
        return $trending;
    }
    
    /**
     * API endpoint to get product information with caching
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
        $cacheKey = $this->cache->generateKey('api_product', ['id' => $id]);
        
        // Try to get from cache
        $response = $this->cache->get($cacheKey);
        
        if ($response === null) {
            // Cache miss - fetch from database
            $product = $this->productModel->find($id);
            
            if (!$product) {
                echo json_encode(['error' => 'Product not found']);
                return;
            }
            
            $response = [
                'success' => true,
                'product' => $product,
                'cached_at' => date('Y-m-d H:i:s')
            ];
            
            // Store in cache for 1 hour
            $this->cache->set($cacheKey, $response, 3600);
        }
        
        // Return product data as JSON
        header('Content-Type: application/json');
        echo json_encode($response);
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
        
        // Try to sync products using Guzzle first, then fall back to cURL
        $synced = $this->syncWithGuzzle();
        
        if (!$synced) {
            // Fallback to cURL if Guzzle fails
            $synced = $this->syncWithCurl();
        }
        
        if ($synced) {
            // Clear all product-related caches
            $this->clearAllProductCache();
            
            $this->setFlash('success', 'Products synchronized successfully');
        } else {
            $this->setFlash('error', 'Failed to synchronize products');
        }
        
        $this->redirect('admin/products');
        return $synced;
    }
    
    /**
     * Invalidate cache for a specific product
     * 
     * @param int $productId
     * @param string $slug
     */
    private function invalidateProductCache($productId, $slug = '')
    {
        // Clear product view cache
        $this->cache->delete($this->cache->generateKey('product_view', ['slug' => $slug]));
        $this->cache->delete($this->cache->generateKey('product_view', ['slug' => $productId]));
        
        // Clear API product cache
        $this->cache->delete($this->cache->generateKey('api_product', ['id' => $productId]));
        
        // Clear recommendations cache
        $this->cache->delete($this->cache->generateKey('product_recommendations', ['product_id' => $productId]));
        $this->cache->delete($this->cache->generateKey('local_recommendations', ['product_id' => $productId]));
        
        // Clear category and product listing caches
        // Note: This is a bit aggressive but ensures data consistency
        $this->clearListingCaches();
    }
    
    /**
     * Clear all product listing related caches
     */
    private function clearListingCaches()
    {
        // Clear home page cache
        $this->cache->delete('home_page_data');
        
        // Clear featured products cache
        $this->cache->delete($this->cache->generateKey('featured_products', []));
        
        // Clear trending products cache
        $this->cache->delete($this->cache->generateKey('trending_products', []));
        
        // Note: For a more sophisticated approach, we would need to
        // track all cache keys or use a cache tag system
    }
    
    /**
     * Clear all product-related caches
     */
    private function clearAllProductCache()
    {
        // This is a simple but effective approach to clear all caches
        // In a production environment, you might want to use a more targeted approach
        $this->cache->clear();
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
            ]);
            
            // Clear listing caches
            if ($result) {
                $this->clearListingCaches();
            }
        }
    }
}
