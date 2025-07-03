<?php
/**
 * Application configuration
 */

// Environment setting (development, testing, production)
define('ENVIRONMENT', 'development');

// Base URL of the application
define('BASE_URL', 'http://192.168.1.74:8000');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'nutrinexas'); // Make sure this matches your database name
define('DB_USER', 'root'); 
define('DB_PASS', '');

// App Root
if (!defined('APPROOT')) {
    define('APPROOT', dirname(dirname(__FILE__)));
}

// Site Name
define('SITENAME', 'Nutri Nexus');

// App Version
define('APPVERSION', '1.0.0');

define('API_KEY', ''); // Replace with your actual

// Email configuration (Updated for PHPMailer with Hostinger)
define('MAIL_HOST', 'smtp.hostinger.com');
define('MAIL_PORT', 465);
define('MAIL_USERNAME', 'support@nutrinexas.com');
define('MAIL_PASSWORD', 'N^ObG51g~');
define('MAIL_FROM_ADDRESS', 'support@nutrinexas.com');
define('MAIL_FROM_NAME', 'Nutri Nexus');
define('MAIL_ENCRYPTION', 'ssl'); // SSL encryption for port 465
define('MAIL_DEBUG', 2); // Set to 2 for detailed debug output, 0 for production

// API Keys for email and newsletter services
define('EMAIL_API_KEY', 'your-email-api-key-here'); // Replace with your actual email API key (e.g., Mailgun, SendGrid)
define('NEWSLETTER_API_KEY', 'your-newsletter-api-key-here'); // Replace with your actual newsletter API key (e.g., Mailchimp)

// Cache configuration
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour

// API Keys for khalti 
define('KHALTI_SECRET_KEY', 'live_secret_key_68791341fdd94846a146f0457ff7b455'); 

// Khalti API endpoints
define('KHALTI_INITIATE_URL', 'https://dev.khalti.com/api/v2/epayment/initiate/');
define('KHALTI_LOOKUP_URL', 'https://dev.khalti.com/api/v2/epayment/lookup/');
define('KHALTI_VERIFY_URL', 'https://dev.khalti.com/api/v2/payment/verify/');

// Define ROOT_DIR if not already defined
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(dirname(__DIR__)));
}

// Upload directories
define('UPLOAD_DIR', ROOT_DIR . '/uploads');
define('PRODUCT_IMAGES_DIR', UPLOAD_DIR . '/products/');
define('PAYMENT_SCREENSHOTS_DIR', UPLOAD_DIR . '/payments/');

// Referral commission percentage
define('REFERRAL_COMMISSION', 10); // 10%

// Tax rate
define('TAX_RATE', 0); // 18% GST

// Default timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
