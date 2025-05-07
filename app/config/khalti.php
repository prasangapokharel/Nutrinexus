<?php
namespace App\Config;

/**
 * Khalti Payment Gateway Configuration
 */
class Khalti
{
    /**
     * Khalti API endpoints
     */
    private $apiEndpoints = [
        'live' => [
            'initiate' => 'https://khalti.com/api/v2/epayment/initiate/',
            'lookup' => 'https://khalti.com/api/v2/epayment/lookup/',
            'verify' => 'https://khalti.com/api/v2/payment/verify/'
        ],
        'test' => [
            'initiate' => 'https://a.khalti.com/api/v2/epayment/initiate/',
            'lookup' => 'https://a.khalti.com/api/v2/epayment/lookup/',
            'verify' => 'https://a.khalti.com/api/v2/payment/verify/'
        ]
    ];

    /**
     * Khalti API keys
     */
    private $apiKeys = [
        'live' => [
            'public_key' => 'live_public_key_from_khalti_dashboard',
            'secret_key' => 'live_secret_key_from_khalti_dashboard'
        ],
        'test' => [
            'public_key' => 'test_public_key_from_khalti_dashboard',
            'secret_key' => 'test_secret_key_from_khalti_dashboard'
        ]
    ];

    /**
     * Current environment (live or test)
     */
    private $environment = 'test'; // Change to 'live' for production

    /**
     * Constructor
     */
    public function __construct()
    {
        // Load environment-specific configuration if needed
        $this->loadEnvironmentConfig();
    }

    /**
     * Load environment-specific configuration
     */
    private function loadEnvironmentConfig()
    {
        // Check if environment variable is set
        if (defined('KHALTI_ENVIRONMENT')) {
            $this->environment = KHALTI_ENVIRONMENT;
        }

        // Load custom keys from environment variables if available
        if (defined('KHALTI_PUBLIC_KEY') && !empty(KHALTI_PUBLIC_KEY)) {
            $this->apiKeys[$this->environment]['public_key'] = KHALTI_PUBLIC_KEY;
        }

        if (defined('KHALTI_SECRET_KEY') && !empty(KHALTI_SECRET_KEY)) {
            $this->apiKeys[$this->environment]['secret_key'] = KHALTI_SECRET_KEY;
        }
    }

    /**
     * Get current environment
     * 
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Set environment
     * 
     * @param string $environment
     * @return void
     */
    public function setEnvironment($environment)
    {
        if (in_array($environment, ['live', 'test'])) {
            $this->environment = $environment;
        }
    }

    /**
     * Get public key
     * 
     * @return string
     */
    public function getPublicKey()
    {
        return $this->apiKeys[$this->environment]['public_key'];
    }

    /**
     * Get secret key
     * 
     * @return string
     */
    public function getSecretKey()
    {
        return $this->apiKeys[$this->environment]['secret_key'];
    }

    /**
     * Get initiate payment endpoint
     * 
     * @return string
     */
    public function getInitiateEndpoint()
    {
        return $this->apiEndpoints[$this->environment]['initiate'];
    }

    /**
     * Get lookup payment endpoint
     * 
     * @return string
     */
    public function getLookupEndpoint()
    {
        return $this->apiEndpoints[$this->environment]['lookup'];
    }

    /**
     * Get verify payment endpoint
     * 
     * @return string
     */
    public function getVerifyEndpoint()
    {
        return $this->apiEndpoints[$this->environment]['verify'];
    }
}
