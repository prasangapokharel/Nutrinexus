<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\OrderItem;
use App\Helpers\CacheHelper;

// Import phpFastCache
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Drivers\Files\Config as FilesConfig;

// Import Spatie Async
use Spatie\Async\Pool;

class HomeController extends Controller
{
    private $productModel;
    private $productImageModel;
    private $orderItemModel;
    private $cache;
    private $hasSymfonyCache = false;
    private $symfonyCache = null;
    private $tagCache = null;
    
    // phpFastCache instance
    private $fastCache = null;
    private $hasFastCache = false;
    
    // Async pool
    private $asyncPool = null;
    private $hasAsync = false;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->productImageModel = new ProductImage();
        $this->orderItemModel = new OrderItem();
        $this->cache = CacheHelper::getInstance();
        
        // Initialize phpFastCache
        $this->initializeFastCache();
        
        // Initialize Spatie Async
        $this->initializeAsync();
        
        // Check if Symfony Cache components are available
        if (class_exists('\\Symfony\\Component\\Cache\\Adapter\\FilesystemAdapter') && 
            class_exists('\\Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter')) {
            
            try {
                // Create a filesystem adapter with a namespace and custom directory
                $this->symfonyCache = new \Symfony\Component\Cache\Adapter\FilesystemAdapter(
                    'app.cache', // namespace
                    0,           // default lifetime (0 = unlimited)
                    defined('CACHE_DIR') ? CACHE_DIR : __DIR__ . '/../../storage/cache' // directory
                );
                
                // Create a tag-aware adapter to allow cache invalidation by tags
                $this->tagCache = new \Symfony\Component\Cache\Adapter\TagAwareAdapter($this->symfonyCache);
                $this->hasSymfonyCache = true;
            } catch (\Exception $e) {
                // Log error but continue - we'll fall back to the regular cache
                error_log('Failed to initialize Symfony Cache: ' . $e->getMessage());
                $this->hasSymfonyCache = false;
            }
        } else {
            error_log('Symfony Cache components not available. Using standard cache only.');
        }
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
                    'securityKey' => 'nutrinexus_cache_key',
                    'htaccess' => true,
                    'defaultKeyHashFunction' => 'md5',
                    'defaultFileNameHashFunction' => 'md5',
                ]));
                
                // Get Files driver instance
                $this->fastCache = CacheManager::getInstance('files');
                $this->hasFastCache = true;
                
                error_log('phpFastCache initialized successfully');
            } else {
                error_log('phpFastCache not available');
                $this->hasFastCache = false;
            }
        } catch (\Exception $e) {
            error_log('Failed to initialize phpFastCache: ' . $e->getMessage());
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
                error_log('Spatie Async initialized successfully');
            } else {
                error_log('Spatie Async not available');
                $this->hasAsync = false;
            }
        } catch (\Exception $e) {
            error_log('Failed to initialize Spatie Async: ' . $e->getMessage());
            $this->hasAsync = false;
        }
    }

    /**
     * Display home page with enhanced caching
     */
    public function index()
    {
        // Try to get data from enhanced cache system
        $viewData = $this->getFromEnhancedCache('home_page_data', function() {
            if ($this->hasAsync) {
                return $this->getHomePageDataAsync();
            } else {
                return $this->getHomePageDataSync();
            }
        }, 3600, ['page' => 'home']);
        
        $this->view('home/index', $viewData);
    }

    /**
     * Get home page data asynchronously
     */
    private function getHomePageDataAsync()
    {
        try {
            $pool = $this->asyncPool;
            
            // Start async tasks
            $featuredProductsTask = $pool->add(function() {
                return $this->getProductsWithImages($this->productModel->getFeaturedProducts(8));
            });
            
            $bestSellingTask = $pool->add(function() {
                return $this->getProductsWithImages($this->orderItemModel->getBestSellingProducts(4));
            });
            
            $productsTask = $pool->add(function() {
                return $this->getProductsWithImages($this->productModel->getProducts(8, 0));
            });
            
            $categoriesTask = $pool->add(function() {
                return ['Protein', 'Creatine', 'Pre-Workout', 'Vitamins'];
            });
            
            // Wait for all tasks to complete
            $pool->wait();
            
            // Get results
            $featuredProducts = $featuredProductsTask->getOutput();
            $bestSellingProducts = $bestSellingTask->getOutput();
            $products = $productsTask->getOutput();
            $categories = $categoriesTask->getOutput();
            
            return [
                'featuredProducts' => $featuredProducts,
                'bestSellingProducts' => $bestSellingProducts,
                'categories' => $categories,
                'products' => $products,
                'popular_products' => $featuredProducts,
                'title' => 'Nutri Nexas - Premium Supplements',
                'cached_at' => date('Y-m-d H:i:s'),
                'cache_source' => 'async_database',
                'load_method' => 'async'
            ];
            
        } catch (\Exception $e) {
            error_log('Async execution failed: ' . $e->getMessage());
            return $this->getHomePageDataSync();
        }
    }

    /**
     * Get home page data synchronously (fallback)
     */
    private function getHomePageDataSync()
    {
        // Get featured products with images
        $featuredProducts = $this->getProductsWithImages($this->productModel->getFeaturedProducts(8));
        
        // Get best selling products with images
        $bestSellingProducts = $this->getProductsWithImages($this->orderItemModel->getBestSellingProducts(4));
        
        // Get all categories
        $categories = ['Protein', 'Creatine', 'Pre-Workout', 'Vitamins'];
        
        // Get all products for latest products section with images
        $products = $this->getProductsWithImages($this->productModel->getProducts(8, 0));
        
        // Get popular products (same as featured for now)
        $popular_products = $featuredProducts;
        
        return [
            'featuredProducts' => $featuredProducts,
            'bestSellingProducts' => $bestSellingProducts,
            'categories' => $categories,
            'products' => $products,
            'popular_products' => $popular_products,
            'title' => 'Nutri Nexas - Premium Supplements',
            'cached_at' => date('Y-m-d H:i:s'),
            'cache_source' => 'sync_database',
            'load_method' => 'sync'
        ];
    }

    /**
     * Display about page with caching
     */
    public function about()
    {
        // Try to get data from cache (with enhanced caching)
        $viewData = $this->getFromEnhancedCache('about_page_data', function() {
            return [
                'title' => 'About Us',
                'cached_at' => date('Y-m-d H:i:s'),
                'cache_source' => 'none'
            ];
        }, 86400, ['page' => 'about', 'static' => true]);
        
        $this->view('home/about', $viewData);
    }

    /**
     * Display privacy page with caching
     */
    public function privacy()
    {
        // Try to get data from cache (with enhanced caching)
        $viewData = $this->getFromEnhancedCache('privacy_page_data', function() {
            return [
                'title' => 'Privacy Policy',
                'cached_at' => date('Y-m-d H:i:s'),
                'cache_source' => 'none'
            ];
        }, 86400, ['page' => 'privacy', 'static' => true]);
        
        $this->view('pages/privacy', $viewData);
    }

    public function faq()
    {
        // Try to get data from cache (with enhanced caching)
        $viewData = $this->getFromEnhancedCache('faq_page_data', function() {
            return [
                'title' => 'FAQ',
                'cached_at' => date('Y-m-d H:i:s'),
                'cache_source' => 'none'
            ];
        }, 86400, ['page' => 'faq', 'static' => true]);
        
        $this->view('pages/faq', $viewData);
    }

    public function terms()
    {
        // Try to get data from cache (with enhanced caching)
        $viewData = $this->getFromEnhancedCache('terms_page_data', function() {
            return [
                'title' => 'Terms and Conditions',
                'cached_at' => date('Y-m-d H:i:s'),
                'cache_source' => 'none'
            ];
        }, 86400, ['page' => 'terms', 'static' => true]);
        
        $this->view('pages/terms', $viewData);
    }
    
    public function shipping()
    {
        // Try to get data from cache (with enhanced caching)
        $viewData = $this->getFromEnhancedCache('shipping_page_data', function() {
            return [
                'title' => 'Shipping Policy',
                'cached_at' => date('Y-m-d H:i:s'),
                'cache_source' => 'none'
            ];
        }, 86400, ['page' => 'shipping', 'static' => true]);
        
        $this->view('pages/shipping', $viewData);
    }

    /**
     * Display authenticator page with caching
     */
    public function authenticator()
    {
        // Try to get data from cache (with enhanced caching)
        $authData = $this->getFromEnhancedCache('authenticator_page_data', function() {
            return [
                'title' => 'Authenticaor Wellcore',
                'cached_at' => date('Y-m-d H:i:s'),
                'cache_source' => 'none'
            ];
        }, 86400, ['page' => 'authenticator', 'static' => true]);
        
        $this->view('home/authenticator', $authData);
    }

    /**
     * Display contact page with caching for the template
     * (but not for form submissions)
     */
    public function contact()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process contact form asynchronously if possible
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            // Validate input
            $errors = [];
            
            if (empty($name)) {
                $errors['name'] = 'Name is required';
            }
            
            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            }
            
            if (empty($subject)) {
                $errors['subject'] = 'Subject is required';
            }
            
            if (empty($message)) {
                $errors['message'] = 'Message is required';
            }
            
            if (empty($errors)) {
                // Store form data in session for potential fallback use
                $_SESSION['contact_form'] = [
                    'name' => $name,
                    'email' => $email,
                    'subject' => $subject,
                    'message' => $message
                ];
                
                // Send email asynchronously if possible
                if ($this->hasAsync) {
                    $this->sendEmailAsync($name, $email, $subject, $message);
                } else {
                    $result = $this->sendEmailWithCurl($name, $email, $subject, $message);
                    
                    if ($result['success']) {
                        $this->setFlash('success', 'Your message has been sent. We will get back to you soon!');
                        $this->redirect('home/contact');
                    } else {
                        $this->setFlash('error', 'Failed to send message. Please try again later.');
                        $this->view('home/contact', [
                            'errors' => ['form' => 'Failed to send message'],
                            'name' => $name,
                            'email' => $email,
                            'subject' => $subject,
                            'message' => $message,
                            'title' => 'Contact Us'
                        ]);
                    }
                }
            } else {
                $this->view('home/contact', [
                    'errors' => $errors,
                    'name' => $name,
                    'email' => $email,
                    'subject' => $subject,
                    'message' => $message,
                    'title' => 'Contact Us'
                ]);
            }
        } else {
            // GET request - show contact form
            // Try to get template data from cache (with enhanced caching)
            $viewData = $this->getFromEnhancedCache('contact_page_data', function() {
                return [
                    'title' => 'Contact Us',
                    'cached_at' => date('Y-m-d H:i:s'),
                    'cache_source' => 'none'
                ];
            }, 86400, ['page' => 'contact', 'static' => true]);
            
            $this->view('home/contact', $viewData);
        }
    }

    /**
     * Send email asynchronously
     */
    private function sendEmailAsync($name, $email, $subject, $message)
    {
        try {
            $pool = $this->asyncPool;
            
            $emailTask = $pool->add(function() use ($name, $email, $subject, $message) {
                return $this->sendEmailWithCurl($name, $email, $subject, $message);
            });
            
            // Don't wait for completion - fire and forget
            // But we can add a callback for when it completes
            $emailTask->then(function($result) {
                if ($result['success']) {
                    error_log('Email sent successfully via async');
                } else {
                    error_log('Email failed via async: ' . json_encode($result));
                }
            });
            
            // Set success message immediately (optimistic)
            $this->setFlash('success', 'Your message is being sent. We will get back to you soon!');
            $this->redirect('home/contact');
            
        } catch (\Exception $e) {
            error_log('Async email failed: ' . $e->getMessage());
            // Fall back to synchronous sending
            $result = $this->sendEmailWithCurl($name, $email, $subject, $message);
            
            if ($result['success']) {
                $this->setFlash('success', 'Your message has been sent. We will get back to you soon!');
                $this->redirect('home/contact');
            } else {
                $this->setFlash('error', 'Failed to send message. Please try again later.');
                $this->redirect('home/contact');
            }
        }
    }
    
    /**
     * Send email using cURL
     * 
     * @param string $name
     * @param string $email
     * @param string $subject
     * @param string $message
     * @return array
     */
    private function sendEmailWithCurl($name, $email, $subject, $message)
    {
        // Check if cURL is available
        if (!function_exists('curl_init')) {
            // Fallback to traditional email sending
            $success = $this->sendFallbackEmail($name, $email, $subject, $message, 'cURL not available');
            return [
                'success' => $success,
                'method' => 'fallback'
            ];
        }
        
        try {
            // Example API endpoint for sending emails (replace with your actual email service)
            $apiUrl = 'https://api.emailservice.com/send';
            
            // Prepare the data
            $data = [
                'to' => defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'admin@example.com',
                'from' => $email,
                'name' => $name,
                'subject' => 'Contact Form: ' . $subject,
                'message' => $message,
            ];
            
            // Initialize cURL
            $ch = curl_init($apiUrl);
            
            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer your-api-key' // Replace with your API key
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for development
            
            // Execute the request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            // Close cURL
            curl_close($ch);
            
            // Check for cURL errors
            if ($error) {
                throw new \Exception('cURL error: ' . $error);
            }
            
            // Try to decode the response
            $responseData = json_decode($response, true) ?: $response;
            
            // Log the response for debugging
            error_log('Email API Response: ' . (is_string($responseData) ? $responseData : json_encode($responseData)) . ' (HTTP Code: ' . $httpCode . ')');
            
            // Return the result
            return [
                'success' => ($httpCode >= 200 && $httpCode < 300),
                'status_code' => $httpCode,
                'response' => $responseData
            ];
            
        } catch (\Exception $e) {
            // Log the error
            error_log('Email sending error: ' . $e->getMessage());
            
            // Fallback to traditional email sending
            $success = $this->sendFallbackEmail($name, $email, $subject, $message, $e->getMessage());
            
            return [
                'success' => $success,
                'error' => $e->getMessage(),
                'method' => 'fallback'
            ];
        }
    }
    
    /**
     * Fallback email sending method using PHP's mail function
     * 
     * @param string $name
     * @param string $email
     * @param string $subject
     * @param string $message
     * @param string $errorMessage
     * @return bool
     */
    private function sendFallbackEmail($name, $email, $subject, $message, $errorMessage = '')
    {
        try {
            // Log that we're using the fallback
            error_log('Using fallback email method. Original error: ' . $errorMessage);
            
            // Use the provided parameters or get from session if not provided
            $name = $name ?: ($_SESSION['contact_form']['name'] ?? 'Website Visitor');
            $email = $email ?: ($_SESSION['contact_form']['email'] ?? 'unknown@example.com');
            $subject = $subject ?: ($_SESSION['contact_form']['subject'] ?? 'Contact Form Submission');
            $message = $message ?: ($_SESSION['contact_form']['message'] ?? 'No message content available.');
            
            // Prepare email headers
            $headers = "From: $name <$email>\r\n";
            $headers .= "Reply-To: $email\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            
            // Prepare email body
            $emailBody = "<h2>Contact Form Submission</h2>";
            $emailBody .= "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
            $emailBody .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
            $emailBody .= "<p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>";
            $emailBody .= "<p><strong>Message:</strong></p>";
            $emailBody .= "<p>" . nl2br(htmlspecialchars($message)) . "</p>";
            
            // Add note about fallback
            $emailBody .= "<p><em>Note: This email was sent using the fallback method due to an API error.</em></p>";
            
            // Send email using PHP's mail function
            $toAddress = defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'admin@example.com';
            $result = mail($toAddress, 'Contact Form: ' . $subject, $emailBody, $headers);
            
            // Log the result
            error_log('Fallback email result: ' . ($result ? 'Success' : 'Failed'));
            
            return $result;
            
        } catch (\Exception $e) {
            error_log('Fallback email error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Subscribe to newsletter with enhanced caching
     */
    public function newsletter()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->setFlash('error', 'Please enter a valid email address');
                $this->redirect('');
                return;
            }
            
            // Subscribe to newsletter asynchronously if possible
            if ($this->hasAsync) {
                $this->subscribeNewsletterAsync($email);
            } else {
                $result = $this->subscribeToNewsletterWithCurl($email);
                
                if ($result['success']) {
                    $this->setFlash('success', 'Thank you for subscribing to our newsletter!');
                } else {
                    $this->setFlash('error', 'Failed to subscribe. Please try again later.');
                }
                
                $this->redirect('');
            }
        } else {
            $this->redirect('');
        }
    }

    /**
     * Subscribe to newsletter asynchronously
     */
    private function subscribeNewsletterAsync($email)
    {
        try {
            $pool = $this->asyncPool;
            
            $subscribeTask = $pool->add(function() use ($email) {
                return $this->subscribeToNewsletterWithCurl($email);
            });
            
            // Don't wait for completion - fire and forget
            $subscribeTask->then(function($result) {
                if ($result['success']) {
                    error_log('Newsletter subscription successful via async');
                } else {
                    error_log('Newsletter subscription failed via async: ' . json_encode($result));
                }
            });
            
            // Set success message immediately (optimistic)
            $this->setFlash('success', 'Thank you for subscribing to our newsletter!');
            $this->redirect('');
            
        } catch (\Exception $e) {
            error_log('Async newsletter subscription failed: ' . $e->getMessage());
            // Fall back to synchronous subscription
            $result = $this->subscribeToNewsletterWithCurl($email);
            
            if ($result['success']) {
                $this->setFlash('success', 'Thank you for subscribing to our newsletter!');
            } else {
                $this->setFlash('error', 'Failed to subscribe. Please try again later.');
            }
            
            $this->redirect('');
        }
    }
    
    /**
     * Subscribe to newsletter using cURL
     * 
     * @param string $email
     * @return array
     */
    private function subscribeToNewsletterWithCurl($email)
    {
        // Check if cURL is available
        if (!function_exists('curl_init')) {
            // Store the email for later retry
            $this->storeFailedSubscription($email, 'cURL not available');
            return [
                'success' => false,
                'error' => 'cURL not available'
            ];
        }
        
        try {
            // Example API endpoint for newsletter subscription
            $apiUrl = 'https://api.newsletter-service.com/subscribe';
            
            // Prepare the data
            $data = [
                'email' => $email,
                'list_id' => 'your-list-id', // Replace with your list ID
            ];
            
            // Initialize cURL
            $ch = curl_init($apiUrl);
            
            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer your-api-key' // Replace with your API key
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for development
            
            // Execute the request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            // Close cURL
            curl_close($ch);
            
            // Check for cURL errors
            if ($error) {
                throw new \Exception('cURL error: ' . $error);
            }
            
            // Try to decode the response
            $responseData = json_decode($response, true) ?: $response;
            
            // Log the response for debugging
            error_log('Newsletter API Response: ' . (is_string($responseData) ? $responseData : json_encode($responseData)) . ' (HTTP Code: ' . $httpCode . ')');
            
            // Check if the request was successful
            $success = ($httpCode >= 200 && $httpCode < 300);
            
            if (!$success) {
                // Store the email for later retry if the request failed
                $this->storeFailedSubscription($email, 'API returned status code ' . $httpCode);
            }
            
            // Return the result
            return [
                'success' => $success,
                'status_code' => $httpCode,
                'response' => $responseData
            ];
            
        } catch (\Exception $e) {
            // Log the error
            error_log('Newsletter subscription error: ' . $e->getMessage());
            
            // Store the email for later retry
            $this->storeFailedSubscription($email, $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Store failed subscription for later retry
     * 
     * @param string $email
     * @param string $errorMessage
     * @return void
     */
    private function storeFailedSubscription($email, $errorMessage)
    {
        try {
            // Create a file to store failed subscriptions if it doesn't exist
            $failedSubscriptionsFile = 'storage/failed_subscriptions.json';
            $failedSubscriptions = [];
            
            if (file_exists($failedSubscriptionsFile)) {
                $failedSubscriptions = json_decode(file_get_contents($failedSubscriptionsFile), true) ?? [];
            }
            
            // Add the failed subscription
            $failedSubscriptions[] = [
                'email' => $email,
                'error' => $errorMessage,
                'timestamp' => time(),
                'attempts' => 1
            ];
            
            // Save the updated list
            file_put_contents($failedSubscriptionsFile, json_encode($failedSubscriptions, JSON_PRETTY_PRINT));
            
            error_log('Stored failed subscription for later retry: ' . $email);
            
        } catch (\Exception $e) {
            error_log('Failed to store failed subscription: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear all caches (enhanced version)
     * Admin function to manually clear caches
     */
    public function clearCache()
    {
        // Only allow admin users to clear cache
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->setFlash('error', 'You do not have permission to perform this action');
            $this->redirect('');
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
        
        // Clear Symfony cache if available
        if ($this->hasSymfonyCache && $this->tagCache) {
            try {
                // Clear all caches with the 'page' tag
                $this->tagCache->invalidateTags(['page']);
                $clearedCaches[] = 'Symfony Cache';
            } catch (\Exception $e) {
                error_log('Failed to clear Symfony cache: ' . $e->getMessage());
            }
        }
        
        $this->setFlash('success', 'Cleared caches: ' . implode(', ', $clearedCaches));
        $this->redirect('admin/dashboard');
    }
    
    /**
     * Retry failed newsletter subscriptions with async processing
     * Admin function to retry failed subscriptions
     */
    public function retryFailedSubscriptions()
    {
        // Only allow admin users to retry failed subscriptions
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->setFlash('error', 'You do not have permission to perform this action');
            $this->redirect('');
            return;
        }
        
        $failedSubscriptionsFile = 'storage/failed_subscriptions.json';
        
        if (!file_exists($failedSubscriptionsFile)) {
            $this->setFlash('info', 'No failed subscriptions to retry');
            $this->redirect('admin/dashboard');
            return;
        }
        
        $failedSubscriptions = json_decode(file_get_contents($failedSubscriptionsFile), true) ?? [];
        
        if (empty($failedSubscriptions)) {
            $this->setFlash('info', 'No failed subscriptions to retry');
            $this->redirect('admin/dashboard');
            return;
        }
        
        if ($this->hasAsync) {
            $this->retryFailedSubscriptionsAsync($failedSubscriptions, $failedSubscriptionsFile);
        } else {
            $this->retryFailedSubscriptionsSync($failedSubscriptions, $failedSubscriptionsFile);
        }
    }

    /**
     * Retry failed subscriptions asynchronously
     */
    private function retryFailedSubscriptionsAsync($failedSubscriptions, $failedSubscriptionsFile)
    {
        try {
            $pool = $this->asyncPool;
            $tasks = [];
            
            // Create async tasks for each subscription
            foreach ($failedSubscriptions as $index => $subscription) {
                $email = $subscription['email'];
                $attempts = $subscription['attempts'] + 1;
                
                if ($attempts < 5) { // Don't retry more than 5 times
                    $tasks[$index] = $pool->add(function() use ($email) {
                        return $this->subscribeToNewsletterWithCurl($email);
                    });
                }
            }
            
            // Wait for all tasks to complete
            $pool->wait();
            
            // Process results
            $successCount = 0;
            $remainingSubscriptions = [];
            
            foreach ($failedSubscriptions as $index => $subscription) {
                if (isset($tasks[$index])) {
                    $result = $tasks[$index]->getOutput();
                    
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        // Keep it in the list for next retry
                        $subscription['attempts'] = $subscription['attempts'] + 1;
                        $subscription['timestamp'] = time();
                        $subscription['error'] = $result['error'] ?? 'Unknown error';
                        $remainingSubscriptions[] = $subscription;
                    }
                } else {
                    // Too many attempts, don't include in remaining
                    continue;
                }
            }
            
            // Save the updated list
            file_put_contents($failedSubscriptionsFile, json_encode($remainingSubscriptions, JSON_PRETTY_PRINT));
            
            $this->setFlash('success', "Async retry completed: {$successCount} successful, " . count($remainingSubscriptions) . " remaining.");
            $this->redirect('admin/dashboard');
            
        } catch (\Exception $e) {
            error_log('Async retry failed: ' . $e->getMessage());
            // Fall back to sync retry
            $this->retryFailedSubscriptionsSync($failedSubscriptions, $failedSubscriptionsFile);
        }
    }

    /**
     * Retry failed subscriptions synchronously
     */
    private function retryFailedSubscriptionsSync($failedSubscriptions, $failedSubscriptionsFile)
    {
        $successCount = 0;
        $remainingSubscriptions = [];
        
        // Process each failed subscription
        foreach ($failedSubscriptions as $subscription) {
            $email = $subscription['email'];
            $attempts = $subscription['attempts'] + 1;
            
            // Try to subscribe again
            $result = $this->subscribeToNewsletterWithCurl($email);
            
            if ($result['success']) {
                // Subscription succeeded
                $successCount++;
            } else {
                // Subscription failed again
                // Keep it in the list if we haven't tried too many times
                if ($attempts < 5) {
                    $subscription['attempts'] = $attempts;
                    $subscription['timestamp'] = time();
                    $subscription['error'] = $result['error'] ?? 'Unknown error';
                    $remainingSubscriptions[] = $subscription;
                }
            }
        }
        
        // Save the updated list
        file_put_contents($failedSubscriptionsFile, json_encode($remainingSubscriptions, JSON_PRETTY_PRINT));
        
        $this->setFlash('success', "Sync retry completed: {$successCount} successful, " . count($remainingSubscriptions) . " remaining.");
        $this->redirect('admin/dashboard');
    }
    
    /**
     * Enhanced cache method with phpFastCache, Symfony Cache, and fallback
     * 
     * @param string $key Cache key
     * @param callable $callback Function to execute on cache miss
     * @param int $lifetime Cache lifetime in seconds
     * @param array $tags Tags for cache invalidation (only used with Symfony cache)
     * @return mixed
     */
    private function getFromEnhancedCache($key, callable $callback, int $lifetime = 3600, array $tags = [])
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
                error_log('phpFastCache error: ' . $e->getMessage());
                // Continue to next cache method
            }
        }
        
        // Try Symfony cache if available
        if ($this->hasSymfonyCache && $this->tagCache) {
            try {
                // Use Symfony's cache system
                $cacheItem = $this->symfonyCache->getItem($key);
                
                if ($cacheItem->isHit()) {
                    // Cache hit - return cached data
                    $data = $cacheItem->get();
                    if (is_array($data)) {
                        $data['cache_source'] = 'symfony_cache';
                        $data['cache_hit'] = true;
                    }
                    return $data;
                }
                
                // Cache miss - execute callback
                $data = $callback();
                
                // Store in Symfony cache
                $cacheItem->set($data);
                $cacheItem->expiresAfter($lifetime);
                
                // Add tags if supported
                if (!empty($tags) && method_exists($this->tagCache, 'invalidateTags')) {
                    // We need to use the tag-aware adapter for saving with tags
                    $tagItem = $this->tagCache->getItem($key);
                    $tagItem->set($data);
                    $tagItem->expiresAfter($lifetime);
                    $tagItem->tag(array_keys($tags));
                    $this->tagCache->save($tagItem);
                } else {
                    // Just save with the regular adapter
                    $this->symfonyCache->save($cacheItem);
                }
                
                if (is_array($data)) {
                    $data['cache_source'] = 'symfony_cache_new';
                    $data['cache_hit'] = false;
                }
                return $data;
                
            } catch (\Exception $e) {
                // Log error and fall back to regular cache
                error_log('Symfony cache error: ' . $e->getMessage());
                // Continue to regular cache below
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
     * Warm up the cache for static pages with async processing
     * Admin function to pre-generate cache
     */
    public function warmupCache()
    {
        // Only allow admin users to warm up cache
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->setFlash('error', 'You do not have permission to perform this action');
            $this->redirect('');
            return;
        }
        
        if ($this->hasAsync) {
            $this->warmupCacheAsync();
        } else {
            $this->warmupCacheSync();
        }
    }

    /**
     * Warm up cache asynchronously
     */
    private function warmupCacheAsync()
    {
        try {
            $pool = $this->asyncPool;
            
            // List of pages to warm up
            $pages = [
                'home_page_data' => ['callback' => [$this, 'getHomePageData'], 'tags' => ['page' => 'home'], 'ttl' => 3600],
                'about_page_data' => ['callback' => [$this, 'getAboutPageData'], 'tags' => ['page' => 'about', 'static' => true], 'ttl' => 86400],
                'authenticator_page_data' => ['callback' => [$this, 'getAuthenticatorPageData'], 'tags' => ['page' => 'authenticator', 'static' => true], 'ttl' => 86400],
                'contact_page_data' => ['callback' => [$this, 'getContactPageData'], 'tags' => ['page' => 'contact', 'static' => true], 'ttl' => 86400],
                'privacy_page_data' => ['callback' => [$this, 'getPrivacyPageData'], 'tags' => ['page' => 'privacy', 'static' => true], 'ttl' => 86400],
                'faq_page_data' => ['callback' => [$this, 'getFaqPageData'], 'tags' => ['page' => 'faq', 'static' => true], 'ttl' => 86400],
                'terms_page_data' => ['callback' => [$this, 'getTermsPageData'], 'tags' => ['page' => 'terms', 'static' => true], 'ttl' => 86400],
                'shipping_page_data' => ['callback' => [$this, 'getShippingPageData'], 'tags' => ['page' => 'shipping', 'static' => true], 'ttl' => 86400]
            ];
            
            $tasks = [];
            
            // Create async tasks for each page
            foreach ($pages as $key => $config) {
                $tasks[$key] = $pool->add(function() use ($key, $config) {
                    try {
                        // Get data using the callback
                        $data = call_user_func($config['callback']);
                        
                        // Store in cache
                        $this->getFromEnhancedCache($key, function() use ($data) {
                            return $data;
                        }, $config['ttl'], $config['tags']);
                        
                        return ['success' => true, 'key' => $key];
                    } catch (\Exception $e) {
                        return ['success' => false, 'key' => $key, 'error' => $e->getMessage()];
                    }
                });
            }
            
            // Wait for all tasks to complete
            $pool->wait();
            
            // Process results
            $warmedUp = [];
            $failed = [];
            
            foreach ($tasks as $key => $task) {
                $result = $task->getOutput();
                if ($result['success']) {
                    $warmedUp[] = $result['key'];
                } else {
                    $failed[] = $result['key'] . ' (' . $result['error'] . ')';
                    error_log('Failed to warm up cache for ' . $result['key'] . ': ' . $result['error']);
                }
            }
            
            $message = 'Async cache warmup completed. ';
            if (!empty($warmedUp)) {
                $message .= 'Warmed up: ' . implode(', ', $warmedUp) . '. ';
            }
            if (!empty($failed)) {
                $message .= 'Failed: ' . implode(', ', $failed) . '.';
            }
            
            $this->setFlash('success', $message);
            $this->redirect('admin/dashboard');
            
        } catch (\Exception $e) {
            error_log('Async cache warmup failed: ' . $e->getMessage());
            // Fall back to sync warmup
            $this->warmupCacheSync();
        }
    }

    /**
     * Warm up cache synchronously
     */
    private function warmupCacheSync()
    {
        // List of pages to warm up
        $pages = [
            'home_page_data' => ['callback' => [$this, 'getHomePageData'], 'tags' => ['page' => 'home'], 'ttl' => 3600],
            'about_page_data' => ['callback' => [$this, 'getAboutPageData'], 'tags' => ['page' => 'about', 'static' => true], 'ttl' => 86400],
            'authenticator_page_data' => ['callback' => [$this, 'getAuthenticatorPageData'], 'tags' => ['page' => 'authenticator', 'static' => true], 'ttl' => 86400],
            'contact_page_data' => ['callback' => [$this, 'getContactPageData'], 'tags' => ['page' => 'contact', 'static' => true], 'ttl' => 86400],
            'privacy_page_data' => ['callback' => [$this, 'getPrivacyPageData'], 'tags' => ['page' => 'privacy', 'static' => true], 'ttl' => 86400],
            'faq_page_data' => ['callback' => [$this, 'getFaqPageData'], 'tags' => ['page' => 'faq', 'static' => true], 'ttl' => 86400],
            'terms_page_data' => ['callback' => [$this, 'getTermsPageData'], 'tags' => ['page' => 'terms', 'static' => true], 'ttl' => 86400],
            'shipping_page_data' => ['callback' => [$this, 'getShippingPageData'], 'tags' => ['page' => 'shipping', 'static' => true], 'ttl' => 86400]
        ];
        
        $warmedUp = [];
        
        // Warm up each page
        foreach ($pages as $key => $config) {
            try {
                // Get data using the callback
                $data = call_user_func($config['callback']);
                
                // Store in cache
                $this->getFromEnhancedCache($key, function() use ($data) {
                    return $data;
                }, $config['ttl'], $config['tags']);
                
                $warmedUp[] = $key;
            } catch (\Exception $e) {
                error_log('Failed to warm up cache for ' . $key . ': ' . $e->getMessage());
            }
        }
        
        $this->setFlash('success', 'Sync cache warmed up for: ' . implode(', ', $warmedUp));
        $this->redirect('admin/dashboard');
    }
    
    /**
     * Get home page data for cache warming
     * 
     * @return array
     */
    public function getHomePageData()
    {
        return $this->getHomePageDataSync();
    }
    
    /**
     * Get about page data for cache warming
     * 
     * @return array
     */
    public function getAboutPageData()
    {
        return [
            'title' => 'About Us',
            'cached_at' => date('Y-m-d H:i:s'),
            'cache_source' => 'warmup'
        ];
    }
    
    /**
     * Get authenticator page data for cache warming
     * 
     * @return array
     */
    public function getAuthenticatorPageData()
    {
        return [
            'title' => 'Authenticaor Wellcore',
            'cached_at' => date('Y-m-d H:i:s'),
            'cache_source' => 'warmup'
        ];
    }
    
    /**
     * Get contact page data for cache warming
     * 
     * @return array
     */
    public function getContactPageData()
    {
        return [
            'title' => 'Contact Us',
            'cached_at' => date('Y-m-d H:i:s'),
            'cache_source' => 'warmup'
        ];
    }

    /**
     * Get privacy page data for cache warming
     * 
     * @return array
     */
    public function getPrivacyPageData()
    {
        return [
            'title' => 'Privacy Policy',
            'cached_at' => date('Y-m-d H:i:s'),
            'cache_source' => 'warmup'
        ];
    }

    /**
     * Get FAQ page data for cache warming
     * 
     * @return array
     */
    public function getFaqPageData()
    {
        return [
            'title' => 'FAQ',
            'cached_at' => date('Y-m-d H:i:s'),
            'cache_source' => 'warmup'
        ];
    }

    /**
     * Get terms page data for cache warming
     * 
     * @return array
     */
    public function getTermsPageData()
    {
        return [
            'title' => 'Terms and Conditions',
            'cached_at' => date('Y-m-d H:i:s'),
            'cache_source' => 'warmup'
        ];
    }

    /**
     * Get shipping page data for cache warming
     * 
     * @return array
     */
    public function getShippingPageData()
    {
        return [
            'title' => 'Shipping Policy',
            'cached_at' => date('Y-m-d H:i:s'),
            'cache_source' => 'warmup'
        ];
    }

    /**
     * Get cache statistics
     * Admin function to view cache performance
     */
    public function cacheStats()
    {
        // Only allow admin users to view cache stats
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->setFlash('error', 'You do not have permission to perform this action');
            $this->redirect('');
            return;
        }
        
        $stats = [
            'phpfastcache' => ['available' => $this->hasFastCache, 'stats' => null],
            'symfony_cache' => ['available' => $this->hasSymfonyCache, 'stats' => null],
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
        
        // Get Symfony cache stats (if available)
        if ($this->hasSymfonyCache && $this->symfonyCache) {
            try {
                // Symfony cache doesn't have built-in stats, so we'll create our own
                $stats['symfony_cache']['stats'] = [
                    'adapter' => get_class($this->symfonyCache),
                    'namespace' => 'app.cache'
                ];
            } catch (\Exception $e) {
                $stats['symfony_cache']['error'] = $e->getMessage();
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
        
        $this->view('admin/cache-stats', [
            'title' => 'Cache Statistics',
            'stats' => $stats
        ]);
    }

    private function getProductsWithImages($products)
    {
        if (empty($products)) {
            return [];
        }
        
        foreach ($products as &$product) {
            // Get all images for this product
            $images = $this->productImageModel->getByProductId($product['id']);
            
            // Set default image structure
            $product['images'] = $images;
            $product['primary_image'] = null;
            
            // Find primary image (first check for is_primary flag, then use first image)
            foreach ($images as $image) {
                if (!empty($image['is_primary'])) {
                    $product['primary_image'] = $image;
                    break;
                }
            }
            
            // If no primary image found, use the first image
            if (empty($product['primary_image']) && !empty($images)) {
                $product['primary_image'] = $images[0];
            }
            
            // Fallback to placeholder if no images exist
            if (empty($product['primary_image'])) {
                $product['primary_image'] = [
                    'image_path' => '/assets/images/placeholder-product.jpg',
                    'alt_text' => 'Product image placeholder'
                ];
            }
        }
        
        return $products;
    }
}
