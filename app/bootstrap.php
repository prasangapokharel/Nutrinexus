<?php
// Start session
session_start();

// Load configuration
require_once 'config/config.php';
require_once 'config/database.php';

// Set error reporting
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}

// Define cache constant
define('CACHE_ENABLED', true);

// Autoload classes
spl_autoload_register(function ($className) {
    // Convert namespace to file path
    $className = str_replace('\\', '/', $className);
    $file = dirname(__FILE__) . '/' . $className . '.php';
    
    // Check if file exists
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load Composer autoloader if it exists
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// Initialize the application
$app = new App\Core\App();
// Define cache directory
define('CACHE_DIR', __DIR__ . '/../storage/cache');

// Create cache directory if it doesn't exist
if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

// Add Symfony Cache to Composer autoload if not already included
// This is just a reminder - you should add these dependencies to your composer.json
// "symfony/cache": "^6.0",
// "symfony/var-exporter": "^6.0"
