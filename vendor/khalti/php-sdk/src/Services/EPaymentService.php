<?php
namespace KhaltiSDK\Services;

use KhaltiSDK\Config\Configuration;
use KhaltiSDK\Exceptions\ApiException;
use KhaltiSDK\Exceptions\ValidationException;
use KhaltiSDK\Http\HttpClient;
use KhaltiSDK\Utils\Logger;

/**
 * Service for Khalti E-Payment API
 * 
 * @package KhaltiSDK\Services
 */
class EPaymentService
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
     * Create a new EPaymentService instance
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
     * Initiate a payment
     * 
     * @param array $params Payment parameters
     * @return array Payment initiation response
     * @throws ValidationException If parameters are invalid
     * @throws ApiException If API request fails
     */
    public function initiate(array $params)
    {
        $this->logger->info('Initiating e-payment', ['params' => $params]);

        // Validate required parameters
        $requiredParams = ['return_url', 'website_url', 'amount', 'purchase_order_id', 'purchase_order_name'];
        $this->validateParams($params, $requiredParams);

        // Validate customer info if provided
        if (isset($params['customer_info'])) {
            $requiredCustomerParams = ['name', 'email', 'phone'];
            $this->validateParams($params['customer_info'], $requiredCustomerParams, 'customer_info');
        }

        // Make API request
        $response = $this->httpClient->post('/epayment/initiate/', $params);
        
        $this->logger->info('E-payment initiated successfully', ['response' => $response]);
        
        return $response;
    }

    /**
     * Verify a payment
     * 
     * @param string $pidx Payment ID
     * @return array Payment verification response
     * @throws ValidationException If parameters are invalid
     * @throws ApiException If API request fails
     */
    public function verify($pidx)
    {
        if (empty($pidx)) {
            throw new ValidationException('Payment ID (pidx) is required');
        }

        $this->logger->info('Verifying e-payment', ['pidx' => $pidx]);

        // Make API request
        $response = $this->httpClient->post('/epayment/lookup/', ['pidx' => $pidx]);
        
        $this->logger->info('E-payment verified successfully', ['response' => $response]);
        
        return $response;
    }

    /**
     * Check payment status
     * 
     * @param string $pidx Payment ID
     * @return array Payment status response
     * @throws ValidationException If parameters are invalid
     * @throws ApiException If API request fails
     */
    public function status($pidx)
    {
        if (empty($pidx)) {
            throw new ValidationException('Payment ID (pidx) is required');
        }

        $this->logger->info('Checking e-payment status', ['pidx' => $pidx]);

        // Make API request
        $response = $this->httpClient->post('/epayment/status/', ['pidx' => $pidx]);
        
        $this->logger->info('E-payment status checked successfully', ['response' => $response]);
        
        return $response;
    }

    /**
     * Validate parameters
     * 
     * @param array $params Parameters to validate
     * @param array $required Required parameters
     * @param string $prefix Parameter prefix for error messages
     * @throws ValidationException If parameters are invalid
     */
    private function validateParams(array $params, array $required, $prefix = '')
    {
        $prefix = !empty($prefix) ? $prefix . '.' : '';
        
        foreach ($required as $param) {
            if (!isset($params[$param]) || empty($params[$param])) {
                throw new ValidationException("{$prefix}{$param} is required");
            }
        }
    }
}

