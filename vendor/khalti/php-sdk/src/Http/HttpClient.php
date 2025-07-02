<?php
namespace KhaltiSDK\Http;

use KhaltiSDK\Config\Configuration;
use KhaltiSDK\Exceptions\ApiException;
use KhaltiSDK\Exceptions\NetworkException;
use KhaltiSDK\Utils\Logger;

/**
 * HTTP client for making API requests
 * 
 * @package KhaltiSDK\Http
 */
class HttpClient
{
    /**
     * @var Configuration SDK configuration
     */
    private $config;

    /**
     * @var Logger Logger instance
     */
    private $logger;

    /**
     * Create a new HttpClient instance
     * 
     * @param Configuration $config SDK configuration
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;
        $this->logger = new Logger($config);
    }

    /**
     * Make a GET request
     * 
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @return array Response data
     * @throws NetworkException If network error occurs
     * @throws ApiException If API returns an error
     */
    public function get($endpoint, array $params = [])
    {
        $url = $this->buildUrl($endpoint);
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $this->logger->debug('Making GET request', ['url' => $url]);
        
        $ch = $this->initCurl($url);
        
        return $this->executeRequest($ch);
    }

    /**
     * Make a POST request
     * 
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array Response data
     * @throws NetworkException If network error occurs
     * @throws ApiException If API returns an error
     */
    public function post($endpoint, array $data = [])
    {
        $url = $this->buildUrl($endpoint);
        
        $this->logger->debug('Making POST request', ['url' => $url, 'data' => $data]);
        
        $ch = $this->initCurl($url);
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        return $this->executeRequest($ch);
    }

    /**
     * Build the full URL for an API endpoint
     * 
     * @param string $endpoint API endpoint
     * @return string Full URL
     */
    private function buildUrl($endpoint)
    {
        return rtrim($this->config->getBaseUrl(), '/') . '/' . ltrim($endpoint, '/');
    }

    /**
     * Initialize a cURL handle with common options
     * 
     * @param string $url Request URL
     * @return resource|\CurlHandle cURL handle (resource in PHP 7, CurlHandle in PHP 8)
     */
    private function initCurl($url)
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config->getTimeout());
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Key ' . $this->config->getSecretKey(),
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        return $ch;
    }

    /**
     * Execute a cURL request and process the response
     * 
     * @param resource|\CurlHandle $ch cURL handle (resource in PHP 7, CurlHandle in PHP 8)
     * @return array Response data
     * @throws NetworkException If network error occurs
     * @throws ApiException If API returns an error
     */
    private function executeRequest($ch)
    {
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        
        curl_close($ch);
        
        if ($errno !== 0) {
            $this->logger->error('cURL error', ['error' => $error, 'code' => $errno]);
            throw new NetworkException("cURL error: {$error}", $errno);
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Invalid JSON response', ['response' => $response]);
            throw new ApiException('Invalid JSON response from API', $statusCode);
        }
        
        if ($statusCode >= 400) {
            $message = $data['detail'] ?? 'Unknown API error';
            $this->logger->error('API error', ['status' => $statusCode, 'message' => $message, 'response' => $data]);
            throw new ApiException($message, $statusCode, null, $data);
        }
        
        return $data;
    }
}

