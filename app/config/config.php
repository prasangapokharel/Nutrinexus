<?php
/**
 * Application configuration
 */

// Environment setting (development, testing, production)
define('ENVIRONMENT', 'development');

// Base URL of the application
define('BASE_URL', 'http://localhost:8000');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'nutrinexus'); // Make sure this matches your database name
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

// Email configuration
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@example.com');
define('MAIL_PASSWORD', 'your-password');
define('MAIL_FROM_ADDRESS', 'info@example.com');
define('MAIL_FROM_NAME', 'Nutri Nexus');

// API Keys for email and newsletter services
define('EMAIL_API_KEY', 'your-email-api-key-here'); // Replace with your actual email API key (e.g., Mailgun, SendGrid)
define('NEWSLETTER_API_KEY', 'your-newsletter-api-key-here'); // Replace with your actual newsletter API key (e.g., Mailchimp)

// Cache configuration
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour

// API Keys for khalti 
define('KHALTI_API_KEY', 'live_secret_key_68791341fdd94846a146f0457ff7b455'); // Replace with your actual khalti 


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
define('TAX_RATE', 18); // 18% GST

// Default timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
