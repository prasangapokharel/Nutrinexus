<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Cart;
use App\Models\ProductImage;
use App\Helpers\CacheHelper;

// Import phpFastCache
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Drivers\Files\Config as FilesConfig;

// Import Spatie Async
use Spatie\Async\Pool;

/**
 * Cart Controller with Enhanced Caching and Async Processing
 * Handles shopping cart functionality with phpFastCache and Spatie Async
 */
class CartController extends Controller
{
    private $productModel;
    private $cartModel;
    private $productImageModel;
    private $cache;
    
    /**
     * @var Pool|null
     */
    private $asyncPool;
    private $hasAsync = false;
    
    // phpFastCache instance
    private $fastCache = null;
    private $hasFastCache = false;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->cartModel = new Cart();
        $this->productImageModel = new ProductImage();
        $this->cache = CacheHelper::getInstance();
        
        // Initialize phpFastCache
        $this->initializeFastCache();
        
        // Initialize Spatie Async
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
                    'defaultTtl' => 1800, // 30 minutes for cart data
                    'securityKey' => 'nutrinexus_cart_cache',
                    'htaccess' => true,
                    'defaultKeyHashFunction' => 'md5',
                    'defaultFileNameHashFunction' => 'md5',
                ]));
                
                // Get Files driver instance
                $this->fastCache = CacheManager::getInstance('files');
                $this->hasFastCache = true;
                
                error_log('phpFastCache initialized successfully for CartController');
            } else {
                error_log('phpFastCache not available in CartController');
                $this->hasFastCache = false;
            }
        } catch (\Exception $e) {
            error_log('Failed to initialize phpFastCache in CartController: ' . $e->getMessage());
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
                error_log('Spatie Async initialized successfully for CartController');
            } else {
                error_log('Spatie\\Async\\Pool class not found in CartController. Async processing disabled.');
                $this->hasAsync = false;
            }
        } catch (\Exception $e) {
            error_log('Failed to create async pool in CartController: ' . $e->getMessage());
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
    private function getFromEnhancedCache($key, callable $callback, int $lifetime = 1800)
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
                error_log('phpFastCache error in CartController: ' . $e->getMessage());
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
     * Display cart with enhanced caching and async processing
     */
    public function index()
    {
        // Generate cache key based on session ID and cart contents
        $cartItems = $this->cartModel->getItems();
        $cacheKey = 'cart_display_' . md5(session_id() . serialize($cartItems));
        
        // Try to get data from enhanced cache
        $viewData = $this->getFromEnhancedCache($cacheKey, function() {
            if ($this->hasAsync) {
                return $this->getCartDisplayDataAsync();
            } else {
                return $this->getCartDisplayDataSync();
            }
        }, 300); // 5 minutes cache for cart display
        
        $this->view('cart/index', $viewData);
    }

    /**
     * Get cart display data asynchronously
     */
    private function getCartDisplayDataAsync()
    {
        try {
            $pool = $this->asyncPool;
            
            // Start async task for cart data
            $cartDataTask = $pool->add(function() {
                return $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
            });
            
            // Wait for task to complete
            $pool->wait();
            
            $cartData = $cartDataTask->getOutput();
            
            return [
                'cartItems' => $cartData['items'],
                'total' => $cartData['total'],
                'tax' => $cartData['tax'],
                'finalTotal' => $cartData['final_total'],
                'title' => 'Shopping Cart',
                'cached_at' => date('Y-m-d H:i:s'),
                'load_method' => 'async'
            ];
            
        } catch (\Exception $e) {
            error_log('Async cart display data failed: ' . $e->getMessage());
            return $this->getCartDisplayDataSync();
        }
    }

    /**
     * Get cart display data synchronously (fallback)
     */
    private function getCartDisplayDataSync()
    {
        $cartData = $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
        
        return [
            'cartItems' => $cartData['items'],
            'total' => $cartData['total'],
            'tax' => $cartData['tax'],
            'finalTotal' => $cartData['final_total'],
            'title' => 'Shopping Cart',
            'cached_at' => date('Y-m-d H:i:s'),
            'load_method' => 'sync'
        ];
    }

    /**
     * Add item to cart with enhanced validation and async processing
     */
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            
            if (!$productId || $quantity < 1) {
                if ($this->isAjaxRequest()) {
                    $this->jsonResponse(['success' => false, 'message' => 'Invalid product information']);
                    return;
                }
                $this->setFlash('error', 'Invalid product information');
                $this->redirect('products');
                return;
            }
            
            // Process add to cart with async validation
            if ($this->hasAsync) {
                $this->addToCartAsync($productId, $quantity);
            } else {
                $this->addToCartSync($productId, $quantity);
            }
        } else {
            $this->redirect('products');
        }
    }

    /**
     * Add to cart asynchronously
     */
    private function addToCartAsync($productId, $quantity)
    {
        try {
            $pool = $this->asyncPool;
            
            // Start async tasks for product validation
            $productTask = $pool->add(function() use ($productId) {
                return $this->productModel->find($productId);
            });
            
            $cartItemsTask = $pool->add(function() {
                return $this->cartModel->getItems();
            });
            
            // Wait for tasks to complete
            $pool->wait();
            
            $product = $productTask->getOutput();
            $currentCart = $cartItemsTask->getOutput();
            
            $this->processAddToCart($productId, $quantity, $product, $currentCart);
            
        } catch (\Exception $e) {
            error_log('Async add to cart failed: ' . $e->getMessage());
            $this->addToCartSync($productId, $quantity);
        }
    }

    /**
     * Add to cart synchronously (fallback)
     */
    private function addToCartSync($productId, $quantity)
    {
        $product = $this->productModel->find($productId);
        $currentCart = $this->cartModel->getItems();
        
        $this->processAddToCart($productId, $quantity, $product, $currentCart);
    }

    /**
     * Process add to cart operation
     */
    private function processAddToCart($productId, $quantity, $product, $currentCart)
    {
        if (!$product) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Product not found']);
                return;
            }
            $this->setFlash('error', 'Product not found');
            $this->redirect('products');
            return;
        }
        
        // Check stock
        $currentQuantity = isset($currentCart[$productId]) ? $currentCart[$productId]['quantity'] : 0;
        $totalQuantity = $currentQuantity + $quantity;
        
        if ($product['stock_quantity'] < $totalQuantity) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => false, 
                    'message' => 'Not enough stock available. Available: ' . $product['stock_quantity'] . ', In cart: ' . $currentQuantity
                ]);
                return;
            }
            $this->setFlash('error', 'Not enough stock available');
            $this->redirect('products/view/' . $productId);
            return;
        }
        
        // Add to cart
        $this->cartModel->addItem($productId, $quantity, $product['price']);
        
        // Invalidate cart cache
        $this->invalidateCartCache();
        
        // Update cart count in session
        $_SESSION['cart_count'] = $this->cartModel->getItemCount();
        
        if ($this->isAjaxRequest()) {
            // Get updated cart data asynchronously if possible
            if ($this->hasAsync) {
                $this->getUpdatedCartDataAsync($product);
            } else {
                $this->getUpdatedCartDataSync($product);
            }
        } else {
            $this->setFlash('success', 'Product added to cart');
            $this->redirect('cart');
        }
    }

    /**
     * Get updated cart data asynchronously
     */
    private function getUpdatedCartDataAsync($product)
    {
        try {
            $pool = $this->asyncPool;
            
            $cartDataTask = $pool->add(function() {
                return $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
            });
            
            // Wait for task to complete
            $pool->wait();
            
            $cartData = $cartDataTask->getOutput();
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Product added to cart successfully',
                'cart_count' => $_SESSION['cart_count'],
                'cart_total' => $cartData['total'],
                'tax' => $cartData['tax'],
                'final_total' => $cartData['final_total'],
                'product_name' => $product['product_name'],
                'load_method' => 'async'
            ]);
            
        } catch (\Exception $e) {
            error_log('Async updated cart data failed: ' . $e->getMessage());
            $this->getUpdatedCartDataSync($product);
        }
    }

    /**
     * Get updated cart data synchronously (fallback)
     */
    private function getUpdatedCartDataSync($product)
    {
        $cartData = $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
        
        $this->jsonResponse([
            'success' => true,
            'message' => 'Product added to cart successfully',
            'cart_count' => $_SESSION['cart_count'],
            'cart_total' => $cartData['total'],
            'tax' => $cartData['tax'],
            'final_total' => $cartData['final_total'],
            'product_name' => $product['product_name'],
            'load_method' => 'sync'
        ]);
    }

    /**
     * Update cart item quantity with enhanced processing
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $action = isset($_POST['action']) ? $_POST['action'] : '';
            
            if (!$productId || !in_array($action, ['increase', 'decrease'])) {
                if ($this->isAjaxRequest()) {
                    $this->jsonResponse(['success' => false, 'message' => 'Invalid update parameters']);
                    return;
                }
                $this->redirect('cart');
                return;
            }
            
            // Process cart update with async validation
            if ($this->hasAsync) {
                $this->updateCartAsync($productId, $action);
            } else {
                $this->updateCartSync($productId, $action);
            }
        } else {
            $this->redirect('cart');
        }
    }

    /**
     * Update cart asynchronously
     */
    private function updateCartAsync($productId, $action)
    {
        try {
            $pool = $this->asyncPool;
            
            // Start async tasks
            $cartTask = $pool->add(function() {
                return $this->cartModel->getItems();
            });
            
            $productTask = $pool->add(function() use ($productId, $action) {
                // Only fetch product if we're increasing quantity
                if ($action === 'increase') {
                    return $this->productModel->find($productId);
                }
                return null;
            });
            
            // Wait for tasks to complete
            $pool->wait();
            
            $cart = $cartTask->getOutput();
            $product = $productTask->getOutput();
            
            $this->processCartUpdate($productId, $action, $cart, $product);
            
        } catch (\Exception $e) {
            error_log('Async cart update failed: ' . $e->getMessage());
            $this->updateCartSync($productId, $action);
        }
    }

    /**
     * Update cart synchronously (fallback)
     */
    private function updateCartSync($productId, $action)
    {
        $cart = $this->cartModel->getItems();
        $product = null;
        
        // Only fetch product if we're increasing quantity
        if ($action === 'increase') {
            $product = $this->productModel->find($productId);
        }
        
        $this->processCartUpdate($productId, $action, $cart, $product);
    }

    /**
     * Process cart update operation
     */
    private function processCartUpdate($productId, $action, $cart, $product)
    {
        if (!isset($cart[$productId])) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Item not found in cart']);
                return;
            }
            $this->redirect('cart');
            return;
        }
        
        // If increasing, check stock availability
        if ($action === 'increase' && $product) {
            $newQuantity = $cart[$productId]['quantity'] + 1;
            
            if ($product['stock_quantity'] < $newQuantity) {
                if ($this->isAjaxRequest()) {
                    $this->jsonResponse([
                        'success' => false, 
                        'message' => 'Not enough stock available. Maximum: ' . $product['stock_quantity']
                    ]);
                    return;
                }
                $this->setFlash('error', 'Not enough stock available');
                $this->redirect('cart');
                return;
            }
        }
        
        // Update cart
        $this->cartModel->updateItem($productId, $action);
        
        // Invalidate cart cache
        $this->invalidateCartCache();
        
        // Update cart count in session
        $_SESSION['cart_count'] = $this->cartModel->getItemCount();
        
        if ($this->isAjaxRequest()) {
            // Get updated cart data asynchronously if possible
            if ($this->hasAsync) {
                $this->getCartUpdateResponseAsync($productId);
            } else {
                $this->getCartUpdateResponseSync($productId);
            }
        } else {
            $this->redirect('cart');
        }
    }

    /**
     * Get cart update response asynchronously
     */
    private function getCartUpdateResponseAsync($productId)
    {
        try {
            $pool = $this->asyncPool;
            
            $cartDataTask = $pool->add(function() {
                return $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
            });
            
            $updatedCartTask = $pool->add(function() {
                return $this->cartModel->getItems();
            });
            
            // Wait for tasks to complete
            $pool->wait();
            
            $cartData = $cartDataTask->getOutput();
            $updatedCart = $updatedCartTask->getOutput();
            
            $this->sendCartUpdateResponse($productId, $cartData, $updatedCart, 'async');
            
        } catch (\Exception $e) {
            error_log('Async cart update response failed: ' . $e->getMessage());
            $this->getCartUpdateResponseSync($productId);
        }
    }

    /**
     * Get cart update response synchronously (fallback)
     */
    private function getCartUpdateResponseSync($productId)
    {
        $cartData = $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
        $updatedCart = $this->cartModel->getItems();
        
        $this->sendCartUpdateResponse($productId, $cartData, $updatedCart, 'sync');
    }

    /**
     * Send cart update response
     */
    private function sendCartUpdateResponse($productId, $cartData, $updatedCart, $loadMethod)
    {
        // Find the updated item
        $itemQuantity = isset($updatedCart[$productId]) ? $updatedCart[$productId]['quantity'] : 0;
        $itemSubtotal = 0;
        
        foreach ($cartData['items'] as $item) {
            if ($item['product']['id'] == $productId) {
                $itemSubtotal = $item['subtotal'];
                break;
            }
        }
        
        $this->jsonResponse([
            'success' => true,
            'message' => 'Cart updated successfully',
            'cart_count' => $_SESSION['cart_count'],
            'cart_total' => $cartData['total'],
            'tax' => $cartData['tax'],
            'final_total' => $cartData['final_total'],
            'item_quantity' => $itemQuantity,
            'item_subtotal' => $itemSubtotal,
            'empty_cart' => empty($cartData['items']),
            'load_method' => $loadMethod
        ]);
    }

    /**
     * Remove item from cart with enhanced processing
     */
    public function remove($productId = null)
    {
        // Handle URL parameter (GET request)
        if ($productId) {
            $productId = (int)$productId;
        }
        // Handle POST data (AJAX request)
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
            $productId = (int)$_POST['product_id'];
        }
        
        if (!$productId) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid product ID']);
                return;
            }
            $this->setFlash('error', 'Invalid product ID');
            $this->redirect('cart');
            return;
        }
        
        // Remove from cart
        $removed = $this->cartModel->removeItem($productId);
        
        if (!$removed) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Item not found in cart']);
                return;
            }
            $this->setFlash('error', 'Item not found in cart');
            $this->redirect('cart');
            return;
        }
        
        // Invalidate cart cache
        $this->invalidateCartCache();
        
        // Update cart count in session
        $_SESSION['cart_count'] = $this->cartModel->getItemCount();
        
        if ($this->isAjaxRequest()) {
            // Get updated cart data asynchronously if possible
            if ($this->hasAsync) {
                $this->getRemoveResponseAsync();
            } else {
                $this->getRemoveResponseSync();
            }
        } else {
            $this->setFlash('success', 'Item removed from cart');
            $this->redirect('cart');
        }
    }

    /**
     * Get remove response asynchronously
     */
    private function getRemoveResponseAsync()
    {
        try {
            $pool = $this->asyncPool;
            
            $cartDataTask = $pool->add(function() {
                return $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
            });
            
            // Wait for task to complete
            $pool->wait();
            
            $cartData = $cartDataTask->getOutput();
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Item removed from cart successfully',
                'cart_count' => $_SESSION['cart_count'],
                'cart_total' => $cartData['total'],
                'tax' => $cartData['tax'],
                'final_total' => $cartData['final_total'],
                'empty_cart' => empty($cartData['items']),
                'load_method' => 'async'
            ]);
            
        } catch (\Exception $e) {
            error_log('Async remove response failed: ' . $e->getMessage());
            $this->getRemoveResponseSync();
        }
    }

    /**
     * Get remove response synchronously (fallback)
     */
    private function getRemoveResponseSync()
    {
        $cartData = $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
        
        $this->jsonResponse([
            'success' => true,
            'message' => 'Item removed from cart successfully',
            'cart_count' => $_SESSION['cart_count'],
            'cart_total' => $cartData['total'],
            'tax' => $cartData['tax'],
            'final_total' => $cartData['final_total'],
            'empty_cart' => empty($cartData['items']),
            'load_method' => 'sync'
        ]);
    }

    /**
     * Clear cart with enhanced processing
     */
    public function clear()
    {
        // Allow both GET and POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
            // Clear cart
            $this->cartModel->clear();
            
            // Invalidate cart cache
            $this->invalidateCartCache();
            
            // Update cart count in session
            $_SESSION['cart_count'] = 0;
            
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Cart cleared successfully',
                    'cart_count' => 0,
                    'cart_total' => 0,
                    'tax' => 0,
                    'final_total' => 0,
                    'empty_cart' => true
                ]);
                return;
            }
            
            $this->setFlash('success', 'Cart cleared successfully');
        }
        
        $this->redirect('cart');
    }

    /**
     * Get cart count with caching (AJAX endpoint)
     */
    public function getCount()
    {
        // Generate cache key for cart count
        $cacheKey = 'cart_count_' . md5(session_id());
        
        // Try to get from enhanced cache
        $count = $this->getFromEnhancedCache($cacheKey, function() {
            return $this->cartModel->getItemCount();
        }, 300); // 5 minutes cache
        
        $_SESSION['cart_count'] = $count;
        
        $this->jsonResponse([
            'success' => true,
            'cart_count' => $count
        ]);
    }

    /**
     * Get cart summary with enhanced caching (AJAX endpoint)
     */
    public function getSummary()
    {
        // Generate cache key for cart summary
        $cartItems = $this->cartModel->getItems();
        $cacheKey = 'cart_summary_' . md5(session_id() . serialize($cartItems));
        
        // Try to get from enhanced cache
        $summaryData = $this->getFromEnhancedCache($cacheKey, function() {
            if ($this->hasAsync) {
                return $this->getCartSummaryAsync();
            } else {
                return $this->getCartSummarySync();
            }
        }, 300); // 5 minutes cache
        
        $this->jsonResponse($summaryData);
    }

    /**
     * Get cart summary asynchronously
     */
    private function getCartSummaryAsync()
    {
        try {
            $pool = $this->asyncPool;
            
            $cartDataTask = $pool->add(function() {
                return $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
            });
            
            $countTask = $pool->add(function() {
                return $this->cartModel->getItemCount();
            });
            
            // Wait for tasks to complete
            $pool->wait();
            
            $cartData = $cartDataTask->getOutput();
            $count = $countTask->getOutput();
            
            return [
                'success' => true,
                'cart_count' => $count,
                'cart_total' => $cartData['total'],
                'tax' => $cartData['tax'],
                'final_total' => $cartData['final_total'],
                'items' => $cartData['items'],
                'load_method' => 'async'
            ];
            
        } catch (\Exception $e) {
            error_log('Async cart summary failed: ' . $e->getMessage());
            return $this->getCartSummarySync();
        }
    }

    /**
     * Get cart summary synchronously (fallback)
     */
    private function getCartSummarySync()
    {
        $cartData = $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
        
        return [
            'success' => true,
            'cart_count' => $this->cartModel->getItemCount(),
            'cart_total' => $cartData['total'],
            'tax' => $cartData['tax'],
            'final_total' => $cartData['final_total'],
            'items' => $cartData['items'],
            'load_method' => 'sync'
        ];
    }

    /**
     * Bulk add items to cart with async processing
     */
    public function bulkAdd()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $items = isset($_POST['items']) ? $_POST['items'] : [];
            
            if (empty($items) || !is_array($items)) {
                if ($this->isAjaxRequest()) {
                    $this->jsonResponse(['success' => false, 'message' => 'No items provided']);
                    return;
                }
                $this->setFlash('error', 'No items provided');
                $this->redirect('cart');
                return;
            }
            
            // Process bulk add with async validation
            if ($this->hasAsync) {
                $this->bulkAddAsync($items);
            } else {
                $this->bulkAddSync($items);
            }
        } else {
            $this->redirect('cart');
        }
    }

    /**
     * Bulk add items asynchronously
     */
    private function bulkAddAsync($items)
    {
        try {
            $pool = $this->asyncPool;
            $validationTasks = [];
            
            // Create validation tasks for each item
            foreach ($items as $index => $item) {
                $productId = isset($item['product_id']) ? (int)$item['product_id'] : 0;
                $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                
                if ($productId && $quantity > 0) {
                    $validationTasks[$index] = $pool->add(function() use ($productId, $quantity) {
                        $product = $this->productModel->find($productId);
                        return [
                            'product_id' => $productId,
                            'quantity' => $quantity,
                            'product' => $product,
                            'valid' => $product && $product['stock_quantity'] >= $quantity
                        ];
                    });
                }
            }
            
            // Wait for all validation tasks to complete
            $pool->wait();
            
            // Process results
            $validItems = [];
            $errors = [];
            
            foreach ($validationTasks as $index => $task) {
                $result = $task->getOutput();
                
                if ($result['valid']) {
                    $validItems[] = $result;
                } else {
                    $errors[] = "Product ID {$result['product_id']}: " . 
                               ($result['product'] ? 'Insufficient stock' : 'Product not found');
                }
            }
            
            $this->processBulkAdd($validItems, $errors);
            
        } catch (\Exception $e) {
            error_log('Async bulk add failed: ' . $e->getMessage());
            $this->bulkAddSync($items);
        }
    }

    /**
     * Bulk add items synchronously (fallback)
     */
    private function bulkAddSync($items)
    {
        $validItems = [];
        $errors = [];
        
        foreach ($items as $item) {
            $productId = isset($item['product_id']) ? (int)$item['product_id'] : 0;
            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
            
            if ($productId && $quantity > 0) {
                $product = $this->productModel->find($productId);
                
                if ($product && $product['stock_quantity'] >= $quantity) {
                    $validItems[] = [
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'product' => $product,
                        'valid' => true
                    ];
                } else {
                    $errors[] = "Product ID {$productId}: " . 
                               ($product ? 'Insufficient stock' : 'Product not found');
                }
            }
        }
        
        $this->processBulkAdd($validItems, $errors);
    }

    /**
     * Process bulk add operation
     */
    private function processBulkAdd($validItems, $errors)
    {
        $addedCount = 0;
        
        foreach ($validItems as $item) {
            $this->cartModel->addItem($item['product_id'], $item['quantity'], $item['product']['price']);
            $addedCount++;
        }
        
        if ($addedCount > 0) {
            // Invalidate cart cache
            $this->invalidateCartCache();
            
            // Update cart count in session
            $_SESSION['cart_count'] = $this->cartModel->getItemCount();
        }
        
        $message = "Added {$addedCount} items to cart";
        if (!empty($errors)) {
            $message .= ". Errors: " . implode(', ', $errors);
        }
        
        if ($this->isAjaxRequest()) {
            $cartData = $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
            
            $this->jsonResponse([
                'success' => $addedCount > 0,
                'message' => $message,
                'added_count' => $addedCount,
                'errors' => $errors,
                'cart_count' => $_SESSION['cart_count'],
                'cart_total' => $cartData['total'],
                'tax' => $cartData['tax'],
                'final_total' => $cartData['final_total']
            ]);
        } else {
            if ($addedCount > 0) {
                $this->setFlash('success', $message);
            } else {
                $this->setFlash('error', 'No items could be added to cart');
            }
            $this->redirect('cart');
        }
    }

    /**
     * Clear all cart-related caches
     */
    public function clearCartCache()
    {
        // Only allow admin users to clear cache
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->setFlash('error', 'You do not have permission to perform this action');
            $this->redirect('cart');
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
        $this->redirect('admin/dashboard');
    }

    /**
     * Get cache statistics for cart
     */
    public function getCacheStats()
    {
        // Only allow admin users to view cache stats
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->setFlash('error', 'You do not have permission to perform this action');
            $this->redirect('cart');
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
        
        $this->view('admin/cart-cache-stats', [
            'title' => 'Cart Cache Statistics',
            'stats' => $stats
        ]);
    }

    /**
     * Invalidate cart-related caches
     */
    private function invalidateCartCache()
    {
        $sessionId = session_id();
        
        $keysToDelete = [
            'cart_display_' . md5($sessionId),
            'cart_count_' . md5($sessionId),
            'cart_summary_' . md5($sessionId)
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
    }

    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Send JSON response
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        // Add timestamp for debugging
        $data['timestamp'] = time();
        
        echo json_encode($data);
        exit;
    }

    /**
     * Get main image URL function with enhanced caching
     */
    function getProductImageUrl($product) 
    {
        // Generate cache key for product image
        $cacheKey = 'product_image_url_' . md5($product['id'] ?? 0);
        
        // Try to get from enhanced cache
        $imageUrl = $this->getFromEnhancedCache($cacheKey, function() use ($product) {
            $mainImageUrl = '';
            
            if (!empty($product['images'])) {
                // Use primary image or first image
                $primaryImage = null;
                foreach ($product['images'] as $img) {
                    if ($img['is_primary']) {
                        $primaryImage = $img;
                        break;
                    }
                }
                $imageData = $primaryImage ?: $product['images'][0];
                $mainImageUrl = filter_var($imageData['image_url'], FILTER_VALIDATE_URL) 
                    ? $imageData['image_url'] 
                    : \App\Core\View::asset('uploads/images/' . $imageData['image_url']);
            } else {
                // Fallback to old image field
                $image = $product['image'] ?? '';
                $mainImageUrl = filter_var($image, FILTER_VALIDATE_URL) 
                    ? $image 
                    : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'));
            }
            
            return $mainImageUrl;
        }, 3600); // 1 hour cache for image URLs
        
        return $imageUrl;
    }

    /**
     * Validate cart items with async processing
     */
    public function validateCart()
    {
        if ($this->hasAsync) {
            $this->validateCartAsync();
        } else {
            $this->validateCartSync();
        }
    }

    /**
     * Validate cart asynchronously
     */
    private function validateCartAsync()
    {
        try {
            $pool = $this->asyncPool;
            $cartItems = $this->cartModel->getItems();
            $validationTasks = [];
            
            // Create validation tasks for each cart item
            foreach ($cartItems as $productId => $item) {
                $validationTasks[$productId] = $pool->add(function() use ($productId, $item) {
                    $product = $this->productModel->find($productId);
                    return [
                        'product_id' => $productId,
                        'cart_quantity' => $item['quantity'],
                        'available_stock' => $product ? $product['stock_quantity'] : 0,
                        'product_exists' => $product !== null,
                        'stock_sufficient' => $product && $product['stock_quantity'] >= $item['quantity']
                    ];
                });
            }
            
            // Wait for all validation tasks to complete
            $pool->wait();
            
            // Process results
            $validationResults = [];
            $hasIssues = false;
            
            foreach ($validationTasks as $productId => $task) {
                $result = $task->getOutput();
                $validationResults[$productId] = $result;
                
                if (!$result['product_exists'] || !$result['stock_sufficient']) {
                    $hasIssues = true;
                }
            }
            
            $this->sendValidationResponse($validationResults, $hasIssues, 'async');
            
        } catch (\Exception $e) {
            error_log('Async cart validation failed: ' . $e->getMessage());
            $this->validateCartSync();
        }
    }

    /**
     * Validate cart synchronously (fallback)
     */
    private function validateCartSync()
    {
        $cartItems = $this->cartModel->getItems();
        $validationResults = [];
        $hasIssues = false;
        
        foreach ($cartItems as $productId => $item) {
            $product = $this->productModel->find($productId);
            
            $result = [
                'product_id' => $productId,
                'cart_quantity' => $item['quantity'],
                'available_stock' => $product ? $product['stock_quantity'] : 0,
                'product_exists' => $product !== null,
                'stock_sufficient' => $product && $product['stock_quantity'] >= $item['quantity']
            ];
            
            $validationResults[$productId] = $result;
            
            if (!$result['product_exists'] || !$result['stock_sufficient']) {
                $hasIssues = true;
            }
        }
        
        $this->sendValidationResponse($validationResults, $hasIssues, 'sync');
    }

    /**
     * Send validation response
     */
    private function sendValidationResponse($validationResults, $hasIssues, $loadMethod)
    {
        if ($this->isAjaxRequest()) {
            $this->jsonResponse([
                'success' => true,
                'has_issues' => $hasIssues,
                'validation_results' => $validationResults,
                'load_method' => $loadMethod
            ]);
        } else {
            if ($hasIssues) {
                $this->setFlash('warning', 'Some items in your cart have stock issues. Please review your cart.');
            } else {
                $this->setFlash('success', 'All cart items are valid and in stock.');
            }
            $this->redirect('cart');
        }
    }
}
