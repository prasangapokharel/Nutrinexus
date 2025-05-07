<?php
// Router script for PHP built-in server
$uri = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));

// Serve static files directly
if ($uri !== "/" && file_exists(__DIR__ . "/public" . $uri)) {
    return false;
}

// Otherwise, route everything to public/index.php
include_once __DIR__ . "/public/index.php";
