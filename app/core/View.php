<?php

namespace App\Core;

/**
 * View helper class
 */
class View
{
    /**
     * Generate URL
     *
     * @param string $path
     * @return string
     */
    public static function url($path = '')
    {
        // Make sure BASE_URL is defined
        if (!defined('BASE_URL')) {
            define('BASE_URL', 'http://192.168.1.74:8000');
        }
        
        return BASE_URL . '/' . ltrim($path, '/');
    }

    /**
     * Generate asset URL
     *
     * @param string $path
     * @return string
     */
    public static function asset($path = '')
    {
        // Make sure BASE_URL is defined
        if (!defined('BASE_URL')) {
            define('BASE_URL', 'http://192.168.1.74:8000');
        }
        
        return BASE_URL . '/assets/' . ltrim($path, '/');
    }

    /**
     * Get physical asset path for file operations
     *
     * @param string $path
     * @return string
     */
    public static function assetPath($path = '')
    {
        // Remove leading slash if present
        $path = ltrim($path, '/');
        
        // Define possible asset directories in order of preference
        $assetDirs = [
            $_SERVER['DOCUMENT_ROOT'] . '/assets/',
            dirname(dirname(__DIR__)) . '/assets/',
            dirname(dirname(dirname(__DIR__))) . '/assets/',
            __DIR__ . '/../../assets/',
            getcwd() . '/assets/',
        ];
        
        // Try to find the asset in each directory
        foreach ($assetDirs as $dir) {
            $fullPath = $dir . $path;
            if (file_exists($fullPath) && is_readable($fullPath)) {
                return $fullPath;
            }
        }
        
        // If not found, return the most likely path (document root)
        return $_SERVER['DOCUMENT_ROOT'] . '/assets/' . $path;
    }

    /**
     * Check if asset exists
     *
     * @param string $path
     * @return bool
     */
    public static function assetExists($path = '')
    {
        $fullPath = self::assetPath($path);
        return file_exists($fullPath) && is_readable($fullPath);
    }

    /**
     * Get asset content (for templates, etc.)
     *
     * @param string $path
     * @return string|false
     */
    public static function getAssetContent($path = '')
    {
        $fullPath = self::assetPath($path);
        
        if (file_exists($fullPath) && is_readable($fullPath)) {
            return file_get_contents($fullPath);
        }
        
        return false;
    }

    /**
     * Include asset file (for PHP includes)
     *
     * @param string $path
     * @return mixed
     */
    public static function includeAsset($path = '')
    {
        $fullPath = self::assetPath($path);
        
        if (file_exists($fullPath) && is_readable($fullPath)) {
            return include $fullPath;
        }
        
        return false;
    }

    /**
     * Get base URL
     *
     * @return string
     */
    public static function getBaseUrl()
    {
        if (defined('BASE_URL')) {
            return BASE_URL;
        }
        
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        
        return $protocol . $host;
    }

    /**
     * Generate full URL with protocol and host
     *
     * @param string $path
     * @return string
     */
    public static function fullUrl($path = '')
    {
        return self::getBaseUrl() . '/' . ltrim($path, '/');
    }

    /**
     * Get current URL
     *
     * @return string
     */
    public static function currentUrl()
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        return $protocol . $host . $uri;
    }

    /**
     * Check if current URL matches given path
     *
     * @param string $path
     * @return bool
     */
    public static function isCurrentUrl($path)
    {
        $currentPath = parse_url(self::currentUrl(), PHP_URL_PATH);
        $checkPath = '/' . ltrim($path, '/');
        
        return $currentPath === $checkPath;
    }
}
