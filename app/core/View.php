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
            define('BASE_URL', 'http://localhost:8000');
        }
        
        return BASE_URL . '/' . $path;
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
            define('BASE_URL', 'http://localhost:8000');
        }
        
        return BASE_URL . '/assets/' . $path;
    }
}
