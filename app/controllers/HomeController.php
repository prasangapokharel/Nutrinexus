<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\OrderItem;
use App\Helpers\CacheHelper;

class HomeController extends Controller
{
    private $productModel;
    private $orderItemModel;
    private $cache;
    private $hasSymfonyCache = false;
    private $symfonyCache = null;
    private $tagCache = null;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->orderItemModel = new OrderItem();
        $this->cache = CacheHelper::getInstance();
        
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
     * Display home page with caching
     */
    public function index()
    {
        // Try to get data from cache (with Symfony cache if available)
        $viewData = $this->getFromCache('home_page_data', function() {
            // Get featured products
            $featuredProducts = $this->productModel->getFeaturedProducts(8);
            
            // Get best selling products using OrderItem model
            $bestSellingProducts = $this->orderItemModel->getBestSellingProducts(4);
            
            // Get all categories
            $categories = ['Protein', 'Creatine', 'Pre-Workout', 'Vitamins'];
            
            // Get all products for latest products section
            $products = $this->productModel->getProducts(8, 0);
            
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
                'cache_source' => 'database'
            ];
        }, 3600, ['page' => 'home']);
        
        $this->view('home/index', $viewData);
    }

    /**
     * Display about page with caching
     */
    public function about()
    {
        // Try to get data from cache (with Symfony cache if available)
        $viewData = $this->getFromCache('about_page_data', function() {
            return [
                'title' => 'About Us',
                'cached_at' => date('Y-m-d H:i:s'),
                'cache_source' => 'none'
            ];
        }, 86400, ['page' => 'about', 'static' => true]);
        
        $this->view('home/about', $viewData);
    }

    /**
     * Display authenticator page with caching
     */
    public function authenticator()
    {
        // Try to get data from cache (with Symfony cache if available)
        $authData = $this->getFromCache('authenticator_page_data', function() {
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
            // Process contact form
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
                
                // Send email synchronously
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
            // Try to get template data from cache (with Symfony cache if available)
            $viewData = $this->getFromCache('contact_page_data', function() {
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
     * Subscribe to newsletter with caching for the form
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
            
            // Subscribe to newsletter synchronously
            $result = $this->subscribeToNewsletterWithCurl($email);
            
            if ($result['success']) {
                $this->setFlash('success', 'Thank you for subscribing to our newsletter!');
            } else {
                $this->setFlash('error', 'Failed to subscribe. Please try again later.');
            }
            
            $this->redirect('');
        } else {
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
     * Clear all home page caches
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
        
        // Clear regular cache
        $this->cache->clear();
        
        // Clear Symfony cache if available
        if ($this->hasSymfonyCache && $this->tagCache) {
            try {
                // Clear all caches with the 'page' tag
                $this->tagCache->invalidateTags(['page']);
            } catch (\Exception $e) {
                error_log('Failed to clear Symfony cache: ' . $e->getMessage());
            }
        }
        
        $this->setFlash('success', 'All caches cleared successfully');
        $this->redirect('admin/dashboard');
    }
    
    /**
     * Retry failed newsletter subscriptions
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
        
        $this->setFlash('success', "Retried {$successCount} failed subscriptions. " . count($remainingSubscriptions) . " remaining.");
        $this->redirect('admin/dashboard');
    }
    
    /**
     * Get data from cache with fallback to callback
     * Uses Symfony cache if available, otherwise falls back to regular cache
     * 
     * @param string $key Cache key
     * @param callable $callback Function to execute on cache miss
     * @param int $lifetime Cache lifetime in seconds
     * @param array $tags Tags for cache invalidation (only used with Symfony cache)
     * @return mixed
     */
    private function getFromCache($key, callable $callback, int $lifetime = 3600, array $tags = [])
    {
        // Try Symfony cache first if available
        if ($this->hasSymfonyCache && $this->tagCache) {
            try {
                // Use Symfony's cache system
                $cacheItem = $this->symfonyCache->getItem($key);
                
                if ($cacheItem->isHit()) {
                    // Cache hit - return cached data
                    $data = $cacheItem->get();
                    if (is_array($data)) {
                        $data['cache_source'] = 'symfony_cache';
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
                $data['cache_source'] = 'database';
            }
        } else {
            // Mark that this came from the regular cache
            if (is_array($data)) {
                $data['cache_source'] = 'app_cache';
            }
        }
        
        return $data;
    }
    
    /**
     * Warm up the cache for static pages
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
        
        // List of pages to warm up
        $pages = [
            'home_page_data' => ['callback' => [$this, 'getHomePageData'], 'tags' => ['page' => 'home'], 'ttl' => 3600],
            'about_page_data' => ['callback' => [$this, 'getAboutPageData'], 'tags' => ['page' => 'about', 'static' => true], 'ttl' => 86400],
            'authenticator_page_data' => ['callback' => [$this, 'getAuthenticatorPageData'], 'tags' => ['page' => 'authenticator', 'static' => true], 'ttl' => 86400],
            'contact_page_data' => ['callback' => [$this, 'getContactPageData'], 'tags' => ['page' => 'contact', 'static' => true], 'ttl' => 86400]
        ];
        
        $warmedUp = [];
        
        // Warm up each page
        foreach ($pages as $key => $config) {
            try {
                // Get data using the callback
                $data = call_user_func($config['callback']);
                
                // Store in cache
                $this->getFromCache($key, function() use ($data) {
                    return $data;
                }, $config['ttl'], $config['tags']);
                
                $warmedUp[] = $key;
            } catch (\Exception $e) {
                error_log('Failed to warm up cache for ' . $key . ': ' . $e->getMessage());
            }
        }
        
        $this->setFlash('success', 'Cache warmed up for: ' . implode(', ', $warmedUp));
        $this->redirect('admin/dashboard');
    }
    
    /**
     * Get home page data for cache warming
     * 
     * @return array
     */
    public function getHomePageData()
    {
        // Get featured products
        $featuredProducts = $this->productModel->getFeaturedProducts(8);
        
        // Get best selling products using OrderItem model
        $bestSellingProducts = $this->orderItemModel->getBestSellingProducts(4);
        
        // Get all categories
        $categories = ['Protein', 'Creatine', 'Pre-Workout', 'Vitamins'];
        
        // Get all products for latest products section
        $products = $this->productModel->getProducts(8, 0);
        
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
            'cache_source' => 'warmup'
        ];
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
}