<?php
namespace KhaltiSDK\Exceptions;

/**
 * Exception thrown when an API error occurs
 * 
 * @package KhaltiSDK\Exceptions
 */
class ApiException extends \Exception
{
    /**
     * @var array|null Response data
     */
    private $responseData;

    /**
     * Create a new ApiException instance
     * 
     * @param string $message Exception message
     * @param int $code Exception code
     * @param \Throwable|null $previous Previous exception
     * @param array|null $responseData Response data
     */
    public function __construct($message, $code = 0, \Throwable $previous = null, array $responseData = null)
    {
        parent::__construct($message, $code, $previous);
        $this->responseData = $responseData;
    }

    /**
     * Get the response data
     * 
     * @return array|null
     */
    public function getResponseData()
    {
        return $this->responseData;
    }
}

