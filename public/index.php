<?php
/**
 * Main entry point for the application
 * 
 * This file initializes the application and routes requests
 * to the appropriate controllers
 */
 
 ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Define constants
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__));

define('APPROOT', ROOT . DS . 'App');
// Update the URLROOT constant to match your base URL
define('URLROOT', 'http://192.168.1.74:8000');

// Load configuration
require_once APPROOT . DS . 'Config' . DS . 'config.php';

// Autoload classes
spl_autoload_register(function($className) {
   // Convert namespace to file path
   $className = str_replace('\\', DS, $className);
   $file = ROOT . DS . $className . '.php';
   
   if (file_exists($file)) {
       require_once $file;
   } else {
       // For debugging - comment out in production
       // echo "Could not find file: $file for class: $className<br>";
   }
});

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize the application
$app = new App\Core\App();
