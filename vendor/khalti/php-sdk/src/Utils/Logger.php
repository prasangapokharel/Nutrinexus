<?php
namespace KhaltiSDK\Utils;

use KhaltiSDK\Config\Configuration;

/**
 * Logger for Khalti SDK
 * 
 * @package KhaltiSDK\Utils
 */
class Logger
{
    /**
     * @var Configuration SDK configuration
     */
    private $config;

    /**
     * @var array Log levels
     */
    private $levels = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4
    ];

    /**
     * Create a new Logger instance
     * 
     * @param Configuration $config SDK configuration
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * Log a debug message
     * 
     * @param string $message Log message
     * @param array $context Log context
     */
    public function debug($message, array $context = [])
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Log an info message
     * 
     * @param string $message Log message
     * @param array $context Log context
     */
    public function info($message, array $context = [])
    {
        $this->log('info', $message, $context);
    }

    /**
     * Log a warning message
     * 
     * @param string $message Log message
     * @param array $context Log context
     */
    public function warning($message, array $context = [])
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Log an error message
     * 
     * @param string $message Log message
     * @param array $context Log context
     */
    public function error($message, array $context = [])
    {
        $this->log('error', $message, $context);
    }

    /**
     * Log a critical message
     * 
     * @param string $message Log message
     * @param array $context Log context
     */
    public function critical($message, array $context = [])
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Log a message
     * 
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Log context
     */
    private function log($level, $message, array $context = [])
    {
        if (!$this->config->isLoggingEnabled()) {
            return;
        }

        if (!isset($this->levels[$level]) || $this->levels[$level] < $this->levels[$this->config->getLogLevel()]) {
            return;
        }

        $logPath = $this->config->getLogPath();
        $logDir = dirname($logPath);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        file_put_contents($logPath, $logMessage, FILE_APPEND);
    }
}

