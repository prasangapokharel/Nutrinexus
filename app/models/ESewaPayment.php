<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class ESewaPayment extends Model
{
    protected $table = 'esewa_payments';
    protected $primaryKey = 'id';
    
    public function __construct()
    {
        parent::__construct();
        $this->createTableIfNotExists();
    }
    
    /**
     * Get eSewa configuration
     * 
     * @return array
     */
    public function getEsewaConfig()
    {
        $configFile = dirname(dirname(dirname(__FILE__))) . '/config/esewa.php';
        
        if (file_exists($configFile)) {
            return require $configFile;
        }
        
        // Default configuration if file doesn't exist
        return [
            'merchant_id' => 'EPAYTEST',
            'client_id' => 'JB0BBQ4aD0UqIThFJwAKBgAXEUkEGQUBBAwdOgABHD4DChwUAB0R',
            'client_secret' => 'BhwIWQQADhIYSxILExMcAgFXFhcOBwAKBgAXEQ==',
            'secret_key' => '8gBm/:&EnhH.1/q',
            'payment_url' => 'https://rc-epay.esewa.com.np/api/epay/main/v2/form',
            'verification_url' => 'https://rc-epay.esewa.com.np/api/epay/main/v1/transactions',
            'oauth_url' => 'https://rc-epay.esewa.com.np/api/epay/main/v1/oauth/token',
            'success_url' => 'checkout/esewaSuccess',
            'failure_url' => 'checkout/esewaFailure'
        ];
    }
    
    /**
     * Generate a callback URL with query parameters
     *
     * @param string $baseUrl
     * @param array $params
     * @return string
     */
    private function generateCallbackUrl($baseUrl, $params)
    {
        $queryString = http_build_query($params);
        return rtrim($baseUrl, '?') . '?' . $queryString;
    }
    
    /**
     * Create the esewa_payments table if it doesn't exist
     */
    private function createTableIfNotExists()
    {
        try {
            $db = Database::getInstance();
            $query = "CREATE TABLE IF NOT EXISTS `esewa_payments` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` int(11) NOT NULL,
                `user_id` int(11) NOT NULL,
                `amount` decimal(10,2) NOT NULL,
                `transaction_id` varchar(255) DEFAULT NULL,
                `reference_id` varchar(255) DEFAULT NULL,
                `status` varchar(50) NOT NULL DEFAULT 'pending',
                `response_data` text DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `order_id` (`order_id`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
            
            $db->query($query);
            
            // Log successful table creation
            error_log("esewa_payments table created or already exists");
        } catch (\Exception $e) {
            // Log the error
            error_log("Error creating esewa_payments table: " . $e->getMessage());
        }
    }
    
    /**
     * Get payment by order ID
     *
     * @param int $orderId
     * @return array|null
     */
    public function getByOrderId($orderId)
    {
        return $this->findOneBy('order_id', $orderId);
    }
    
    /**
     * Get payment by transaction ID
     *
     * @param string $transactionId
     * @return array|null
     */
    public function getByTransactionId($transactionId)
    {
        return $this->findOneBy('transaction_id', $transactionId);
    }
    
    /**
     * Update payment by order ID
     *
     * @param int $orderId
     * @param array $data
     * @return bool
     */
    public function updateByOrderId($orderId, $data)
    {
        $payment = $this->getByOrderId($orderId);
        return $payment ? $this->update($payment['id'], $data) : false;
    }
    
    /**
     * Create a new eSewa payment
     *
     * @param array $data
     * @return int|bool
     */
    public function createESewaPayment($data)
    {
        // Ensure the table exists before inserting
        $this->createTableIfNotExists();
        
        // Check if the table exists now
        try {
            $db = Database::getInstance();
            $checkQuery = "SHOW TABLES LIKE 'esewa_payments'";
            $result = $db->query($checkQuery);
            
            if ($result && $result->rowCount() === 0) {
                error_log("esewa_payments table still doesn't exist after creation attempt");
                return false;
            }
        } catch (\Exception $e) {
            error_log("Error checking esewa_payments table: " . $e->getMessage());
            return false;
        }
        
        return $this->create($data);
    }
    
    /**
     * Verify payment with eSewa API
     *
     * @param string $referenceId
     * @param string $transactionId
     * @param float $amount
     * @return array
     */
    public function verifyPayment($referenceId, $transactionId, $amount)
    {
        $config = $this->getEsewaConfig();
        
        // First get OAuth token
        $token = $this->getOAuthToken();
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Failed to get authentication token'
            ];
        }
        
        // Prepare verification request
        $verificationUrl = $config['verification_url'] . '/' . $referenceId;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $verificationUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            error_log("eSewa Verification cURL Error: " . $curl_error);
            return [
                'success' => false,
                'message' => 'Connection error: ' . $curl_error
            ];
        }
        
        $responseData = json_decode($response, true);
        
        if ($status_code == 200 && 
            isset($responseData['status']) && 
            $responseData['status'] === 'COMPLETE' &&
            $responseData['total_amount'] == $amount) {
            return [
                'success' => true,
                'data' => $responseData,
                'transaction_id' => $transactionId
            ];
        }
        
        return [
            'success' => false,
            'data' => $responseData,
            'message' => 'Payment verification failed: ' . 
                        ($responseData['message'] ?? 'Unknown error')
        ];
    }
    
    /**
     * Get OAuth token for eSewa API
     *
     * @return string|null
     */
    private function getOAuthToken()
    {
        $config = $this->getEsewaConfig();
        
        $auth = base64_encode($config['client_id'] . ':' . $config['client_secret']);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $config['oauth_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'client_credentials',
                'scope' => 'payment verification'
            ]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . $auth,
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ]);
        
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($status_code == 200) {
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        }
        
        error_log("Failed to get OAuth token. Status: $status_code, Response: $response");
        return null;
    }
    
    /**
     * Generate eSewa payment form data
     *
     * @param int $orderId
     * @param float $amount
     * @param string $productName
     * @return array
     */
    public function generatePaymentData($orderId, $amount, $productName)
    {
        $config = $this->getEsewaConfig();
        
        // Generate a unique transaction ID
        $transactionId = 'ORDER_' . $orderId . '_' . time();
        
        // Build the success and failure URLs
        $baseUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $baseUrl .= "://" . $_SERVER['HTTP_HOST'];
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && $scriptName !== '\\') {
            $baseUrl .= rtrim($scriptName, '/\\');
        }
        
        $successUrl = $baseUrl . '/' . $config['success_url'];
        $failureUrl = $baseUrl . '/' . $config['failure_url'];
        
        // Add order ID to the URLs
        $successUrl = $this->generateCallbackUrl($successUrl, ['oid' => $orderId, 'amt' => $amount]);
        $failureUrl = $this->generateCallbackUrl($failureUrl, ['oid' => $orderId]);
        
        return [
            'url' => $config['payment_url'],
            'merchant_id' => $config['merchant_id'],
            'amount' => $amount,
            'product_id' => $transactionId,
            'success_url' => $successUrl,
            'failure_url' => $failureUrl
        ];
    }
}