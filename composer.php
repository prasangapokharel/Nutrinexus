<?php
/**
 * PHPMailer Installation and Setup Script
 * Run this to install and configure PHPMailer
 */

echo "NutriNexus PHPMailer Installation Script\n";
echo "=======================================\n\n";

// Check if Composer is available
function checkComposer() {
    $composerPaths = [
        'composer',
        'composer.phar',
        '/usr/local/bin/composer',
        '/usr/bin/composer'
    ];
    
    foreach ($composerPaths as $composer) {
        $output = [];
        $returnVar = 0;
        exec("$composer --version 2>&1", $output, $returnVar);
        
        if ($returnVar === 0) {
            echo "✓ Composer found: $composer\n";
            return $composer;
        }
    }
    
    return false;
}

// Install PHPMailer via Composer
function installPHPMailer($composer) {
    echo "Installing PHPMailer via Composer...\n";
    
    // Create composer.json if it doesn't exist
    if (!file_exists('composer.json')) {
        $composerConfig = [
            'name' => 'nutrinexus/ecommerce',
            'description' => 'NutriNexus E-commerce Platform',
            'type' => 'project',
            'require' => [
                'php' => '>=7.4',
                'phpmailer/phpmailer' => '^6.8',
                'vlucas/phpdotenv' => '^5.5'
            ],
            'autoload' => [
                'psr-4' => [
                    'App\\' => 'app/'
                ]
            ],
            'config' => [
                'optimize-autoloader' => true,
                'sort-packages' => true
            ],
            'minimum-stability' => 'stable',
            'prefer-stable' => true
        ];
        
        file_put_contents('composer.json', json_encode($composerConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        echo "✓ Created composer.json\n";
    }
    
    // Run composer install
    $output = [];
    $returnVar = 0;
    exec("$composer install 2>&1", $output, $returnVar);
    
    if ($returnVar === 0) {
        echo "✓ PHPMailer installed successfully\n";
        return true;
    } else {
        echo "✗ Failed to install PHPMailer\n";
        echo "Output:\n" . implode("\n", $output) . "\n";
        return false;
    }
}

// Test PHPMailer installation
function testPHPMailer() {
    echo "Testing PHPMailer installation...\n";
    
    // Include autoloader
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
        echo "✓ Autoloader found\n";
    } else {
        echo "✗ Autoloader not found\n";
        return false;
    }
    
    // Test PHPMailer class
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "✓ PHPMailer class available\n";
        return true;
    } else {
        echo "✗ PHPMailer class not found\n";
        return false;
    }
}

// Test email functionality
function testEmailFunctionality() {
    echo "Testing email functionality...\n";
    
    try {
        // Include bootstrap
        if (file_exists('app/bootstrap.php')) {
            require_once 'app/bootstrap.php';
        } elseif (file_exists('App/bootstrap.php')) {
            require_once 'App/bootstrap.php';
        } else {
            echo "✗ Bootstrap file not found\n";
            return false;
        }
        
        // Test EmailHelper
        if (class_exists('App\Helpers\EmailHelper')) {
            $available = App\Helpers\EmailHelper::isPHPMailerAvailable();
            if ($available) {
                echo "✓ EmailHelper can use PHPMailer\n";
                
                // Test connection
                $connectionTest = App\Helpers\EmailHelper::testConnection();
                if ($connectionTest) {
                    echo "✓ SMTP connection successful\n";
                } else {
                    echo "⚠ SMTP connection failed (check credentials)\n";
                }
                
                return true;
            } else {
                echo "✗ EmailHelper cannot use PHPMailer\n";
                return false;
            }
        } else {
            echo "✗ EmailHelper class not found\n";
            return false;
        }
        
    } catch (Exception $e) {
        echo "✗ Error testing email functionality: " . $e->getMessage() . "\n";
        return false;
    }
}

// Main installation process
echo "Step 1: Checking Composer...\n";
$composer = checkComposer();

if (!$composer) {
    echo "✗ Composer not found. Please install Composer first:\n";
    echo "Visit: https://getcomposer.org/download/\n";
    exit(1);
}

echo "\nStep 2: Installing PHPMailer...\n";
$installed = installPHPMailer($composer);

if (!$installed) {
    echo "✗ Failed to install PHPMailer\n";
    exit(1);
}

echo "\nStep 3: Testing PHPMailer...\n";
$phpmailerTest = testPHPMailer();

if (!$phpmailerTest) {
    echo "✗ PHPMailer test failed\n";
    exit(1);
}

echo "\nStep 4: Testing Email Functionality...\n";
$emailTest = testEmailFunctionality();

if (!$emailTest) {
    echo "⚠ Email functionality test failed, but PHPMailer is installed\n";
} else {
    echo "✓ Email functionality test passed\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "INSTALLATION COMPLETE!\n";
echo str_repeat("=", 50) . "\n";

echo "\nNext Steps:\n";
echo "1. Test your checkout process\n";
echo "2. Send a test email: php test_email.php your-email@example.com\n";
echo "3. Run email queue processor: php scripts/process_email_queue.php\n";
echo "4. Set up cron job for email processing\n";

echo "\nCron Job Example:\n";
echo "*/5 * * * * /usr/bin/php " . __DIR__ . "/scripts/process_email_queue.php\n";

echo "\nYour email system is now ready!\n";
