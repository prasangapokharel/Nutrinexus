<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use Spatie\Async\Pool;

class KhaltiPayment extends Model
{
    protected $table = 'khalti_payments';
    protected $primaryKey = 'id';
    private $asyncPool;
    
    public function __construct()
    {
        parent::__construct();
        
        // Initialize Spatie Async Pool if available
        if (class_exists('\\Spatie\\Async\\Pool')) {
            try {
                $this->asyncPool = Pool::create();
            } catch (\Exception $e) {
                error_log('Failed to create async pool in KhaltiPayment: ' . $e->getMessage());
                $this->asyncPool = null;
            }
        } else {
            $this->asyncPool = null;
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
        $sql = "SELECT * FROM {$this->table} WHERE order_id = ? ORDER BY created_at DESC LIMIT 1";
        return $this->db->query($sql)->bind([$orderId])->single();
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
        $sql = "UPDATE {$this->table} SET ";
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $sql .= implode(', ', $fields) . " WHERE order_id = ?";
        $params[] = $orderId;
        
        return $this->db->query($sql)->bind($params)->execute();
    }
    
    /**
     * Verify payment with Khalti
     *
     * @param string $token
     * @param int $amount
     * @return array
     */
    public function verifyPayment($token, $amount)
    {
        // Khalti API endpoint for verifying payment
        $url = KHALTI_VERIFY_URL;
        
        // Your Khalti secret key
        $secret_key = KHALTI_SECRET_KEY;
        
        // Prepare the request data
        $data = [
            'token' => $token,
            'amount' => $amount
        ];
        
        // Log the request data for debugging
        error_log("Khalti Verification Request: " . json_encode($data));
        
        // If async is available, try both methods in parallel
        if ($this->asyncPool) {
            try {
                $curlPromise = $this->asyncPool->add(function() use ($url, $secret_key, $data) {
                    return $this->makeApiRequestCurl($url, 'POST', $data, [
                        'Authorization: Key ' . $secret_key,
                        'Content-Type: application/json'
                    ]);
                });
                
                // Wait for async task to complete
                $this->asyncPool->wait();
                
                // Get result
                $response = $curlPromise->then(function($result) {
                    return $result;
                })->catch(function(\Exception $e) use ($url, $secret_key, $data) {
                    error_log('Error in async Khalti verification: ' . $e->getMessage());
                    // Fall back to synchronous request
                    return $this->makeApiRequestCurl($url, 'POST', $data, [
                        'Authorization: Key ' . $secret_key,
                        'Content-Type: application/json'
                    ]);
                });
            } catch (\Exception $e) {
                error_log('Async processing error in Khalti verification: ' . $e->getMessage());
                // Fall back to synchronous request
                $response = $this->makeApiRequestCurl($url, 'POST', $data, [
                    'Authorization: Key ' . $secret_key,
                    'Content-Type: application/json'
                ]);
            }
        } else {
            // Standard approach without async
            $response = $this->makeApiRequestCurl($url, 'POST', $data, [
                'Authorization: Key ' . $secret_key,
                'Content-Type: application/json'
            ]);
        }
        
        // Log the response for debugging
        error_log("Khalti Verification Response: " . json_encode($response));
        
        if (isset($response['error'])) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $response['error'],
                'data' => $response
            ];
        }
        
        if (isset($response['idx'])) {
            return [
                'success' => true,
                'transaction_id' => $response['idx'],
                'data' => $response
            ];
        }
        
