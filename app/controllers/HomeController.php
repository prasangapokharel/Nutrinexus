<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\OrderItem;
use App\Helpers\CacheHelper;
// Use the correct namespace for Spatie\Async\Pool
use Spatie\Async\Pool;

class HomeController extends Controller
{
    private $productModel;
    private $orderItemModel;
    private $cache;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->orderItemModel = new OrderItem();
        $this->cache = CacheHelper::getInstance();
    }

    /**
     * Display home page with caching
     */
    public function index()
    {
        // Try to get data from cache
        $viewData = $this->cache->get('home_page_data');
        
        if ($viewData === null) {
            // Cache miss - fetch from database
            
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
            
            $viewData = [
                'featuredProducts' => $featuredProducts,
                'bestSellingProducts' => $bestSellingProducts,
                'categories' => $categories,
                'products' => $products,
                'popular_products' => $popular_products,
                'title' => 'Nutri Nexas - Premium Supplements',
                'cached_at' => date('Y-m-d H:i:s')
            ];
            
            // Store in cache for 1 hour
            $this->cache->set('home_page_data', $viewData, 3600);
        }
        
        $this->view('home/index', $viewData);
    }

    /**
     * Display about page with caching
     */
    public function about()
    {
        // Try to get data from cache
        $viewData = $this->cache->get('about_page_data');
        
        if ($viewData === null) {
            // Cache miss - prepare view data
            $viewData = [
                'title' => 'About Us',
                'cached_at' => date('Y-m-d H:i:s')
            ];
            
            // Store in cache for 24 hours (static content)
            $this->cache->set('about_page_data', $viewData, 86400);
        }
        
        $this->view('home/about', $viewData);
    }

    /**
     * Display authenticator page with caching
     */
    public function authenticator()
    {
        // Try to get data from cache
        $authData = $this->cache->get('authenticator_page_data');
        
        if ($authData === null) {
            // Cache miss - prepare view data
            $authData = [
                'title' => 'Authenticaor Wellcore',
                'cached_at' => date('Y-m-d H:i:s')
            ];
            
            // Store in cache for 24 hours (static content)
            $this->cache->set('authenticator_page_data', $authData, 86400);
        }
        
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
                
                // Send email asynchronously using Spatie/Async
                $this->sendEmailAsync($name, $email, $subject, $message);
                
                // Set success message and redirect immediately
                // The email will be sent in the background
                $this->setFlash('success', 'Your message has been sent. We will get back to you soon!');
                $this->redirect('home/contact');
            }
            
            if (!empty($errors)) {
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
            // Try to get template data from cache
            $viewData = $this->cache->get('contact_page_data');
            
            if ($viewData === null) {
                // Cache miss - prepare view data
                $viewData = [
                    'title' => 'Contact Us',
                    'cached_at' => date('Y-m-d H:i:s')
                ];
                
                // Store in cache for 24 hours (static content)
                $this->cache->set('contact_page_data', $viewData, 86400);
            }
            
            $this->view('home/contact', $viewData);
        }
    }
    
    /**
     * Send email asynchronously using Spatie/Async with proper error handling
     * 
     * @param string $name
     * @param string $email
     * @param string $subject
     * @param string $message
     * @return void
     */
    private function sendEmailAsync($name, $email, $subject, $message)
    {
        // First check if the Spatie\Async\Pool class exists
        if (!class_exists('\\Spatie\\Async\\Pool')) {
            // Log that we're falling back to synchronous processing
            error_log('Spatie\\Async\\Pool class not found. Falling back to synchronous email sending.');
            
            // Fall back to synchronous email sending
            $this->sendEmailWithCurl($name, $email, $subject, $message);
            return;
        }
        
        try {
            // Create an async pool
            $pool = Pool::create();
            
            // Add the email sending task to the pool
            $pool->add(function () use ($name, $email, $subject, $message) {
                return $this->sendEmailWithCurl($name, $email, $subject, $message);
            })->then(function ($result) {
                // This runs when the task completes successfully
                error_log('Email sent successfully: ' . json_encode($result));
            })->catch(function (\Exception $exception) use ($name, $email, $subject, $message) {
                // This runs when the task fails
                error_log('Async email sending error: ' . $exception->getMessage());
                
                // Fallback to traditional email sending
                $this->sendFallbackEmail($name, $email, $subject, $message, $exception->getMessage());
            });
            
            // Start the pool (non-blocking)
            $pool->wait();
            
        } catch (\Exception $e) {
            // Log the error
            error_log('Failed to create async pool: ' . $e->getMessage());
            
            // Fallback to synchronous email sending with cURL
            $this->sendEmailWithCurl($name, $email, $subject, $message);
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
            
            // Subscribe to newsletter asynchronously
            $this->subscribeToNewsletterAsync($email);
            
            // Set success message and redirect immediately
            // The subscription will be processed in the background
            $this->setFlash('success', 'Thank you for subscribing to our newsletter!');
            $this->redirect('');
        } else {
            $this->redirect('');
        }
    }
    
    /**
     * Subscribe to newsletter asynchronously using Spatie/Async with proper error handling
     * 
     * @param string $email
     * @return void
     */
    private function subscribeToNewsletterAsync($email)
    {
        // First check if the Spatie\Async\Pool class exists
        if (!class_exists('\\Spatie\\Async\\Pool')) {
            // Log that we're falling back to synchronous processing
            error_log('Spatie\\Async\\Pool class not found. Falling back to synchronous newsletter subscription.');
            
            // Fall back to synchronous subscription
            $this->subscribeToNewsletterWithCurl($email);
            return;
        }
        
        try {
            // Create an async pool
            $pool = Pool::create();
            
            // Add the subscription task to the pool
            $pool->add(function () use ($email) {
                return $this->subscribeToNewsletterWithCurl($email);
            })->then(function ($result) {
                // This runs when the task completes successfully
                error_log('Newsletter subscription successful: ' . json_encode($result));
            })->catch(function (\Exception $exception) use ($email) {
                // This runs when the task fails
                error_log('Async newsletter subscription error: ' . $exception->getMessage());
                
                // Store the email for later retry
                $this->storeFailedSubscription($email, $exception->getMessage());
            });
            
            // Start the pool (non-blocking)
            $pool->wait();
            
        } catch (\Exception $e) {
            // Log the error
            error_log('Failed to create async pool for newsletter: ' . $e->getMessage());
            
            // Fallback to synchronous subscription with cURL
            $this->subscribeToNewsletterWithCurl($email);
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
        
        $this->cache->clear();
        $this->setFlash('success', 'Cache cleared successfully');
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
}