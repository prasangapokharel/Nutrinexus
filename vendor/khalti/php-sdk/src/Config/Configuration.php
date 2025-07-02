<?php
namespace KhaltiSDK\Config;

use KhaltiSDK\Exceptions\ConfigurationException;

/**
 * Configuration class for Khalti SDK
 * 
 * @package KhaltiSDK\Config
 */
class Configuration
{
    /**
     * @var string Sandbox environment
     */
    const ENV_SANDBOX = 'sandbox';

    /**
     * @var string Live environment
     */
    const ENV_LIVE = 'live';

    /**
     * @var string Default environment
     */
    const DEFAULT_ENV = self::ENV_SANDBOX;

    /**
     * @var string Sandbox API base URL
     */
    const SANDBOX_BASE_URL = 'https://dev.khalti.com/api/v2';

    /**
     * @var string Live API base URL
     */
    const LIVE_BASE_URL = 'https://khalti.com/api/v2';

    /**
     * @var string Current environment
     */
    private $environment;

    /**
     * @var string Merchant secret key
     */
    private $secretKey;

    /**
     * @var string Merchant public key
     */
    private $publicKey;

    /**
     * @var int Request timeout in seconds
     */
    private $timeout;

    /**
     * @var bool Whether to enable logging
     */
    private $enableLogging;

    /**
     * @var string Log file path
     */
    private $logPath;

    /**
     * @var string Log level
     */
    private $logLevel;

    /**
     * Create a new Configuration instance
     * 
     * @param array $config Configuration array
     * @throws ConfigurationException If configuration is invalid
     */
    public function __construct(array $config)
    {
        // Set default values
        $this->environment = self::DEFAULT_ENV;
        $this->timeout = 30;
        $this->enableLogging = false;
        $this->logPath = __DIR__ . '/../../logs/khalti.log';
        $this->logLevel = 'info';

        // Override with provided values
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        // Validate required configuration
        if (empty($this->secretKey)) {
            throw new ConfigurationException('Secret key is required');
        }
    }

    /**
     * Get the API base URL based on the current environment
     * 
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->environment === self::ENV_LIVE ? self::LIVE_BASE_URL : self::SANDBOX_BASE_URL;
    }

    /**
     * Get the current environment
     * 
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Set the environment
     * 
     * @param string $environment 'live' or 'sandbox'
     * @return self
     * @throws ConfigurationException If environment is invalid
     */
    public function setEnvironment($environment)
    {
        if (!in_array($environment, [self::ENV_LIVE, self::ENV_SANDBOX])) {
            throw new ConfigurationException("Invalid environment: {$environment}. Must be 'live' or 'sandbox'");
        }
        $this->environment = $environment;
        return $this;
    }

    /**
     * Get the merchant secret key
     * 
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Set the merchant secret key
     * 
     * @param string $secretKey Merchant secret key
     * @return self
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
        return $this;
    }

    /**
     * Get the merchant public key
     * 
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Set the merchant public key
     * 
     * @param string $publicKey Merchant public key
     * @return self
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
        return $this;
    }

    /**
     * Get the request timeout
     * 
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Set the request timeout
     * 
     * @param int $timeout Request timeout in seconds
     * @return self
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Check if logging is enabled
     * 
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return $this->enableLogging;
    }

    /**
     * Enable or disable logging
     * 
     * @param bool $enable Whether to enable logging
     * @return self
     */
    public function setLoggingEnabled($enable)
    {
        $this->enableLogging = $enable;
        return $this;
    }

    /**
     * Get the log file path
     * 
     * @return string
     */
    public function getLogPath()
    {
        return $this->logPath;
    }

    /**
     * Set the log file path
     * 
     * @param string $path Log file path
     * @return self
     */
    public function setLogPath($path)
    {
        $this->logPath = $path;
        return $this;
    }

    /**
     * Get the log level
     * 
     * @return string
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * Set the log level
     * 
     * @param string $level Log level
     * @return self
     */
    public function setLogLevel($level)
    {
        $this->logLevel = $level;
        return $this;
    }
}

