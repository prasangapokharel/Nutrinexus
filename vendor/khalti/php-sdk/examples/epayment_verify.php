<?php
require_once __DIR__ . '/../vendor/autoload.php';

use KhaltiSDK\Khalti;
use KhaltiSDK\Exceptions\ApiException;
use KhaltiSDK\Exceptions\ValidationException;

// Simulate receiving a pidx from Khalti after payment
$pidx = $_GET['pidx'] ?? 'test_pidx_123456789';

try {
    // Initialize Khalti SDK with configuration
    $khalti = new Khalti([
        'environment' => 'sandbox',
        'secretKey' => 'test_secret_key_f59e8b7d18b4499ca40f68195a846e9b',
        'enableLogging' => true
    ]);

    // Verify payment
    $response = $khalti->ePayment()->verify($pidx);

    // Process verification response
    echo "Payment verification successful!\n";
    echo "Status: " . ($response['status'] ?? 'Unknown') . "\n";
    echo "Transaction ID: " . ($response['transaction_id'] ?? 'N/A') . "\n";
    echo "Amount: " . (($response['amount'] ?? 0) / 100) . " NPR\n";
    
    // In a real application, you would update your database with the payment status
    // and redirect the user to a success page
    
} catch (ValidationException $e) {
    echo "Validation Error: " . $e->getMessage() . "\n";
} catch (ApiException $e) {
    echo "API Error (" . $e->getCode() . "): " . $e->getMessage() . "\n";
    if ($responseData = $e->getResponseData()) {
        echo "Response Data: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    }
    
    // In a real application, you would redirect the user to a failure page
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

