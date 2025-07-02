### Khalti PHP SDK
A simple and easy PHP SDK for integrating Khalti Payment Gateway into your PHP applications. Developed by www.prasangapokharel.com.np

## 🚀 Installation

### Via Composer

```shellscript
composer require khalti/php-sdk
```
## 🧪 Test Credentials

Use these credentials for testing in the sandbox environment:

| Credential | Value
|-----|-----
| Test Phone Number | `9800000001`
| Test MPIN | `1111`
| Test OTP | `987654`
| Secret Key | `live_secret_key_68791341fdd94846a146f0457ff7b455`
| Public Key | `test_public_key_dc74e0fd57cb46cd93832aee0a390234`


## ⚙️ Configuration

You can configure the SDK in two ways:

### 1. Using an array

```php
use KhaltiSDK\Khalti;

$khalti = new Khalti([
    'environment' => 'sandbox', // or 'live'
    'secretKey' => 'your_secret_key',
    'publicKey' => 'your_public_key',
    'enableLogging' => true,
    'logPath' => '/path/to/logs/khalti.log',
    'logLevel' => 'info'
]);
```

### 2. Using a configuration file

```php
use KhaltiSDK\Khalti;

$khalti = new Khalti('/path/to/config/khalti.php');
```

Example configuration file:

```php
return [
    'environment' => 'sandbox',
    'secretKey' => 'your_secret_key',
    'publicKey' => 'your_public_key',
    'enableLogging' => true,
    'logPath' => __DIR__ . '/../logs/khalti.log',
    'logLevel' => 'info'
];
```

## 🔍 Usage

### E-Payment API

#### Initiating a Payment

```php
try {
    $params = [
        'return_url' => 'https://example.com/khalti/verify',
        'website_url' => 'https://example.com',
        'amount' => 1000, // Amount in paisa (10 NPR)
        'purchase_order_id' => 'ORDER-123',
        'purchase_order_name' => 'Test Order',
        'customer_info' => [
            'name' => 'Prasanga Raman Pokharel',
            'email' => 'prasanga@gmail.com',
            'phone' => '9800000000'
        ]
    ];

    $response = $khalti->ePayment()->initiate($params);
    
    // Redirect user to payment URL
    header('Location: ' . $response['payment_url']);
    exit;
} catch (Exception $e) {
    // Handle error
    echo $e->getMessage();
}
```

#### Verifying a Payment

```php
try {
    // Get pidx from return URL query parameter
    $pidx = $_GET['pidx'];
    
    $response = $khalti->ePayment()->verify($pidx);
    
    // Process verification response
    if ($response['status'] === 'Completed') {
        // Payment successful, update your database
        // ...
        
        // Redirect to success page
        header('Location: /payment/success');
        exit;
    } else {
        // Payment failed or pending
        // ...
        
        // Redirect to failure page
        header('Location: /payment/failure');
        exit;
    }
} catch (Exception $e) {
    // Handle error
    echo $e->getMessage();
}
```

### Wallet API

#### Verifying a Wallet Payment

```php
try {
    // Token received from client-side Khalti widget
    $token = $_POST['token'];
    $amount = $_POST['amount']; // Amount in paisa
    
    $response = $khalti->wallet()->verify($token, $amount);
    
    // Payment successful, update your database
    // ...
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Handle error
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
```

### Transaction API

#### Listing Transactions

```php
try {
    $transactions = $khalti->transaction()->list([
        'page' => 1,
        'page_size' => 10
    ]);
    
    foreach ($transactions['records'] as $transaction) {
        echo $transaction['idx'] . ': ' . $transaction['amount'] / 100 . ' NPR<br>';
    }
} catch (Exception $e) {
    // Handle error
    echo $e->getMessage();
}
```

#### Getting Transaction Details

```php
try {
    $transaction = $khalti->transaction()->find('transaction_idx');
    
    echo 'Amount: ' . $transaction['amount'] / 100 . ' NPR<br>';
    echo 'Status: ' . $transaction['status'] . '<br>';
    echo 'Date: ' . $transaction['created_on'] . '<br>';
} catch (Exception $e) {
    // Handle error
    echo $e->getMessage();
}
```

## 🛡️ Error Handling

The SDK throws different types of exceptions for different error scenarios:

- `ValidationException`: Thrown when request parameters are invalid
- `ApiException`: Thrown when the Khalti API returns an error
- `NetworkException`: Thrown when a network error occurs
- `ConfigurationException`: Thrown when the SDK configuration is invalid


Example error handling:

```php
use KhaltiSDK\Exceptions\ValidationException;
use KhaltiSDK\Exceptions\ApiException;
use KhaltiSDK\Exceptions\NetworkException;
use KhaltiSDK\Exceptions\ConfigurationException;

try {
    $response = $khalti->ePayment()->initiate($params);
    // Process response
} catch (ValidationException $e) {
    // Handle validation error
    echo "Validation Error: " . $e->getMessage();
} catch (ApiException $e) {
    // Handle API error
    echo "API Error (" . $e->getCode() . "): " . $e->getMessage();
    $responseData = $e->getResponseData(); // Get full response data
} catch (NetworkException $e) {
    // Handle network error
    echo "Network Error: " . $e->getMessage();
} catch (ConfigurationException $e) {
    // Handle configuration error
    echo "Configuration Error: " . $e->getMessage();
} catch (Exception $e) {
    // Handle other errors
    echo "Error: " . $e->getMessage();
}
```

