<?php
namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Email Helper
 * Provides email functionality using PHPMailer
 */
class EmailHelper
{
    /**
     * Send an email
     *
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param array $headers
     * @return bool
     */
    public static function send($to, $subject, $message, $headers = [])
    {
        try {
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            
            // Recipients
            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($to);
            
            // Set reply-to if specified
            if (isset($headers['Reply-To'])) {
                $mail->addReplyTo($headers['Reply-To']);
            }
            
            // Add CC recipients if specified
            if (isset($headers['CC'])) {
                if (is_array($headers['CC'])) {
                    foreach ($headers['CC'] as $cc) {
                        $mail->addCC($cc);
                    }
                } else {
                    $mail->addCC($headers['CC']);
                }
            }
            
            // Add BCC recipients if specified
            if (isset($headers['BCC'])) {
                if (is_array($headers['BCC'])) {
                    foreach ($headers['BCC'] as $bcc) {
                        $mail->addBCC($bcc);
                    }
                } else {
                    $mail->addBCC($headers['BCC']);
                }
            }
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $message));
            
            return $mail->send();
        } catch (Exception $e) {
            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Send an email with template
     *
     * @param string $to
     * @param string $subject
     * @param string $template
     * @param array $data
     * @param array $headers
     * @return bool
     */
    public static function sendTemplate($to, $subject, $template, $data = [], $headers = [])
    {
        // Load template
        $templatePath = dirname(dirname(__FILE__)) . '/views/emails/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            return false;
        }
        
        // Extract data to make variables available in template
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the template file
        include $templatePath;
        
        // Get the contents of the buffer
        $message = ob_get_clean();
        
        // Send email
        return self::send($to, $subject, $message, $headers);
    }

    /**
     * Send email in background
     *
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param array $headers
     * @return bool
     */
    public static function sendInBackground($to, $subject, $message, $headers = [])
    {
        // Store email data in a file
        $emailData = [
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
            'headers' => $headers,
            'timestamp' => time()
        ];
        
        $filename = dirname(dirname(__FILE__)) . '/storage/emails/' . uniqid() . '.email';
        
        // Create directory if it doesn't exist
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        
        // Save email data
        file_put_contents($filename, serialize($emailData));
        
        // Execute email sender script in background
        if (PHP_OS === 'WINNT') {
            pclose(popen('start /B php ' . dirname(dirname(__FILE__)) . '/console/send_emails.php', 'r'));
        } else {
            exec('php ' . dirname(dirname(__FILE__)) . '/console/send_emails.php > /dev/null 2>&1 &');
        }
        
        return true;
    }
}
