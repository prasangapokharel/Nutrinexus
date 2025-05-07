<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class KhaltiPayment extends Model
{
    protected $table = 'khalti_payments';
    protected $primaryKey = 'id';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getByOrderId($orderId)
    {
        return $this->findOneBy('order_id', $orderId);
    }
    
    public function updateByOrderId($orderId, $data)
    {
        $payment = $this->getByOrderId($orderId);
        return $payment ? $this->update($payment['id'], $data) : false;
    }
    
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
        
        // Initialize cURL
        $ch = curl_init();
        
        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Key ' . $secret_key,
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ]);
        
        // Execute cURL request
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        // Close cURL
        curl_close($ch);
        
        // Log the response for debugging
        error_log("Khalti Verification Response: " . $response);
        
        if ($curl_error) {
            error_log("Khalti cURL Error: " . $curl_error);
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $curl_error
            ];
        }
        
        $response_data = json_decode($response, true);
        
        if ($status_code == 200) {
            return [
                'success' => true,
                'data' => $response_data,
                'transaction_id' => $response_data['idx'] ?? null
            ];
        }
        
        return [
            'success' => false,
            'data' => $response_data,
            'message' => $response_data['detail'] ?? 'Payment verification failed'
        ];
    }

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
        
        // Initialize cURL
        $ch = curl_init();
        
        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Key ' . $secret_key,
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ]);
        
        // Execute cURL request
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        // Close cURL
        curl_close($ch);
        
        // Log the response for debugging
        error_log("Khalti Initiation Response: " . $response);
        
        if ($curl_error) {
            error_log("Khalti cURL Error: " . $curl_error);
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $curl_error
            ];
        }
        
        $response_data = json_decode($response, true);
        
        if ($status_code == 200 && isset($response_data['payment_url'])) {
            return [
                'success' => true,
                'payment_url' => $response_data['payment_url'],
                'pidx' => $response_data['pidx'] ?? null
            ];
        }
        
        // Log the error for debugging
        error_log('Khalti payment initiation failed: ' . json_encode($response_data));
        
        return [
            'success' => false,
            'message' => $response_data['detail'] ?? 'Payment initiation failed',
            'data' => $response_data,
            'status_code' => $status_code
        ];
    }

    public function lookupPayment($pidx)
    {
        // Khalti API endpoint for looking up payment
        $url = KHALTI_LOOKUP_URL;
        
        // Your Khalti secret key
        $secret_key = KHALTI_SECRET_KEY;
        
        // Initialize cURL
        $ch = curl_init();
        
        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['pidx' => $pidx]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Key ' . $secret_key,
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ]);
        
        // Execute cURL request
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        // Close cURL
        curl_close($ch);
        
        // Log the verification response
        error_log("Khalti Lookup Response: " . $response);
        
        if ($curl_error) {
            error_log("Khalti cURL Error: " . $curl_error);
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $curl_error
            ];
        }
        
        $response_data = json_decode($response, true);
        
        if ($status_code == 200) {
            return [
                'success' => true,
                'status' => $response_data['status'] ?? 'Unknown',
                'data' => $response_data,
                'transaction_id' => $response_data['transaction_id'] ?? null
            ];
        }
        
        return [
            'success' => false,
            'message' => $response_data['detail'] ?? 'Payment verification failed',
            'data' => $response_data
        ];
    }
}
