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
     * Display about page with caching
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
                // Send email using cURL
                $emailSent = $this->sendEmailWithCurl($name, $email, $subject, $message);
                
                if ($emailSent) {
                    $this->setFlash('success', 'Your message has been sent. We will get back to you soon!');
                    $this->redirect('home/contact');
                } else {
                    // Fallback to traditional email sending
                    $headers = "From: $name <$email>\r\n";
                    $headers .= "Reply-To: $email\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                    
                    $emailBody = "<h2>Contact Form Submission</h2>";
                    $emailBody .= "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
                    $emailBody .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
                    $emailBody .= "<p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>";
                    $emailBody .= "<p><strong>Message:</strong></p>";
                    $emailBody .= "<p>" . nl2br(htmlspecialchars($message)) . "</p>";
                    
                    // Send email using PHP's mail function as fallback
                    if (mail(MAIL_FROM_ADDRESS, 'Contact Form: ' . $subject, $emailBody, $headers)) {
                        $this->setFlash('success', 'Your message has been sent. We will get back to you soon!');
                        $this->redirect('home/contact');
                    } else {
                        $errors['general'] = 'Failed to send email. Please try again later.';
                    }
                }
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
     * Send email using cURL
     * 
     * @param string $name
     * @param string $email
     * @param string $subject
     * @param string $message
     * @return bool
     */
    private function sendEmailWithCurl($name, $email, $subject, $message)
    {
        // Check if cURL is available
        if (!function_exists('curl_init')) {
            return false;
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
            
            // Close cURL
            curl_close($ch);
            
            // Log the response for debugging
            error_log('Email API Response: ' . $response . ' (HTTP Code: ' . $httpCode . ')');
            
            // Check if the request was successful
            return ($httpCode >= 200 && $httpCode < 300);
            
        } catch (\Exception $e) {
            // Log the error
            error_log('Email sending error: ' . $e->getMessage());
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
            
            // Example of using cURL to subscribe to a newsletter service
            $subscribed = $this->subscribeToNewsletter($email);
            
            if ($subscribed) {
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
     * @return bool
     */
    private function subscribeToNewsletter($email)
    {
        if (!function_exists('curl_init')) {
            return false;
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
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            // Execute the request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Close cURL
            curl_close($ch);
            
            // Check if the request was successful
            return ($httpCode >= 200 && $httpCode < 300);
            
        } catch (\Exception $e) {
            error_log('Newsletter subscription error: ' . $e->getMessage());
            return false;
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
}
