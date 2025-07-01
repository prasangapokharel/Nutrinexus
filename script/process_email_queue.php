<?php

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the application bootstrap
$bootstrapPaths = [
    dirname(__DIR__) . '/app/bootstrap.php',
    dirname(__DIR__) . '/App/bootstrap.php',
    __DIR__ . '/../app/bootstrap.php',
    __DIR__ . '/../App/bootstrap.php'
];

$bootstrapLoaded = false;
foreach ($bootstrapPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $bootstrapLoaded = true;
        echo "Bootstrap loaded from: $path\n";
        break;
    }
}

if (!$bootstrapLoaded) {
    echo "Error: Could not find bootstrap file\n";
    echo "Tried paths:\n";
    foreach ($bootstrapPaths as $path) {
        echo "- $path\n";
    }
    exit(1);
}

// Check if PHPMailer is available
use App\Helpers\EmailHelper;

try {
    echo "Checking PHPMailer availability...\n";
    
    if (!EmailHelper::isPHPMailerAvailable()) {
        echo "Error: PHPMailer is not available\n";
        echo "Please install PHPMailer by running: composer install\n";
        exit(1);
    }
    
    echo "PHPMailer is available ✓\n";
    
} catch (Exception $e) {
    echo "Error checking PHPMailer: " . $e->getMessage() . "\n";
    exit(1);
}

// Test email connection
try {
    echo "Testing email connection...\n";
    
    $connectionTest = EmailHelper::testConnection();
    if ($connectionTest) {
        echo "Email connection test successful ✓\n";
    } else {
        echo "Warning: Email connection test failed\n";
        echo "Continuing with queue processing...\n";
    }
    
} catch (Exception $e) {
    echo "Warning: Email connection test error: " . $e->getMessage() . "\n";
    echo "Continuing with queue processing...\n";
}

// Process email queue
use App\Models\EmailQueue;

try {
    echo "Starting email queue processing...\n";
    
    $emailQueue = new EmailQueue();
    
    // Process up to 50 emails at a time
    $results = $emailQueue->processQueue(50);
    
    echo "Email processing results:\n";
    echo "- Processed: {$results['processed']}\n";
    echo "- Success: {$results['success']}\n";
    echo "- Failed: {$results['failed']}\n";
    
    if (!empty($results['errors'])) {
        echo "Errors:\n";
        foreach ($results['errors'] as $error) {
            echo "- $error\n";
        }
    }
    
    // Clean up old sent emails (older than 30 days)
    $cleaned = $emailQueue->cleanupOldEmails(30);
    echo "Cleaned up $cleaned old emails\n";
    
    echo "Email queue processing completed successfully.\n";
    
} catch (Exception $e) {
    echo "Error processing email queue: " . $e->getMessage() . "\n";
    error_log("Email queue processing error: " . $e->getMessage());
    exit(1);
}

// Optional: Send test email if requested
if (isset($argv[1]) && $argv[1] === '--test' && isset($argv[2])) {
    $testEmail = $argv[2];
    echo "\nSending test email to: $testEmail\n";
    
    try {
        $testResult = EmailHelper::sendTestEmail($testEmail);
        if ($testResult) {
            echo "Test email sent successfully ✓\n";
        } else {
            echo "Test email failed ✗\n";
        }
    } catch (Exception $e) {
        echo "Test email error: " . $e->getMessage() . "\n";
    }
}

echo "\nScript completed at: " . date('Y-m-d H:i:s') . "\n";
