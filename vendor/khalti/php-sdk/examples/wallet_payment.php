<?php
require_once __DIR__ . '/../vendor/autoload.php';

use KhaltiSDK\Khalti;
use KhaltiSDK\Exceptions\ApiException;
use KhaltiSDK\Exceptions\ValidationException;

try {
    // Initialize Khalti SDK with configuration
    $khalti = new Khalti([
        'environment' => 'sandbox',
        'secretKey' => 'test_secret_key_f59e8b7d18b4499ca40f68195a846e9b',
        'publicKey' => 'test_public_key_dc74e0fd57cb46cd93832aee0a390234',
        'enableLogging' => true
    ]);

    // Step 1: Initiate payment (this would typically be triggered by a client-side request)
    // In a real application, the token would be sent from the client after the user completes the payment
    $token = 'test_token_123456789';
    $amount = 1000; // Amount in paisa (10 NPR)

    // Step 2: Verify payment
    $response = $khalti->wallet()->verify($token, $amount);

    // Process verification response
    echo "Payment verification successful!\n";
    echo "Transaction ID: " . ($response['idx'] ?? 'N/A') . "\n";
    echo "Amount: " . (($response['amount'] ?? 0) / 100) . " NPR\n";
    echo "Mobile: " . ($response['mobile'] ?? 'N/A') . "\n";
    
    // In a real application, you would update your database with the payment status
    
} catch (ValidationException $e) {
    echo "Validation Error: " . $e->getMessage() . "\n";
} catch (ApiException $e) {
    echo "API Error (" . $e->getCode() . "): " . $e->getMessage() . "\n";
    if ($responseData = $e->getResponseData()) {
        echo "Response Data: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

