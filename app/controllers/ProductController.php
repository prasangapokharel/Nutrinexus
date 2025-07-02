<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Wishlist;
use App\Helpers\CacheHelper;
// Add Spatie Async
use Spatie\Async\Pool;

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
        
        // Initialize Spatie Async Pool with fallback
        if (class_exists('\\Spatie\\Async\\Pool')) {
            try {
                $this->asyncPool = Pool::create();
            } catch (\Exception $e) {
                error_log('Failed to create async pool: ' . $e->getMessage());
                $this->asyncPool = null;
            }
        } else {
            error_log('Spatie\\Async\\Pool class not found. Async processing disabled.');
            $this->asyncPool = null;
        }
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
            // Use async to fetch products and count in parallel if available
            if ($this->asyncPool) {
                try {
                    $productsPromise = $this->asyncPool->add(function() use ($limit, $offset) {
                        return $this->productModel->getProductsWithImages($limit, $offset);
                    });
                    
                    $totalProductsPromise = $this->asyncPool->add(function() {
                        return $this->productModel->getProductCount();
                    });
                    
                    // Wait for async tasks to complete
                    $this->asyncPool->wait();
                    
                    // Get results
                    $products = $productsPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($limit, $offset) {
                        error_log('Error fetching products asynchronously: ' . $e->getMessage());
                        return $this->productModel->getProductsWithImages($limit, $offset);
                    });
                    
                    $totalProducts = $totalProductsPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) {
                        error_log('Error fetching product count asynchronously: ' . $e->getMessage());
                        return $this->productModel->getProductCount();
                    });
                } catch (\Exception $e) {
                    error_log('Async processing error: ' . $e->getMessage());
                    // Fall back to standard approach
                    $products = $this->productModel->getProductsWithImages($limit, $offset);
                    $totalProducts = $this->productModel->getProductCount();
                }
            } else {
                // Standard approach without async
                $products = $this->productModel->getProductsWithImages($limit, $offset);
                $totalProducts = $this->productModel->getProductCount();
            }
            
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
            $product = $this->productModel->findBySlugWithImages($slug);
            
            // If not found by slug, try by ID (for backward compatibility)
            if (!$product && is_numeric($slug)) {
                $product = $this->productModel->findWithImages($slug);
            }
            
            if (!$product) {
                $this->setFlash('error', 'Product not found');
                $this->redirect('products');
                return;
            }
            
            // Use async to fetch reviews, ratings, and related products in parallel if available
            if ($this->asyncPool) {
                try {
                    $reviewsPromise = $this->asyncPool->add(function() use ($product) {
                        return $this->reviewModel->getByProductId($product['id']);
                    });
                    
                    $ratingPromise = $this->asyncPool->add(function() use ($product) {
                        return $this->reviewModel->getAverageRating($product['id']);
                    });
                    
                    $reviewCountPromise = $this->asyncPool->add(function() use ($product) {
                        return $this->reviewModel->getReviewCount($product['id']);
                    });
                    
                    $relatedProductsPromise = $this->asyncPool->add(function() use ($product) {
                        return $this->getLocalRecommendations($product['id']);
                    });
                    
                    // Wait for async tasks to complete
                    $this->asyncPool->wait();
                    
                    // Get results
                    $reviews = $reviewsPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($product) {
                        error_log('Error fetching reviews asynchronously: ' . $e->getMessage());
                        return $this->reviewModel->getByProductId($product['id']);
                    });
                    
                    $averageRating = $ratingPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($product) {
                        error_log('Error fetching average rating asynchronously: ' . $e->getMessage());
                        return $this->reviewModel->getAverageRating($product['id']);
                    });
                    
                    $reviewCount = $reviewCountPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($product) {
                        error_log('Error fetching review count asynchronously: ' . $e->getMessage());
                        return $this->reviewModel->getReviewCount($product['id']);
                    });
                    
                    $relatedProducts = $relatedProductsPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($product) {
                        error_log('Error fetching related products asynchronously: ' . $e->getMessage());
                        return $this->getLocalRecommendations($product['id']);
                    });
                } catch (\Exception $e) {
                    error_log('Async processing error: ' . $e->getMessage());
                    // Fall back to standard approach
                    $reviews = $this->reviewModel->getByProductId($product['id']);
                    $averageRating = $this->reviewModel->getAverageRating($product['id']);
                    $reviewCount = $this->reviewModel->getReviewCount($product['id']);
                    $relatedProducts = $this->getLocalRecommendations($product['id']);
                }
            } else {
                // Standard approach without async
                $reviews = $this->reviewModel->getByProductId($product['id']);
                $averageRating = $this->reviewModel->getAverageRating($product['id']);
                $reviewCount = $this->reviewModel->getReviewCount($product['id']);
                $relatedProducts = $this->getLocalRecommendations($product['id']);
            }
            
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
        // Use async to fetch user-specific data in parallel if available
        if (Session::has('user_id') && $this->asyncPool) {
            try {
                $inWishlistPromise = $this->asyncPool->add(function() use ($viewData) {
                    return $this->wishlistModel->isInWishlist(Session::get('user_id'), $viewData['product']['id']);
                });
                
                $hasReviewedPromise = $this->asyncPool->add(function() use ($viewData) {
                    return $this->reviewModel->hasUserReviewed(Session::get('user_id'), $viewData['product']['id']);
                });
                
                // Wait for async tasks to complete
                $this->asyncPool->wait();
                
                // Get results
                $inWishlist = $inWishlistPromise->then(function($result) {
                    return $result;
                })->catch(function(\Exception $e) use ($viewData) {
                    error_log('Error checking wishlist asynchronously: ' . $e->getMessage());
                    return $this->wishlistModel->isInWishlist(Session::get('user_id'), $viewData['product']['id']);
                });
                
                $hasReviewed = $hasReviewedPromise->then(function($result) {
                    return $result;
                })->catch(function(\Exception $e) use ($viewData) {
                    error_log('Error checking reviews asynchronously: ' . $e->getMessage());
                    return $this->reviewModel->hasUserReviewed(Session::get('user_id'), $viewData['product']['id']);
                });
            } catch (\Exception $e) {
                error_log('Async processing error: ' . $e->getMessage());
                // Fall back to standard approach
                $inWishlist = $this->wishlistModel->isInWishlist(Session::get('user_id'), $viewData['product']['id']);
                $hasReviewed = $this->reviewModel->hasUserReviewed(Session::get('user_id'), $viewData['product']['id']);
            }
        } else {
            // Standard approach without async
            $inWishlist = Session::has('user_id') ? $this->wishlistModel->isInWishlist(Session::get('user_id'), $viewData['product']['id']) : false;
            $hasReviewed = Session::has('user_id') ? $this->reviewModel->hasUserReviewed(Session::get('user_id'), $viewData['product']['id']) : false;
        }
        
        // Generate QR code for product sharing
        $qrCode = $this->generateProductQRCode($viewData['product']['id'], $viewData['product']['slug'] ?? '');
        
        $viewData['inWishlist'] = $inWishlist;
        $viewData['hasReviewed'] = $hasReviewed;
        $viewData['qrCode'] = $qrCode;
        
        $this->view('products/view', $viewData);
    }

    /**
     * Generate QR code for product sharing
     * 
     * @param int $productId
     * @param string $slug
     * @return string Base64 encoded QR code image
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
            // Use async if available
            if ($this->asyncPool) {
                try {
                    $productsPromise = $this->asyncPool->add(function() use ($keyword) {
                        return $this->productModel->searchProducts($keyword);
                    });
                    
                    // Wait for async task to complete
                    $this->asyncPool->wait();
                    
                    // Get results
                    $products = $productsPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($keyword) {
                        error_log('Error searching products asynchronously: ' . $e->getMessage());
                        return $this->productModel->searchProducts($keyword);
                    });
                } catch (\Exception $e) {
                    error_log('Async processing error: ' . $e->getMessage());
                    // Fall back to standard approach
                    $products = $this->productModel->searchProducts($keyword);
                }
            } else {
                // Standard approach without async
                $products = $this->productModel->searchProducts($keyword);
            }
            
            // Add images to each product
            foreach ($products as &$product) {
                $product['images'] = $this->productImageModel->getByProductId($product['id']);
                $product['primary_image'] = $this->productImageModel->getPrimaryImage($product['id']);
                
                if ($product['primary_image']) {
                    $product['image'] = $product['primary_image']['image_url'];
                }
            }
            
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
            // Use async to check wishlist status in parallel if available
            if ($this->asyncPool && !empty($viewData['products'])) {
                try {
                    $wishlistPromises = [];
                    $productIds = array_column($viewData['products'], 'id');
                    
                    foreach ($productIds as $index => $productId) {
                        $wishlistPromises[$productId] = $this->asyncPool->add(function() use ($productId) {
                            return [
                                'id' => $productId,
                                'in_wishlist' => $this->wishlistModel->isInWishlist(Session::get('user_id'), $productId)
                            ];
                        });
                    }
                    
                    // Wait for all async tasks to complete
                    $this->asyncPool->wait();
                    
                    // Process results
                    $wishlistResults = [];
                    foreach ($wishlistPromises as $productId => $promise) {
                        $promise->then(function($result) use (&$wishlistResults) {
                            $wishlistResults[$result['id']] = $result['in_wishlist'];
                        })->catch(function(\Exception $e) use ($productId, &$wishlistResults) {
                            error_log('Error checking wishlist asynchronously: ' . $e->getMessage());
                            $wishlistResults[$productId] = false;
                        });
                    }
                    
                    // Update products with wishlist status
                    foreach ($viewData['products'] as &$product) {
                        $product['in_wishlist'] = $wishlistResults[$product['id']] ?? false;
                    }
                } catch (\Exception $e) {
                    error_log('Async processing error: ' . $e->getMessage());
                    // Fall back to standard approach
                    foreach ($viewData['products'] as &$product) {
                        $product['in_wishlist'] = $this->wishlistModel->isInWishlist(Session::get('user_id'), $product['id']);
                    }
                }
            } else {
                // Standard approach without async
                foreach ($viewData['products'] as &$product) {
                    $product['in_wishlist'] = $this->wishlistModel->isInWishlist(Session::get('user_id'), $product['id']);
                }
            }
        }
        
        $this->view('products/search', $viewData);
    }

    /**
     * Search products by flavor
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
        $cacheKey = $this->cache->generateKey('product_flavor_search', ['flavor' => $flavor]);
        
        // Try to get data from cache
        $viewData = $this->cache->get($cacheKey);
        
        if ($viewData === null) {
            // Cache miss - fetch from database
            // Use async if available
            if ($this->asyncPool) {
                try {
                    $productsPromise = $this->asyncPool->add(function() use ($flavor) {
                        $sql = "SELECT * FROM products WHERE flavor LIKE ? ORDER BY id DESC";
                        return $this->productModel->query($sql, ['%' . $flavor . '%']);
                    });
                    
                    // Wait for async task to complete
                    $this->asyncPool->wait();
                    
                    // Get results
                    $products = $productsPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($flavor) {
                        error_log('Error searching products by flavor asynchronously: ' . $e->getMessage());
                        $sql = "SELECT * FROM products WHERE flavor LIKE ? ORDER BY id DESC";
                        return $this->productModel->query($sql, ['%' . $flavor . '%']);
                    });
                } catch (\Exception $e) {
                    error_log('Async processing error: ' . $e->getMessage());
                    // Fall back to standard approach
                    $sql = "SELECT * FROM products WHERE flavor LIKE ? ORDER BY id DESC";
                    $products = $this->productModel->query($sql, ['%' . $flavor . '%']);
                }
            } else {
                // Standard approach without async
                $sql = "SELECT * FROM products WHERE flavor LIKE ? ORDER BY id DESC";
                $products = $this->productModel->query($sql, ['%' . $flavor . '%']);
            }
            
            $viewData = [
                'products' => $products,
                'flavor' => $flavor,
                'title' => 'Products with ' . $flavor . ' Flavor',
                'cached_at' => date('Y-m-d H:i:s')
            ];
            
            // Store in cache for 15 minutes
            $this->cache->set($cacheKey, $viewData, 900);
        }
        
        // Add dynamic data that shouldn't be cached
        // Check if products are in user's wishlist
        if (Session::has('user_id')) {
            foreach ($viewData['products'] as &$product) {
                $product['in_wishlist'] = $this->wishlistModel->isInWishlist(Session::get('user_id'), $product['id']);
            }
        }
        
        $this->view('products/flavor', $viewData);
    }

    /**
     * Filter products by capsule type
     */
    public function filterByCapsule($isCapsule = 1)
    {
        // Generate cache key based on capsule filter
        $cacheKey = $this->cache->generateKey('product_capsule_filter', ['is_capsule' => $isCapsule]);
        
        // Try to get data from cache
        $viewData = $this->cache->get($cacheKey);
        
        if ($viewData === null) {
            // Cache miss - fetch from database
            // Use async if available
            if ($this->asyncPool) {
                try {
                    $productsPromise = $this->asyncPool->add(function() use ($isCapsule) {
                        $sql = "SELECT * FROM products WHERE capsule = ? ORDER BY id DESC";
                        return $this->productModel->query($sql, [$isCapsule]);
                    });
                    
                    // Wait for async task to complete
                    $this->asyncPool->wait();
                    
                    // Get results
                    $products = $productsPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($isCapsule) {
                        error_log('Error filtering products by capsule asynchronously: ' . $e->getMessage());
                        $sql = "SELECT * FROM products WHERE capsule = ? ORDER BY id DESC";
                        return $this->productModel->query($sql, [$isCapsule]);
                    });
                } catch (\Exception $e) {
                    error_log('Async processing error: ' . $e->getMessage());
                    // Fall back to standard approach
                    $sql = "SELECT * FROM products WHERE capsule = ? ORDER BY id DESC";
                    $products = $this->productModel->query($sql, [$isCapsule]);
                }
            } else {
                // Standard approach without async
                $sql = "SELECT * FROM products WHERE capsule = ? ORDER BY id DESC";
                $products = $this->productModel->query($sql, [$isCapsule]);
            }
            
            $viewData = [
                'products' => $products,
                'isCapsule' => $isCapsule,
                'title' => $isCapsule ? 'Capsule Products' : 'Non-Capsule Products',
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
        
        $this->view('products/capsule', $viewData);
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
        $review = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
        
        // Validate input
        $errors = [];
        
        if (!$productId) {
            $errors['product_id'] = 'Invalid product';
        } else {
            // Use async to fetch product if available
            if ($this->asyncPool) {
                try {
                    $productPromise = $this->asyncPool->add(function() use ($productId) {
                        return $this->productModel->find($productId);
                    });
                    
                    $hasReviewedPromise = $this->asyncPool->add(function() use ($productId) {
                        return $this->reviewModel->hasUserReviewed(Session::get('user_id'), $productId);
                    });
                    
                    // Wait for async tasks to complete
                    $this->asyncPool->wait();
                    
                    // Get results
                    $product = $productPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($productId) {
                        error_log('Error fetching product asynchronously: ' . $e->getMessage());
                        return $this->productModel->find($productId);
                    });
                    
                    $hasReviewed = $hasReviewedPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($productId) {
                        error_log('Error checking review status asynchronously: ' . $e->getMessage());
                        return $this->reviewModel->hasUserReviewed(Session::get('user_id'), $productId);
                    });
                } catch (\Exception $e) {
                    error_log('Async processing error: ' . $e->getMessage());
                    // Fall back to standard approach
                    $product = $this->productModel->find($productId);
                    $hasReviewed = $this->reviewModel->hasUserReviewed(Session::get('user_id'), $productId);
                }
            } else {
                // Standard approach without async
                $product = $this->productModel->find($productId);
                $hasReviewed = $this->reviewModel->hasUserReviewed(Session::get('user_id'), $productId);
            }
            
            if (!$product) {
                $errors['product_id'] = 'Product not found';
            }
            
            // Check if user has already reviewed this product
            if ($hasReviewed) {
                $errors['general'] = 'You have already reviewed this product';
            }
        }
        
        if ($rating < 1 || $rating > 5) {
            $errors['rating'] = 'Rating must be between 1 and 5';
        }
        
        if (empty($review)) {
            $errors['review'] = 'Review is required';
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
                if ($this->asyncPool) {
                    $this->asyncPool->add(function() use ($productId, $product) {
                        $this->invalidateProductCache($productId, $product['slug'] ?? '');
                        return true;
                    })->then(function($result) {
                        error_log('Product cache invalidated asynchronously');
                    })->catch(function(\Exception $e) {
                        error_log('Error invalidating product cache asynchronously: ' . $e->getMessage());
                    });
                } else {
                    // Synchronous cache invalidation
                    $this->invalidateProductCache($productId, $product['slug'] ?? '');
                }
                
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
            // Use async to fetch products and count in parallel if available
            if ($this->asyncPool) {
                try {
                    $productsPromise = $this->asyncPool->add(function() use ($category, $limit, $offset) {
                        $sql = "SELECT * FROM products WHERE category = ? ORDER BY id DESC LIMIT ? OFFSET ?";
                        return $this->productModel->getProductsByCategory($category, $limit, $offset);
                    });
                    
                    $totalProductsPromise = $this->asyncPool->add(function() use ($category) {
                        return $this->productModel->getProductCountByCategory($category);
                    });
                    
                    // Wait for async tasks to complete
                    $this->asyncPool->wait();
                    
                    // Get results
                    $products = $productsPromise->then(function($result) {
                        // Add images to each product
                        foreach ($result as &$product) {
                            $product['images'] = $this->productImageModel->getByProductId($product['id']);
                            $product['primary_image'] = $this->productImageModel->getPrimaryImage($product['id']);
                            
                            if ($product['primary_image']) {
                                $product['image'] = $product['primary_image']['image_url'];
                            }
                        }
                        return $result;
                    })->catch(function(\Exception $e) use ($category, $limit, $offset) {
                        error_log('Error fetching category products asynchronously: ' . $e->getMessage());
                        $sql = "SELECT * FROM products WHERE category = ? ORDER BY id DESC LIMIT ? OFFSET ?";
                        return $this->productModel->getProductsByCategory($category, $limit, $offset);
                    });
                    
                    $totalProducts = $totalProductsPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($category) {
                        error_log('Error fetching category product count asynchronously: ' . $e->getMessage());
                        return $this->productModel->getProductCountByCategory($category);
                    });
                } catch (\Exception $e) {
                    error_log('Async processing error: ' . $e->getMessage());
                    // Fall back to standard approach
                    $sql = "SELECT * FROM products WHERE category = ? ORDER BY id DESC LIMIT ? OFFSET ?";
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
                }
            } else {
                // Standard approach without async
                $sql = "SELECT * FROM products WHERE category = ? ORDER BY id DESC LIMIT ? OFFSET ?";
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
            }
            
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
            // Use async to try multiple fetching methods in parallel if available
            if ($this->asyncPool) {
                try {
                    $guzzlePromise = $this->asyncPool->add(function() use ($productId) {
                        return $this->fetchWithGuzzle('recommendations', ['product_id' => $productId]);
                    });
                    
                    $curlPromise = $this->asyncPool->add(function() use ($productId) {
                        return $this->fetchWithCurl('recommendations', ['product_id' => $productId]);
                    });
                    
                    $localPromise = $this->asyncPool->add(function() use ($productId) {
                        return $this->getLocalRecommendations($productId);
                    });
                    
                    // Wait for async tasks to complete
                    $this->asyncPool->wait();
                    
                    // Get results and use the first successful one
                    $guzzleResult = null;
                    $curlResult = null;
                    $localResult = null;
                    
                    $guzzlePromise->then(function($result) use (&$guzzleResult) {
                        $guzzleResult = $result;
                    })->catch(function(\Exception $e) {
                        error_log('Error fetching recommendations with Guzzle asynchronously: ' . $e->getMessage());
                    });
                    
                    $curlPromise->then(function($result) use (&$curlResult) {
                        $curlResult = $result;
                    })->catch(function(\Exception $e) {
                        error_log('Error fetching recommendations with cURL asynchronously: ' . $e->getMessage());
                    });
                    
                    $localPromise->then(function($result) use (&$localResult) {
                        $localResult = $result;
                    })->catch(function(\Exception $e) {
                        error_log('Error fetching local recommendations asynchronously: ' . $e->getMessage());
                    });
                    
                    // Use the first available result
                    if (!empty($guzzleResult)) {
                        $recommendations = $guzzleResult;
                    } elseif (!empty($curlResult)) {
                        $recommendations = $curlResult;
                    } elseif (!empty($localResult)) {
                        $recommendations = $localResult;
                    } else {
                        // Fallback to empty array if all methods fail
                        $recommendations = [];
                    }
                } catch (\Exception $e) {
                    error_log('Async processing error: ' . $e->getMessage());
                    // Fall back to standard approach
                    $recommendations = $this->fetchWithGuzzle('recommendations', ['product_id' => $productId]);
                    
                    if ($recommendations === null) {
                        // Fallback to cURL if Guzzle fails
                        $recommendations = $this->fetchWithCurl('recommendations', ['product_id' => $productId]);
                    }
                    
                    if (empty($recommendations)) {
                        // If both methods fail or return empty, use local fallback
                        $recommendations = $this->getLocalRecommendations($productId);
                    }
                }
            } else {
                // Standard approach without async
                $recommendations = $this->fetchWithGuzzle('recommendations', ['product_id' => $productId]);
                
                if ($recommendations === null) {
                    // Fallback to cURL if Guzzle fails
                    $recommendations = $this->fetchWithCurl('recommendations', ['product_id' => $productId]);
                }
                
                if (empty($recommendations)) {
                    // If both methods fail or return empty, use local fallback
                    $recommendations = $this->getLocalRecommendations($productId);
                }
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
            // Use async if available
            if ($this->asyncPool) {
                try {
                    $productPromise = $this->asyncPool->add(function() use ($productId) {
                        return $this->productModel->find($productId);
                    });
                    
                    // Wait for async task to complete
                    $this->asyncPool->wait();
                    
                    // Get product result
                    $product = $productPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($productId) {
                        error_log('Error fetching product asynchronously: ' . $e->getMessage());
                        return $this->productModel->find($productId);
                    });
                    
                    if (!$product) {
                        return [];
                    }
                    
                    $category = $product['category'] ?? '';
                    
                    if (empty($category)) {
                        return [];
                    }
                    
                    // Now fetch related products
                    $relatedPromise = $this->asyncPool->add(function() use ($category, $productId, $limit) {
                        return $this->productModel->getRelatedProducts($productId, $category, $limit);
                    });
                    
                    // Wait for async task to complete
                    $this->asyncPool->wait();
                    
                    // Get related products result
                    $results = $relatedPromise->then(function($result) {
                        // Add images to each product
                        foreach ($result as &$relatedProduct) {
                            $relatedProduct['images'] = $this->productImageModel->getByProductId($relatedProduct['id']);
                            $relatedProduct['primary_image'] = $this->productImageModel->getPrimaryImage($relatedProduct['id']);
                            
                            if ($relatedProduct['primary_image']) {
                                $relatedProduct['image'] = $relatedProduct['primary_image']['image_url'];
                            }
                        }
                        return $result;
                    })->catch(function(\Exception $e) use ($category, $productId, $limit) {
                        error_log('Error fetching related products asynchronously: ' . $e->getMessage());
                        return $this->productModel->getRelatedProducts($productId, $category, $limit);
                    });
                } catch (\Exception $e) {
                    error_log('Async processing error: ' . $e->getMessage());
                    // Fall back to standard approach
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
                }
            } else {
                // Standard approach without async
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
            }
            
            // Ensure we return an array
            $recommendations = is_array($results) ? $results : [];
            
            // Store in cache for 3 hours
            $this->cache->set($cacheKey, $recommendations, 10800);
        }
        
        return $recommendations;
    }
    
    /**
     * Get similar products based on flavor with caching
     * 
     * @param int $productId
     * @param int $limit
     * @return array
     */
    public function getSimilarFlavorProducts($productId, $limit = 4)
    {
        // Generate cache key
        $cacheKey = $this->cache->generateKey('similar_flavor_products', [
            'product_id' => $productId,
            'limit' => $limit
        ]);
        
        // Try to get from cache
        $similarProducts = $this->cache->get($cacheKey);
        
        if ($similarProducts === null) {
            // Cache miss - fetch from database
            // Use async if available
            if ($this->asyncPool) {
                try {
                    $productPromise = $this->asyncPool->add(function() use ($productId) {
                        return $this->productModel->find($productId);
                    });
                    
                    // Wait for async task to complete
                    $this->asyncPool->wait();
                    
                    // Get product result
                    $product = $productPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($productId) {
                        error_log('Error fetching product asynchronously: ' . $e->getMessage());
                        return $this->productModel->find($productId);
                    });
                    
                    if (!$product || empty($product['flavor'])) {
                        return [];
                    }
                    
                    // Now fetch similar flavor products
                    $similarPromise = $this->asyncPool->add(function() use ($product, $productId, $limit) {
                        $sql = "SELECT * FROM products WHERE flavor LIKE ? AND id != ? ORDER BY RAND() LIMIT ?";
                        return $this->productModel->query($sql, ['%' . $product['flavor'] . '%', $productId, $limit]);
                    });
                    
                    // Wait for async task to complete
                    $this->asyncPool->wait();
                    
                    // Get similar products result
                    $results = $similarPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($product, $productId, $limit) {
                        error_log('Error fetching similar flavor products asynchronously: ' . $e->getMessage());
                        $sql = "SELECT * FROM products WHERE flavor LIKE ? AND id != ? ORDER BY RAND() LIMIT ?";
                        return $this->productModel->query($sql, ['%' . $product['flavor'] . '%', $productId, $limit]);
                    });
                } catch (\Exception $e) {
                    error_log('Async processing error: ' . $e->getMessage());
                    // Fall back to standard approach
                    $product = $this->productModel->find($productId);
                    
                    if (!$product || empty($product['flavor'])) {
                        return [];
                    }
                    
                    $sql = "SELECT * FROM products WHERE flavor LIKE ? AND id != ? ORDER BY RAND() LIMIT ?";
                    $results = $this->productModel->query($sql, ['%' . $product['flavor'] . '%', $productId, $limit]);
                }
            } else {
                // Standard approach without async
                $product = $this->productModel->find($productId);
                
                if (!$product || empty($product['flavor'])) {
                    return [];
                }
                
                $sql = "SELECT * FROM products WHERE flavor LIKE ? AND id != ? ORDER BY RAND() LIMIT ?";
                $results = $this->productModel->query($sql, ['%' . $product['flavor'] . '%', $productId, $limit]);
            }
            
            // Ensure we return an array
            $similarProducts = is_array($results) ? $results : [];
            
            // Store in cache for 3 hours
            $this->cache->set($cacheKey, $similarProducts, 10800);
        }
        
        return $similarProducts;
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
            // Use async to try multiple fetching methods in parallel if available
            if ($this->asyncPool) {
                try {
                    $guzzlePromise = $this->asyncPool->add(function() use ($limit) {
                        return $this->fetchWithGuzzle('trending', ['limit' => $limit]);
                    });
                    
                    $curlPromise = $this->asyncPool->add(function() use ($limit) {
                        return $this->fetchWithCurl('trending', ['limit' => $limit]);
                    });
                    
                    $featuredPromise = $this->asyncPool->add(function() use ($limit) {
                        return $this->productModel->getFeaturedProducts($limit);
                    });
                    
                    // Wait for async tasks to complete
                    $this->asyncPool->wait();
                    
                    // Get results and use the first successful one
                    $guzzleResult = null;
                    $curlResult = null;
                    $featuredResult = null;
                    
                    $guzzlePromise->then(function($result) use (&$guzzleResult) {
                        $guzzleResult = $result;
                    })->catch(function(\Exception $e) {
                        error_log('Error fetching trending products with Guzzle asynchronously: ' . $e->getMessage());
                    });
                    
                    $curlPromise->then(function($result) use (&$curlResult) {
                        $curlResult = $result;
                    })->catch(function(\Exception $e) {
                        error_log('Error fetching trending products with cURL asynchronously: ' . $e->getMessage());
                    });
                    
                    $featuredPromise->then(function($result) use (&$featuredResult) {
                        $featuredResult = $result;
                    })->catch(function(\Exception $e) {
                        error_log('Error fetching featured products asynchronously: ' . $e->getMessage());
                    });
                    
                    // Use the first available result
                    if (!empty($guzzleResult)) {
                        $trending = $guzzleResult;
                    } elseif (!empty($curlResult)) {
                        $trending = $curlResult;
                    } elseif (!empty($featuredResult)) {
                        $trending = $featuredResult;
                    } else {
                        // Fallback to empty array if all methods fail
                        $trending = [];
                    }
                } catch (\Exception $e) {
                    error_log('Async processing error: ' . $e->getMessage());
                    // Fall back to standard approach
                    $trending = $this->fetchWithGuzzle('trending', ['limit' => $limit]);
                    
                    if ($trending === null) {
                        // Fallback to cURL if Guzzle fails
                        $trending = $this->fetchWithCurl('trending', ['limit' => $limit]);
                    }
                    
                    if (empty($trending)) {
                        // If both methods fail or return empty, use local fallback
                        $trending = $this->productModel->getFeaturedProducts($limit);
                    }
                }
            } else {
                // Standard approach without async
                $trending = $this->fetchWithGuzzle('trending', ['limit' => $limit]);
                
                if ($trending === null) {
                    // Fallback to cURL if Guzzle fails
                    $trending = $this->fetchWithCurl('trending', ['limit' => $limit]);
                }
                
                if (empty($trending)) {
                    // If both methods fail or return empty, use local fallback
                    $trending = $this->productModel->getFeaturedProducts($limit);
                }
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
            // Use async if available
            if ($this->asyncPool) {
                try {
                    $productPromise = $this->asyncPool->add(function() use ($id) {
                        return $this->productModel->find($id);
                    });
                    
                    // Wait for async task to complete
                    $this->asyncPool->wait();
                    
                    // Get product result
                    $product = $productPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($id) {
                        error_log('Error fetching product asynchronously: ' . $e->getMessage());
                        return $this->productModel->find($id);
                    });
                } catch (\Exception $e) {
                    error_log('Async processing error: ' . $e->getMessage());
                    // Fall back to standard approach
                    $product = $this->productModel->find($id);
                }
            } else {
                // Standard approach without async
                $product = $this->productModel->find($id);
            }
            
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
        
        // Use async to try multiple syncing methods in parallel if available
        if ($this->asyncPool) {
            try {
                $guzzlePromise = $this->asyncPool->add(function() {
                    return $this->syncWithGuzzle();
                });
                
                $curlPromise = $this->asyncPool->add(function() {
                    return $this->syncWithCurl();
                });
                
                // Wait for async tasks to complete
                $this->asyncPool->wait();
                
                // Get results and use the first successful one
                $guzzleResult = false;
                $curlResult = false;
                
                $guzzlePromise->then(function($result) use (&$guzzleResult) {
                    $guzzleResult = $result;
                })->catch(function(\Exception $e) {
                    error_log('Error syncing products with Guzzle asynchronously: ' . $e->getMessage());
                });
                
                $curlPromise->then(function($result) use (&$curlResult) {
                    $curlResult = $result;
                })->catch(function(\Exception $e) {
                    error_log('Error syncing products with cURL asynchronously: ' . $e->getMessage());
                });
                
                // Use the first successful result
                $synced = $guzzleResult || $curlResult;
            } catch (\Exception $e) {
                error_log('Async processing error: ' . $e->getMessage());
                // Fall back to standard approach
                $synced = $this->syncWithGuzzle();
                
                if (!$synced) {
                    // Fallback to cURL if Guzzle fails
                    $synced = $this->syncWithCurl();
                }
            }
        } else {
            // Standard approach without async
            $synced = $this->syncWithGuzzle();
            
            if (!$synced) {
                // Fallback to cURL if Guzzle fails
                $synced = $this->syncWithCurl();
            }
        }
        
        if ($synced) {
            // Clear all product-related caches asynchronously
            if ($this->asyncPool) {
                $this->asyncPool->add(function() {
                    $this->clearAllProductCache();
                    return true;
                })->then(function($result) {
                    error_log('Product cache cleared asynchronously');
                })->catch(function(\Exception $e) {
                    error_log('Error clearing product cache asynchronously: ' . $e->getMessage());
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
        $this->cache->delete($this->cache->generateKey('similar_flavor_products', ['product_id' => $productId]));
        
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
        
        // Clear flavor search cache
        $this->cache->delete($this->cache->generateKey('product_flavor_search', []));
        
        // Clear capsule filter cache
        $this->cache->delete($this->cache->generateKey('product_capsule_filter', []));
        
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