## 📝 Logging

The SDK provides built-in logging functionality. You can enable it by setting `enableLogging` to `true` in the configuration.

Log levels:

- `debug`: Detailed debug information
- `info`: Interesting events
- `warning`: Exceptional occurrences that are not errors
- `error`: Runtime errors that do not require immediate action
- `critical`: Critical conditions




## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

#### Demo Code

```php
<?php
// Include Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

use KhaltiSDK\Khalti;
use KhaltiSDK\Exceptions\ValidationException;
use KhaltiSDK\Exceptions\ApiException;
use KhaltiSDK\Exceptions\NetworkException;
use KhaltiSDK\Exceptions\ConfigurationException;
// Initialize Khalti SDK
$khalti = new Khalti([
    'environment' => 'sandbox', // Use 'live' for production
    'secretKey' => 'live_secret_key_68791341fdd94846a146f0457ff7b455',
    'publicKey' => 'test_public_key_dc74e0fd57cb46cd93832aee0a390234', // Replace with your public key
    'enableLogging' => true,
    'logPath' => __DIR__ . '/khalti.log'
]);

// Process form submission
$message = '';
$paymentUrl = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['initiate_payment'])) {
    try {
        // Prepare payment parameters
        $params = [
            'return_url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/test.php?verify=true',
            'website_url' => 'http://' . $_SERVER['HTTP_HOST'],
            'amount' => (int)($_POST['amount'] * 100), // Convert to paisa
            'purchase_order_id' => 'ORDER-' . time(),
            'purchase_order_name' => $_POST['product_name'],
            'customer_info' => [
                'name' => $_POST['customer_name'],
                'email' => $_POST['customer_email'],
                'phone' => $_POST['customer_phone']
            ]
        ];

        // Initiate payment
        $response = $khalti->ePayment()->initiate($params);
        
        // Store payment details in session for verification
        session_start();
        $_SESSION['khalti_payment'] = [
            'purchase_order_id' => $params['purchase_order_id'],
            'amount' => $params['amount']
        ];
        
        // Redirect to payment URL
        if (isset($response['payment_url'])) {
            header('Location: ' . $response['payment_url']);
            exit;
        } else {
            $error = "Payment initiation successful but no payment URL returned.";
        }
    } catch (ValidationException $e) {
        $error = "Validation Error: " . $e->getMessage();
    } catch (ApiException $e) {
        $error = "API Error (" . $e->getCode() . "): " . $e->getMessage();
    } catch (NetworkException $e) {
        $error = "Network Error: " . $e->getMessage();
    } catch (ConfigurationException $e) {
        $error = "Configuration Error: " . $e->getMessage();
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Verify payment
if (isset($_GET['verify']) && $_GET['verify'] === 'true' && isset($_GET['pidx'])) {
    try {
        $pidx = $_GET['pidx'];
        
        // Verify payment
        $response = $khalti->ePayment()->verify($pidx);
        
        // Process verification response
        if (isset($response['status']) && $response['status'] === 'Completed') {
            $message = "Payment successful! Transaction ID: " . ($response['transaction_id'] ?? 'N/A');
        } else {
            $error = "Payment verification failed. Status: " . ($response['status'] ?? 'Unknown');
        }
    } catch (Exception $e) {
        $error = "Verification Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khalti PHP SDK Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 2rem;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #5C2D91;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-primary {
            background-color: #5C2D91;
            border-color: #5C2D91;
        }
        .btn-primary:hover {
            background-color: #4A2275;
            border-color: #4A2275;
        }
        .test-credentials {
            background-color: #f0f0f0;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
        }
        .test-credentials code {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="mb-0">Khalti PHP SDK Test</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-success"><?php echo $message; ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <div class="test-credentials">
                            <h5>Test Credentials</h5>
                            <code>Phone Number: 9800000001</code>
                            <code>MPIN: 1111</code>
                            <code>OTP: 987654</code>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="product_name" name="product_name" value="Test Product" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (NPR)</label>
                                <input type="number" class="form-control" id="amount" name="amount" value="10" min="10" step="1" required>
                                <small class="text-muted">Minimum amount: 10 NPR</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" value="John Doe" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="customer_email" class="form-label">Customer Email</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email" value="john@example.com" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="customer_phone" class="form-label">Customer Phone</label>
                                <input type="text" class="form-control" id="customer_phone" name="customer_phone" value="9800000001" required>
                            </div>
                            
                            <button type="submit" name="initiate_payment" class="btn btn-primary">Pay with Khalti</button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">SDK Information</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>Environment:</strong> Sandbox</p>
                        <p><strong>Return URL:</strong> <?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/test.php?verify=true'; ?></p>
                        <p><strong>Log Path:</strong> <?php echo __DIR__ . '/khalti.log'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```
## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 📞 Support

If you encounter any issues or have questions, please create an issue on GitHub or contact [prasangaramanpokharel@gmail.com](mailto:prasangaramanpokharel@gmail.com).
