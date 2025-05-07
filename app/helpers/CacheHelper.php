<?php
namespace App\Helpers;

/**
* Cache Helper
* Provides caching functionality
*/
class CacheHelper
{
    private static $instance = null;
    private $cache = [];
    private $expiry = [];
    private $cachePath;
    private $defaultTTL = 3600; // 1 hour default

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->cachePath = dirname(dirname(__FILE__)) . '/storage/cache';
        
        // Create cache directory if it doesn't exist
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
    }

    /**
     * Get cache instance (Singleton pattern)
     *
     * @return CacheHelper
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new CacheHelper();
        }
        return self::$instance;
    }

    /**
     * Generate a cache key
     *
     * @param string $prefix
     * @param array $params
     * @return string
     */
    public function generateKey($prefix, $params = [])
    {
        $key = $prefix;
        if (!empty($params)) {
            $key .= '_' . md5(serialize($params));
        }
        return $key;
    }

    /**
     * Get cached data
     *
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        if (!defined('CACHE_ENABLED') || !CACHE_ENABLED) {
            return null;
        }
        
        // Try memory cache first
        if (isset($this->cache[$key]) && isset($this->expiry[$key])) {
            if ($this->expiry[$key] > time()) {
                return $this->cache[$key];
            }
            // Remove expired cache
            unset($this->cache[$key]);
            unset($this->expiry[$key]);
        }

        // Try file cache
        $filename = $this->cachePath . '/' . md5($key) . '.cache';
        if (file_exists($filename)) {
            $data = unserialize(file_get_contents($filename));
            if ($data['expiry'] > time()) {
                // Store in memory cache
                $this->cache[$key] = $data['value'];
                $this->expiry[$key] = $data['expiry'];
                return $data['value'];
            }
            // Remove expired cache file
            unlink($filename);
        }

        return null;
    }

    /**
     * Set cached data
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        if (!defined('CACHE_ENABLED') || !CACHE_ENABLED) {
            return false;
        }
        
        $ttl = $ttl ?? $this->defaultTTL;
        $expiry = time() + $ttl;

        // Store in memory cache
        $this->cache[$key] = $value;
        $this->expiry[$key] = $expiry;

        // Store in file cache
        $filename = $this->cachePath . '/' . md5($key) . '.cache';
        $data = [
            'value' => $value,
            'expiry' => $expiry
        ];
        file_put_contents($filename, serialize($data));

        return true;
    }

    /**
     * Delete specific cache
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        // Remove from memory cache
        unset($this->cache[$key]);
        unset($this->expiry[$key]);

        // Remove from file cache
        $filename = $this->cachePath . '/' . md5($key) . '.cache';
        if (file_exists($filename)) {
            unlink($filename);
        }

        return true;
    }

    /**
     * Clear all cache
     *
     * @return bool
     */
    public function clear()
    {
        // Clear memory cache
        $this->cache = [];
        $this->expiry = [];

        // Clear file cache
        $files = glob($this->cachePath . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }

        return true;
    }

    /**
     * Remember data with callback
     *
     * @param string $key
     * @param int $ttl
     * @param callable $callback
     * @return mixed
     */
    public function remember($key, $ttl, $callback)
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
}
