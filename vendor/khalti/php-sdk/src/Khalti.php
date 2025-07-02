<?php
namespace KhaltiSDK;

use KhaltiSDK\Exceptions\ConfigurationException;
use KhaltiSDK\Services\EPaymentService;
use KhaltiSDK\Services\WalletService;
use KhaltiSDK\Services\TransactionService;
use KhaltiSDK\Config\Configuration;

/**
 * Main Khalti SDK class
 * 
 * This is the main entry point for the Khalti SDK. It provides access to all
 * the services offered by the SDK.
 * 
 * @package KhaltiSDK
 */
class Khalti
{
    /**
     * @var Configuration SDK configuration
     */
    private $config;

    /**
     * @var EPaymentService E-Payment service instance
     */
    private $ePaymentService;

    /**
     * @var WalletService Wallet service instance
     */
    private $walletService;

    /**
     * @var TransactionService Transaction service instance
     */
    private $transactionService;

    /**
     * Create a new Khalti SDK instance
     * 
     * @param string|array $config Configuration array or path to config file
     * @throws ConfigurationException If configuration is invalid
     */
    public function __construct($config)
    {
        if (is_string($config)) {
            // Load configuration from file
            if (!file_exists($config)) {
                throw new ConfigurationException("Configuration file not found: {$config}");
            }
            $config = require $config;
        }

        $this->config = new Configuration($config);
        $this->ePaymentService = new EPaymentService($this->config);
        $this->walletService = new WalletService($this->config);
        $this->transactionService = new TransactionService($this->config);
    }

    /**
     * Get the E-Payment service
     * 
     * @return EPaymentService
     */
    public function ePayment()
    {
        return $this->ePaymentService;
    }

    /**
     * Get the Wallet service
     * 
     * @return WalletService
     */
    public function wallet()
    {
        return $this->walletService;
    }

    /**
     * Get the Transaction service
     * 
     * @return TransactionService
     */
    public function transaction()
    {
        return $this->transactionService;
    }

    /**
     * Get the SDK configuration
     * 
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the SDK environment
     * 
     * @param string $environment 'live' or 'sandbox'
     * @return self
     */
    public function setEnvironment($environment)
    {
        $this->config->setEnvironment($environment);
        return $this;
    }

    /**
     * Set the merchant secret key
     * 
     * @param string $secretKey Merchant secret key
     * @return self
     */
    public function setSecretKey($secretKey)
    {
        $this->config->setSecretKey($secretKey);
        return $this;
    }

    /**
     * Set the merchant public key
     * 
     * @param string $publicKey Merchant public key
     * @return self
     */
    public function setPublicKey($publicKey)
    {
        $this->config->setPublicKey($publicKey);
        return $this;
    }
}

