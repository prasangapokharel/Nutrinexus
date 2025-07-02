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

    // Prepare payment parameters
    $params = [
        'return_url' => 'https://example.com/khalti/verify',
        'website_url' => 'https://example.com',
        'amount' => 1000, // Amount in paisa (10 NPR)
        'purchase_order_id' => 'ORDER-' . time(),
        'purchase_order_name' => 'Test Order',
        'customer_info' => [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '9800000000'
        ]
    ];

    // Initiate payment
    $response = $khalti->ePayment()->initiate($params);

    // Redirect user to payment URL
    if (isset($response['payment_url'])) {
        echo "Payment initiated successfully. Redirecting to: " . $response['payment_url'] . "\n";
        // In a real application, you would redirect the user:
        // header('Location: ' . $response['payment_url']);
        // exit;
    } else {
        echo "Payment initiation successful but no payment URL returned.\n";
        print_r($response);
    }
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

