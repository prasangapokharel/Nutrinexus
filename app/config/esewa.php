<?php
/**
 * eSewa Payment Gateway Configuration
 */

return [
    // Test credentials
    'merchant_id' => 'EPAYTEST',
    'client_id' => 'JB0BBQ4aD0UqIThFJwAKBgAXEUkEGQUBBAwdOgABHD4DChwUAB0R',
    'client_secret' => 'BhwIWQQADhIYSxILExMcAgFXFhcOBwAKBgAXEQ==',
    'secret_key' => '8gBm/:&EnhH.1/q',
    
    // API Endpoints (test environment)
    'payment_url' => 'https://rc-epay.esewa.com.np/api/epay/main/v2/form',
    'verification_url' => 'https://rc-epay.esewa.com.np/api/epay/main/v1/transactions',
    'oauth_url' => 'https://rc-epay.esewa.com.np/api/epay/main/v1/oauth/token',
    
    // Callback URLs
    'success_url' => 'checkout/esewaSuccess',
    'failure_url' => 'checkout/esewaFailure',
    
    // Test user credentials (for sandbox testing)
    'test_user' => [
        'id' => '9806800001', // Can use 9806800001 to 9806800005
        'password' => 'Nepal@123',
        'mpin' => '1122' // For mobile app testing
    ]
];