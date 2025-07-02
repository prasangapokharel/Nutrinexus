<?php
namespace KhaltiSDK\Services;

use KhaltiSDK\Config\Configuration;
use KhaltiSDK\Exceptions\ApiException;
use KhaltiSDK\Http\HttpClient;
use KhaltiSDK\Utils\Logger;

/**
 * Service for Khalti Transaction API
 * 
 * @package KhaltiSDK\Services
 */
class TransactionService
{
    /**
     * @var Configuration SDK configuration
     */
    private $config;

    /**
     * @var HttpClient HTTP client
     */
    private $httpClient;

    /**
     * @var Logger Logger instance
     */
    private $logger;

    /**
     * Create a new TransactionService instance
     * 
     * @param Configuration $config SDK configuration
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;
        $this->httpClient = new HttpClient($config);
        $this->logger = new Logger($config);
    }

    /**
     * List transactions
     * 
     * @param array $params Query parameters
     * @return array Transactions list
     * @throws ApiException If API request fails
     */
    public function list(array $params = [])
    {
        $this->logger->info('Listing transactions', ['params' => $params]);

        // Make API request
        $response = $this->httpClient->get('/merchant-transaction/', $params);
        
        $this->logger->info('Transactions listed successfully', ['count' => count($response['records'] ?? [])]);
        
        return $response;
    }

    /**
     * Get transaction details
     * 
     * @param string $idx Transaction ID
     * @return array Transaction details
     * @throws ApiException If API request fails
     */
    public function find($idx)
    {
        $this->logger->info('Getting transaction details', ['idx' => $idx]);

        // Make API request
        $response = $this->httpClient->get("/merchant-transaction/{$idx}/");
        
        $this->logger->info('Transaction details retrieved successfully');
        
        return $response;
    }
}