        return [
            'success' => false,
            'message' => $response['detail'] ?? 'Payment verification failed',
            'data' => $response
        ];
    }

    /**
     * Initiate payment with Khalti
     *
     * @param string $returnUrl
     * @param string $websiteUrl
     * @param int $amount
     * @param string $purchaseOrderId
     * @param string $purchaseOrderName
     * @param array $customerInfo
     * @return array
     */
    public function initiatePayment($returnUrl, $websiteUrl, $amount, $purchaseOrderId, $purchaseOrderName, $customerInfo)
    {
        // Khalti API endpoint for initiating payment
        $url = KHALTI_INITIATE_URL;
        
        // Your Khalti secret key
        $secret_key = KHALTI_SECRET_KEY;
        
        // Prepare the request data
        $data = [
            "return_url" => $returnUrl,
            "website_url" => $websiteUrl,
            "amount" => (int)$amount,
            "purchase_order_id" => $purchaseOrderId,
            "purchase_order_name" => $purchaseOrderName,
            "customer_info" => $customerInfo
        ];
        
        // Log the request data for debugging
        error_log("Khalti Initiation Request: " . json_encode($data));
        
        // If async is available, try both methods in parallel
        if ($this->asyncPool) {
            try {
                $curlPromise = $this->asyncPool->add(function() use ($url, $secret_key, $data) {
                    return $this->makeApiRequestCurl($url, 'POST', $data, [
                        'Authorization: Key ' . $secret_key,
                        'Content-Type: application/json'
                    ]);
                });
                
                // Wait for async task to complete
                $this->asyncPool->wait();
                
                // Get result
                $response = $curlPromise->then(function($result) {
                    return $result;
                })->catch(function(\Exception $e) use ($url, $secret_key, $data) {
                    error_log('Error in async Khalti initiation: ' . $e->getMessage());
                    // Fall back to synchronous request
                    return $this->makeApiRequestCurl($url, 'POST', $data, [
                        'Authorization: Key ' . $secret_key,
                        'Content-Type: application/json'
                    ]);
                });
            } catch (\Exception $e) {
                error_log('Async processing error in Khalti initiation: ' . $e->getMessage());
                // Fall back to synchronous request
                $response = $this->makeApiRequestCurl($url, 'POST', $data, [
                    'Authorization: Key ' . $secret_key,
                    'Content-Type: application/json'
                ]);
            }
        } else {
            // Standard approach without async
            $response = $this->makeApiRequestCurl($url, 'POST', $data, [
                'Authorization: Key ' . $secret_key,
                'Content-Type: application/json'
            ]);
        }
        
        // Log the response for debugging
        error_log("Khalti Initiation Response: " . json_encode($response));
        
        if (isset($response['error'])) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $response['error'],
                'data' => $response
            ];
        }
        
        if (isset($response['payment_url'])) {
            return [
                'success' => true,
                'payment_url' => $response['payment_url'],
                'pidx' => $response['pidx'] ?? null
            ];
        }
        
        return [
            'success' => false,
            'message' => $response['detail'] ?? 'Payment initiation failed',
            'data' => $response,
            'status_code' => $response['status_code'] ?? 'Unknown'
        ];
    }

    /**
     * Lookup payment status with Khalti
     *
     * @param string $pidx
     * @return array
     */
    public function lookupPayment($pidx)
    {
        // Khalti API endpoint for looking up payment
        $url = KHALTI_LOOKUP_URL . $pidx . '/';
        
        // Your Khalti secret key
        $secret_key = KHALTI_SECRET_KEY;
        
        // Log the request for debugging
        error_log("Khalti Lookup Request for PIDX: " . $pidx);
        
        // If async is available, try both methods in parallel
        if ($this->asyncPool) {
            try {
                $curlPromise = $this->asyncPool->add(function() use ($url, $secret_key) {
                    return $this->makeApiRequestCurl($url, 'GET', [], [
                        'Authorization: Key ' . $secret_key,
                        'Content-Type: application/json'
                    ]);
                });
                
                // Wait for async task to complete
                $this->asyncPool->wait();
                
                // Get result
                $response = $curlPromise->then(function($result) {
                    return $result;
                })->catch(function(\Exception $e) use ($url, $secret_key) {
                    error_log('Error in async Khalti lookup: ' . $e->getMessage());
                    // Fall back to synchronous request
                    return $this->makeApiRequestCurl($url, 'GET', [], [
                        'Authorization: Key ' . $secret_key,
                        'Content-Type: application/json'
                    ]);
                });
            } catch (\Exception $e) {
                error_log('Async processing error in Khalti lookup: ' . $e->getMessage());
                // Fall back to synchronous request
                $response = $this->makeApiRequestCurl($url, 'GET', [], [
                    'Authorization: Key ' . $secret_key,
                    'Content-Type: application/json'
                ]);
            }
        } else {
            // Standard approach without async
            $response = $this->makeApiRequestCurl($url, 'GET', [], [
                'Authorization: Key ' . $secret_key,
                'Content-Type: application/json'
            ]);
        }
        
        // Log the response for debugging
        error_log("Khalti Lookup Response: " . json_encode($response));
        
        if (isset($response['error'])) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $response['error'],
                'data' => $response
            ];
        }
        
        if (isset($response['status'])) {
            return [
                'success' => true,
                'status' => $response['status'],
                'transaction_id' => $response['transaction_id'] ?? null,
                'data' => $response
            ];
        }
        
        return [
            'success' => false,
            'message' => $response['detail'] ?? 'Failed to lookup payment',
            'data' => $response
        ];
    }

    /**
     * Make API request to Khalti using cURL
     *
     * @param string $url
     * @param string $method
     * @param array $data
     * @param array $headers
     * @return array
     */
    private function makeApiRequestCurl($url, $method = 'GET', $data = [], $headers = [])
    {
        $curl = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ];
        
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        
        curl_setopt_array($curl, $options);
        
        $response = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            error_log("Khalti cURL Error: " . $err);
            return ['error' => $err, 'status_code' => $status_code];
        }
        
        $decoded = json_decode($response, true);
        
        if (!$decoded) {
            error_log("Khalti Invalid JSON Response: " . $response);
            return ['error' => 'Invalid response', 'raw_response' => $response, 'status_code' => $status_code];
        }
        
        return $decoded;
    }
}
