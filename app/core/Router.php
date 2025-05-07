<?php
namespace App\Core;

/**
 * Router class
 */
class Router
{
    protected $routes = [
        'GET' => [],
        'POST' => []
    ];

    /**
     * Register a GET route
     */
    public function get($uri, $controller)
    {
        $this->routes['GET'][$uri] = $controller;
    }

    /**
     * Register a POST route
     */
    public function post($uri, $controller)
    {
        $this->routes['POST'][$uri] = $controller;
    }

    /**
     * Resolve the current route
     */
    public function resolve()
    {
        $uri = $this->getUri();
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Check for exact match
        if (isset($this->routes[$method][$uri])) {
            return $this->parseController($this->routes[$method][$uri]);
        }
        
        // Check for routes with parameters
        foreach ($this->routes[$method] as $route => $controller) {
            if (strpos($route, '{') !== false) {
                $pattern = $this->convertRouteToRegex($route);
                
                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches); // Remove the full match
                    
                    list($controller, $method) = $this->parseController($controller);
                    
                    return [$controller, $method, $matches];
                }
            }
        }
        
        return null;
    }

    /**
     * Convert route with parameters to regex pattern
     */
    private function convertRouteToRegex($route)
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
        return '#^' . $pattern . '$#';
    }

    /**
     * Parse controller string (Controller@method)
     */
    private function parseController($controller)
    {
        $segments = explode('@', $controller);
        
        return [$segments[0], $segments[1], []];
    }

    /**
     * Get the current URI
     */
    private function getUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        // Remove query string
        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        
        // Remove base path
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && $basePath !== '\\') {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Remove leading and trailing slashes
        return trim($uri, '/');
    }
}
