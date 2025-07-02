<?php
namespace KhaltiSDK\Services;

use KhaltiSDK\Config\Configuration;
use KhaltiSDK\Exceptions\ApiException;
use KhaltiSDK\Exceptions\ValidationException;
use KhaltiSDK\Http\HttpClient;
use KhaltiSDK\Utils\Logger;

/**
 * Service for Khalti Wallet API
 * 
 * @package KhaltiSDK\Services
 */
class WalletService
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
     * Create a new WalletService instance
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
     * Initiate a wallet payment
     * 
     * @param array $params Payment parameters
     * @return array Payment initiation response
     * @throws ValidationException If parameters are invalid
     * @throws ApiException If API request fails
     */
    public function initiate(array $params)
    {
        $this->logger->info('Initiating wallet payment', ['params' => $params]);

        // Validate required parameters
        $requiredParams = ['public_key', 'mobile', 'amount', 'product_identity', 'product_name'];
        $this->validateParams($params, $requiredParams);

        // Make API request
        $response = $this->httpClient->post('/payment/initiate/', $params);
        
        $this->logger->info('Wallet payment initiated successfully', ['response' => $response]);
        
        return $response;
    }

    /**
     * Verify a wallet payment
     * 
     * @param string $token Payment token
     * @param int $amount Payment amount in paisa
     * @return array Payment verification response
     * @throws ValidationException If parameters are invalid
     * @throws ApiException If API request fails
     */
    public function verify($token, $amount)
    {
        if (empty($token)) {
            throw new ValidationException('Payment token is required');
        }

        if (empty($amount) || !is_numeric($amount)) {
            throw new ValidationException('Amount must be a valid number');
        }

        $this->logger->info('Verifying wallet payment', ['token' => $token, 'amount' => $amount]);

        // Make API request
        $response = $this->httpClient->post('/payment/verify/', [
            'token' => $token,
            'amount' => $amount
        ]);
        
        $this->logger->info('Wallet payment verified successfully', ['response' => $response]);
        
        return $response;
    }

    /**
     * Validate parameters
     * 
     * @param array $params Parameters to validate
     * @param array $required Required parameters
     * @throws ValidationException If parameters are invalid
     */
    private function validateParams(array $params, array $required)
    {
        foreach ($required as $param) {
            if (!isset($params[$param]) || empty($params[$param])) {
                throw new ValidationException("{$param} is required");
            }
        }
    }
}

