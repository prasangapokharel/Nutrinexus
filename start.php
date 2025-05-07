<?php

/**
 * NutriNexus Application Starter
 * 
 * This script starts the PHP built-in web server for development
 * and points it to the public directory.
 */

// Configuration
$host = 'localhost';
$port = 8000;
$public_dir = __DIR__ . '/public';

// Display startup message
echo "Starting NutriNexus application server...\n";
echo "Server will be available at http://{$host}:{$port}/\n";
echo "Press Ctrl+C to stop the server.\n\n";

// Check if the public directory exists
if (!is_dir($public_dir)) {
    die("Error: Public directory not found at {$public_dir}\n");
}

// Create router script to handle requests
$router_file = __DIR__ . '/router.php';
file_put_contents($router_file, '<?php
// Router script for PHP built-in server
$uri = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));

// Serve static files directly
if ($uri !== "/" && file_exists(__DIR__ . "/public" . $uri)) {
    return false;
}

// Otherwise, route everything to public/index.php
include_once __DIR__ . "/public/index.php";
');

// Start the server
$command = sprintf(
    'php -S %s:%d -t %s %s',
    $host,
    $port,
    escapeshellarg($public_dir),
    escapeshellarg($router_file)
);

// Execute the command
system($command);

// Clean up router file when server stops
unlink($router_file);
