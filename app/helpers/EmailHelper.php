<?php
namespace App\Helpers;

/**
 * EmailHelper - PHPMailer Exclusive with Proper Error Handling
 * Ensures PHPMailer is loaded and works perfectly
 */
class EmailHelper
{
    private static $config = [
        'host' => 'smtp.hostinger.com',
        'port' => 465,
        'username' => 'Nutrinexus@shp.re',
        'password' => 'Y1]r&ePF~/k',
        'encryption' => 'ssl',
        'from_email' => 'Nutrinexus@shp.re',
        'from_name' => 'Nutri Nexus',
        'debug' => false
    ];

    private static $phpmailerLoaded = null;

    /**
     * Initialize PHPMailer - ensures it's properly loaded
     */
    private static function initPHPMailer()
    {
        if (self::$phpmailerLoaded !== null) {
            return self::$phpmailerLoaded;
        }

        // Try to load PHPMailer classes
        try {
            // Check if Composer autoloader is loaded
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                // Try to load Composer autoloader
                $autoloadPaths = [
                    __DIR__ . '/../../vendor/autoload.php',
                    dirname(dirname(__DIR__)) . '/vendor/autoload.php',
                    dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php',
                ];

                foreach ($autoloadPaths as $autoloadPath) {
                    if (file_exists($autoloadPath)) {
                        require_once $autoloadPath;
                        error_log('Loaded Composer autoloader from: ' . $autoloadPath);
                        break;
                    }
                }
            }

            // Check if PHPMailer is now available
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                self::$phpmailerLoaded = true;
                error_log('PHPMailer successfully loaded');
                return true;
            } else {
                self::$phpmailerLoaded = false;
                error_log('PHPMailer not found after autoloader attempts');
                return false;
            }

        } catch (\Exception $e) {
            self::$phpmailerLoaded = false;
            error_log('Error loading PHPMailer: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email using PHPMailer exclusively
     */
    public static function send($to, $subject, $body, $toName = '', $isHTML = true)
    {
        // Ensure PHPMailer is loaded
        if (!self::initPHPMailer()) {
            error_log('Cannot send email: PHPMailer not available');
            throw new \Exception('PHPMailer is required but not installed. Please run: composer install');
        }

        try {
            // Import PHPMailer classes
            $phpmailerClass = 'PHPMailer\PHPMailer\PHPMailer';
            $smtpClass = 'PHPMailer\PHPMailer\SMTP';
            $exceptionClass = 'PHPMailer\PHPMailer\Exception';

            // Suppress any output from PHPMailer
            ob_start();
            
            // Create PHPMailer instance
            $mail = new $phpmailerClass(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = self::getConfig('host');
            $mail->SMTPAuth = true;
            $mail->Username = self::getConfig('username');
            $mail->Password = self::getConfig('password');
            
            // Handle encryption
            $encryption = self::getConfig('encryption');
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = $phpmailerClass::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = $phpmailerClass::ENCRYPTION_STARTTLS;
            }
            
            $mail->Port = self::getConfig('port');
            
            // Debug settings
            if (self::getConfig('debug')) {
                $mail->SMTPDebug = $smtpClass::DEBUG_SERVER;
                $mail->Debugoutput = function($str, $level) {
                    error_log("PHPMailer Debug Level $level: " . trim($str));
                };
            } else {
                $mail->SMTPDebug = $smtpClass::DEBUG_OFF;
            }

            // Recipients
            $mail->setFrom(self::getConfig('from_email'), self::getConfig('from_name'));
            $mail->addAddress($to, $toName);

            // Content
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            // Set charset and encoding
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            
            // Additional headers
            $mail->addCustomHeader('X-Mailer', 'NutriNexus Mailer v1.0');
            $mail->addCustomHeader('X-Priority', '3');

            // Send email
            $result = $mail->send();
            
            // Clean any output buffer
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            if ($result) {
                error_log("Email sent successfully to: $to via PHPMailer");
                return true;
            } else {
                error_log("Failed to send email to: $to - PHPMailer returned false");
                return false;
            }

        } catch (\Exception $e) {
            // Clean any output buffer
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            error_log("PHPMailer Exception for $to: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send template-based email
     * This is the missing method that AuthController is calling
     */
    public static function sendTemplate($to, $subject, $template, $templateData = [], $toName = '')
    {
        try {
            // Get template content
            $templatePath = self::getTemplatePath($template);
            $body = '';
            
            if ($templatePath && file_exists($templatePath)) {
                // Load template from file
                $body = file_get_contents($templatePath);
                
                if ($body !== false) {
                    // Replace placeholders with data
                    foreach ($templateData as $key => $value) {
                        $body = str_replace('{{' . $key . '}}', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $body);
                    }
                    error_log('Using email template from: ' . $templatePath);
                } else {
                    error_log('Failed to read template file: ' . $templatePath);
                    $body = self::createFallbackTemplate($template, $templateData);
                }
            } else {
                error_log('Template file not found: ' . $template . ', using fallback');
                $body = self::createFallbackTemplate($template, $templateData);
            }
            
            // Send the email
            return self::send($to, $subject, $body, $toName, true);
            
        } catch (\Exception $e) {
            error_log('Failed to send template email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email configuration status
     * This is the missing method that AuthController is calling
     */
    public static function getConfigStatus()
    {
        return [
            'phpmailer_available' => self::initPHPMailer(),
            'host' => self::getConfig('host'),
            'port' => self::getConfig('port'),
            'username' => self::getConfig('username'),
            'from_email' => self::getConfig('from_email'),
            'from_name' => self::getConfig('from_name'),
            'encryption' => self::getConfig('encryption'),
            'debug' => self::getConfig('debug')
        ];
    }

    /**
     * Create fallback email templates when template files are not found
     */
    private static function createFallbackTemplate($template, $data)
    {
        switch ($template) {
            case 'login':
                return self::createLoginTemplate($data);
            case 'register':
                return self::createWelcomeTemplate($data);
            case 'forgot-password':
                return self::createPasswordResetTemplate($data);
            case 'password-changed':
                return self::createPasswordChangedTemplate($data);
            default:
                return self::createGenericTemplate($template, $data);
        }
    }

    /**
     * Create login notification template
     */
    private static function createLoginTemplate($data)
    {
        $firstName = self::getSafeValue($data, 'first_name', 'User');
        $loginTime = self::getSafeValue($data, 'login_time', date('Y-m-d H:i:s'));
        $ipAddress = self::getSafeValue($data, 'ip_address', 'Unknown');
        $siteName = self::getSafeValue($data, 'site_name', 'NutriNexus');

        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Notification - ' . htmlspecialchars($siteName) . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background: #0a3167; color: white; padding: 20px; text-align: center; border-radius: 5px; margin-bottom: 20px; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #0a3167; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Login Notification</h1>
            <p>New login to your ' . htmlspecialchars($siteName) . ' account</p>
        </div>
        
        <p>Hello <strong>' . htmlspecialchars($firstName) . '</strong>,</p>
        <p>We wanted to let you know that someone just logged into your ' . htmlspecialchars($siteName) . ' account.</p>
        
        <div class="info-box">
            <h3>Login Details</h3>
            <p><strong>Time:</strong> ' . htmlspecialchars($loginTime) . '</p>
            <p><strong>IP Address:</strong> ' . htmlspecialchars($ipAddress) . '</p>
        </div>
        
        <p>If this was you, you can safely ignore this email. If you didn\'t log in, please contact our support team immediately.</p>
        
        <div class="footer">
            <p><strong>' . htmlspecialchars($siteName) . '</strong></p>
            <p>This is an automated security notification. Please do not reply to this email.</p>
            <p>© 2025 ' . htmlspecialchars($siteName) . '. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Create welcome email template
     */
    private static function createWelcomeTemplate($data)
    {
        $firstName = self::getSafeValue($data, 'first_name', 'User');
        $siteName = self::getSafeValue($data, 'site_name', 'NutriNexus');
        $siteUrl = self::getSafeValue($data, 'site_url', 'http://localhost');

        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ' . htmlspecialchars($siteName) . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #0a3167 0%, #082850 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 5px; margin-bottom: 20px; }
        .welcome-box { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center; }
        .cta-button { display: inline-block; background: #C5A572; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Welcome to ' . htmlspecialchars($siteName) . '!</h1>
            <p>Your journey to better nutrition starts here</p>
        </div>
        
        <p>Hello <strong>' . htmlspecialchars($firstName) . '</strong>,</p>
        <p>Welcome to ' . htmlspecialchars($siteName) . '! We\'re thrilled to have you join our community of health-conscious individuals.</p>
        
        <div class="welcome-box">
            <h3>🌟 What\'s Next?</h3>
            <p>Explore our premium nutrition supplements and start your wellness journey today!</p>
            <a href="' . htmlspecialchars($siteUrl) . '" class="cta-button">Start Shopping</a>
        </div>
        
        <p>If you have any questions, our support team is here to help. Feel free to reach out anytime!</p>
        
        <div class="footer">
            <p><strong>' . htmlspecialchars($siteName) . '</strong></p>
            <p>Thank you for choosing us for your nutrition needs.</p>
            <p>© 2025 ' . htmlspecialchars($siteName) . '. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Create password reset template
     */
    private static function createPasswordResetTemplate($data)
    {
        $firstName = self::getSafeValue($data, 'first_name', 'User');
        $resetUrl = self::getSafeValue($data, 'reset_url', '#');
        $siteName = self::getSafeValue($data, 'site_name', 'NutriNexus');

        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - ' . htmlspecialchars($siteName) . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #0A3167 0%, #082850 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 5px; margin-bottom: 20px; }
        .reset-box { background: #f8f9fa; border-left: 4px solid #C5A572; padding: 25px; margin: 25px 0; border-radius: 0 8px 8px 0; text-align: center; }
        .reset-button { display: inline-block; background: linear-gradient(135deg, #C5A572 0%, #B89355 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: 600; font-size: 16px; margin: 20px 0; }
        .security-info { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; }
        .link-text { background-color: #f8f9fa; padding: 10px; border-radius: 4px; word-break: break-all; font-family: monospace; font-size: 12px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 Reset Your Password</h1>
            <p>' . htmlspecialchars($siteName) . ' Password Recovery</p>
        </div>
        
        <p>Hello <strong>' . htmlspecialchars($firstName) . '</strong>,</p>
        <p>We received a request to reset your password for your ' . htmlspecialchars($siteName) . ' account. If you didn\'t make this request, you can safely ignore this email.</p>
        
        <div class="reset-box">
            <h3>Reset Your Password</h3>
            <p>Click the button below to create a new password:</p>
            <a href="' . htmlspecialchars($resetUrl) . '" class="reset-button">Reset Password</a>
        </div>
        
        <p>If the button doesn\'t work, you can copy and paste this link into your browser:</p>
        <div class="link-text">' . htmlspecialchars($resetUrl) . '</div>
        
        <div class="security-info">
            <p><strong>⏰ Important:</strong></p>
            <p>This password reset link will expire in 1 hour for security reasons. If you need to reset your password after this time, please request a new reset link.</p>
        </div>
        
        <div class="footer">
            <p>If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>
            <p>© 2025 ' . htmlspecialchars($siteName) . '. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Create password changed template
     */
    private static function createPasswordChangedTemplate($data)
    {
        $firstName = self::getSafeValue($data, 'first_name', 'User');
        $siteName = self::getSafeValue($data, 'site_name', 'NutriNexus');

        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Changed - ' . htmlspecialchars($siteName) . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background: #0a3167; color: white; padding: 20px; text-align: center; border-radius: 5px; margin-bottom: 20px; }
        .success-box { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Password Changed</h1>
            <p>Your ' . htmlspecialchars($siteName) . ' password has been updated</p>
        </div>
        
        <p>Hello <strong>' . htmlspecialchars($firstName) . '</strong>,</p>
        
        <div class="success-box">
            <h3>Password Successfully Changed</h3>
            <p>Your password has been successfully updated. You can now use your new password to log in to your account.</p>
        </div>
        
        <p>If you did not make this change, please contact our support team immediately.</p>
        
        <div class="footer">
            <p><strong>' . htmlspecialchars($siteName) . '</strong></p>
            <p>This is an automated security notification. Please do not reply to this email.</p>
            <p>© 2025 ' . htmlspecialchars($siteName) . '. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Create generic template for unknown template types
     */
    private static function createGenericTemplate($template, $data)
    {
        $siteName = self::getSafeValue($data, 'site_name', 'NutriNexus');
        
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification - ' . htmlspecialchars($siteName) . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background: #0a3167; color: white; padding: 20px; text-align: center; border-radius: 5px; margin-bottom: 20px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Notification from ' . htmlspecialchars($siteName) . '</h1>
        </div>
        
        <p>You have received a notification from ' . htmlspecialchars($siteName) . '.</p>
        <p>Template: ' . htmlspecialchars($template) . '</p>
        
        <div class="footer">
            <p><strong>' . htmlspecialchars($siteName) . '</strong></p>
            <p>© 2025 ' . htmlspecialchars($siteName) . '. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Send order confirmation email using PHPMailer
     */
    public static function sendOrderConfirmation($order, $orderItems, $user)
    {
        try {
            // Validate input data
            if (empty($order) || empty($user) || empty($user['email'])) {
                error_log('Invalid order confirmation email data');
                return false;
            }

            error_log('Preparing order confirmation email for: ' . $user['email']);

            // Prepare template data with safe defaults
            $templateData = [
                'customer_name' => self::getSafeValue($order, 'customer_name', 
                    self::getSafeValue($user, 'first_name', 'Customer') . ' ' . 
                    self::getSafeValue($user, 'last_name', '')),
                'order_number' => self::getSafeValue($order, 'invoice', 'N/A'),
                'order_date' => date('F j, Y', strtotime(self::getSafeValue($order, 'created_at', date('Y-m-d H:i:s')))),
                'payment_method' => self::getPaymentMethodName(self::getSafeValue($order, 'payment_method_id', 1)),
                'order_status' => ucfirst(self::getSafeValue($order, 'status', 'pending')),
                'subtotal' => number_format(self::getSafeValue($order, 'total_amount', 0) - self::getSafeValue($order, 'delivery_fee', 0), 2),
                'delivery_fee' => number_format(self::getSafeValue($order, 'delivery_fee', 0), 2),
                'total_amount' => number_format(self::getSafeValue($order, 'total_amount', 0), 2),
                'delivery_address' => self::getSafeValue($order, 'address', 'Address not provided'),
                'track_url' => self::getBaseUrl() . '/orders/track',
                'support_url' => self::getBaseUrl() . '/contact',
                'company_name' => 'NutriNexus',
                'company_email' => 'support@nutrinexus.com',
                'company_phone' => '+977-1-4567890'
            ];

            // Generate order items HTML safely
            $orderItemsHtml = self::generateOrderItemsHtml($orderItems);
            $templateData['order_items'] = $orderItemsHtml;

            // Get email template
            $emailBody = self::getEmailTemplate($templateData);

            // Send email using PHPMailer
            $subject = 'Order Confirmation - #' . $templateData['order_number'];
            $result = self::send($user['email'], $subject, $emailBody, $templateData['customer_name']);

            if ($result) {
                error_log('Order confirmation email sent successfully via PHPMailer for order: ' . $templateData['order_number']);
            } else {
                error_log('Failed to send order confirmation email via PHPMailer for order: ' . $templateData['order_number']);
            }

            return $result;

        } catch (\Exception $e) {
            error_log('Failed to send order confirmation email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test PHPMailer connection and functionality
     */
    public static function testConnection()
    {
        try {
            // Ensure PHPMailer is loaded
            if (!self::initPHPMailer()) {
                error_log('PHPMailer connection test failed: PHPMailer not available');
                return false;
            }

            $phpmailerClass = 'PHPMailer\PHPMailer\PHPMailer';
            $smtpClass = 'PHPMailer\PHPMailer\SMTP';

            ob_start();
            
            $mail = new $phpmailerClass(true);
            $mail->isSMTP();
            $mail->Host = self::getConfig('host');
            $mail->SMTPAuth = true;
            $mail->Username = self::getConfig('username');
            $mail->Password = self::getConfig('password');
            $mail->SMTPSecure = self::getConfig('encryption') === 'ssl' ? 
                $phpmailerClass::ENCRYPTION_SMTPS : $phpmailerClass::ENCRYPTION_STARTTLS;
            $mail->Port = self::getConfig('port');
            $mail->SMTPDebug = $smtpClass::DEBUG_OFF;
            
            // Try to connect
            $result = $mail->smtpConnect();
            
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            if ($result) {
                $mail->smtpClose();
                error_log('PHPMailer SMTP connection test successful');
                return true;
            }
            
            error_log('PHPMailer SMTP connection test failed');
            return false;
            
        } catch (\Exception $e) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            error_log('PHPMailer connection test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send test email
     */
    public static function sendTestEmail($toEmail, $toName = 'Test User')
    {
        try {
            $subject = 'Test Email from NutriNexus - PHPMailer';
            $body = self::createTestEmailTemplate();
            
            $result = self::send($toEmail, $subject, $body, $toName);
            
            if ($result) {
                error_log('Test email sent successfully via PHPMailer to: ' . $toEmail);
                return true;
            } else {
                error_log('Test email failed via PHPMailer to: ' . $toEmail);
                return false;
            }
            
        } catch (\Exception $e) {
            error_log('Test email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email template or create HTML email
     */
    private static function getEmailTemplate($templateData)
    {
        // Try to load template file first
        $templatePath = self::getTemplatePath('order');
        
        if ($templatePath && file_exists($templatePath)) {
            $emailBody = file_get_contents($templatePath);
            
            if ($emailBody !== false) {
                // Replace placeholders
                foreach ($templateData as $key => $value) {
                    $emailBody = str_replace('{{' . $key . '}}', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $emailBody);
                }
                error_log('Using email template from: ' . $templatePath);
                return $emailBody;
            }
        }
        
        // Fallback to built-in HTML template
        error_log('Template file not found, using built-in template');
        return self::createBuiltInEmailTemplate($templateData);
    }

    /**
     * Create built-in HTML email template
     */
    private static function createBuiltInEmailTemplate($data)
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - ' . htmlspecialchars($data['company_name']) . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background: #0a3167; color: white; padding: 20px; text-align: center; border-radius: 5px; margin-bottom: 20px; }
        .order-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .order-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .order-table th, .order-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .order-table th { background-color: #0a3167; color: white; }
        .total { font-weight: bold; font-size: 18px; color: #0a3167; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Confirmation</h1>
            <p>Thank you for your purchase!</p>
        </div>
        
        <p>Hello ' . htmlspecialchars($data['customer_name']) . ',</p>
        <p>We\'re excited to confirm that we\'ve received your order and it\'s being processed.</p>
        
        <div class="order-info">
            <h3>Order Information</h3>
            <p><strong>Order Number:</strong> #' . htmlspecialchars($data['order_number']) . '</p>
            <p><strong>Order Date:</strong> ' . htmlspecialchars($data['order_date']) . '</p>
            <p><strong>Payment Method:</strong> ' . htmlspecialchars($data['payment_method']) . '</p>
            <p><strong>Status:</strong> ' . htmlspecialchars($data['order_status']) . '</p>
        </div>

        <h3>Order Items</h3>
        <table class="order-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                ' . $data['order_items'] . '
            </tbody>
        </table>

        <div class="order-info">
            <h3>Order Summary</h3>
            <p><strong>Subtotal:</strong> ₹' . htmlspecialchars($data['subtotal']) . '</p>
            <p><strong>Delivery Fee:</strong> ₹' . htmlspecialchars($data['delivery_fee']) . '</p>
            <p class="total"><strong>Total Amount:</strong> ₹' . htmlspecialchars($data['total_amount']) . '</p>
        </div>

        <div class="order-info">
            <h3>Delivery Address</h3>
            <p>' . htmlspecialchars($data['delivery_address']) . '</p>
        </div>

        <p><strong>Estimated Delivery:</strong> 3-5 business days</p>
        <p>You\'ll receive a tracking notification once your order ships.</p>

        <div class="footer">
            <p><strong>' . htmlspecialchars($data['company_name']) . '</strong></p>
            <p>Email: ' . htmlspecialchars($data['company_email']) . ' | Phone: ' . htmlspecialchars($data['company_phone']) . '</p>
            <p>© 2025 ' . htmlspecialchars($data['company_name']) . '. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Generate order items HTML safely
     */
    private static function generateOrderItemsHtml($orderItems)
    {
        if (empty($orderItems) || !is_array($orderItems)) {
            return '<tr><td colspan="4" style="text-align: center; color: #666;">No items found</td></tr>';
        }

        $html = '';
        foreach ($orderItems as $item) {
            $productName = self::getSafeValue($item, 'product_name', 'Product');
            $quantity = self::getSafeValue($item, 'quantity', 1);
            $price = self::getSafeValue($item, 'price', 0);
            $total = self::getSafeValue($item, 'total', 0);

            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . $quantity . '</td>';
            $html .= '<td>₹' . number_format($price, 2) . '</td>';
            $html .= '<td>₹' . number_format($total, 2) . '</td>';
            $html .= '</tr>';
        }

        return $html;
    }

    /**
     * Create test email template
     */
    private static function createTestEmailTemplate()
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Email</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .header { background: #0a3167; color: white; padding: 20px; text-align: center; border-radius: 5px; }
        .content { padding: 20px; background: white; border-radius: 5px; margin-top: 10px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PHPMailer Test Email</h1>
        </div>
        <div class="content">
            <div class="success">
                ✅ <strong>Success!</strong> PHPMailer is working correctly.
            </div>
            <p>This is a test email sent from NutriNexus using PHPMailer.</p>
            <p><strong>Email Configuration:</strong></p>
            <ul>
                <li>SMTP Host: ' . self::getConfig('host') . '</li>
                <li>SMTP Port: ' . self::getConfig('port') . '</li>
                <li>Encryption: SSL/TLS</li>
                <li>From: ' . self::getConfig('from_email') . '</li>
            </ul>
            <p>If you received this email, your email configuration is working properly!</p>
            <p><strong>Sent at:</strong> ' . date('Y-m-d H:i:s') . '</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Helper methods
     */
    private static function getSafeValue($array, $key, $default = '')
    {
        return isset($array[$key]) && $array[$key] !== null ? $array[$key] : $default;
    }

    private static function getPaymentMethodName($paymentMethodId)
    {
        $methods = [
            1 => 'Cash on Delivery',
            2 => 'Bank Transfer',
            3 => 'Online Payment'
        ];
        return isset($methods[$paymentMethodId]) ? $methods[$paymentMethodId] : 'Unknown';
    }

    private static function getBaseUrl()
    {
        try {
            if (defined('URLROOT')) {
                return URLROOT;
            }
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
            $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
            return $protocol . $host;
        } catch (\Exception $e) {
            error_log('Error getting base URL: ' . $e->getMessage());
            return 'http://localhost';
        }
    }

    private static function getTemplatePath($template)
    {
        $possiblePaths = [
            $_SERVER['DOCUMENT_ROOT'] . '/assets/templates/' . $template . '.html',
            dirname(dirname(__DIR__)) . '/assets/templates/' . $template . '.html',
            dirname(dirname(dirname(__DIR__))) . '/assets/templates/' . $template . '.html',
            __DIR__ . '/../../assets/templates/' . $template . '.html',
            (defined('APPROOT') ? APPROOT . '/assets/templates/' . $template . '.html' : null)
        ];

        foreach ($possiblePaths as $path) {
            if ($path && file_exists($path) && is_readable($path)) {
                return $path;
            }
        }
        return null;
    }

    private static function getConfig($key)
    {
        try {
            switch ($key) {
                case 'host':
                    return defined('MAIL_HOST') ? MAIL_HOST : self::$config['host'];
                case 'port':
                    return defined('MAIL_PORT') ? MAIL_PORT : self::$config['port'];
                case 'username':
                    return defined('MAIL_USERNAME') ? MAIL_USERNAME : self::$config['username'];
                case 'password':
                    return defined('MAIL_PASSWORD') ? MAIL_PASSWORD : self::$config['password'];
                case 'encryption':
                    return defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : self::$config['encryption'];
                case 'from_email':
                    return defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : self::$config['from_email'];
                case 'from_name':
                    return defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : self::$config['from_name'];
                case 'debug':
                    return defined('MAIL_DEBUG') ? MAIL_DEBUG : self::$config['debug'];
                default:
                    return isset(self::$config[$key]) ? self::$config[$key] : '';
            }
        } catch (\Exception $e) {
            error_log('Error getting email config for key ' . $key . ': ' . $e->getMessage());
            return isset(self::$config[$key]) ? self::$config[$key] : '';
        }
    }

    public static function setConfig($config)
    {
        if (is_array($config)) {
            self::$config = array_merge(self::$config, $config);
        }
    }

    public static function enableDebug()
    {
        self::$config['debug'] = true;
    }

    public static function disableDebug()
    {
        self::$config['debug'] = false;
    }

    /**
     * Check if PHPMailer is properly installed
     */
    public static function isPHPMailerAvailable()
    {
        return self::initPHPMailer();
    }
}
