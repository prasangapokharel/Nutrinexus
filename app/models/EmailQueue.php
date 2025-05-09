<?php
namespace App\Models;

use App\Core\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailQueue
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Add an email to the queue
     * 
     * @param string $toEmail Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string $toName Recipient name (optional)
     * @param int $priority Priority (1-10, lower is higher priority)
     * @param array $metadata Additional metadata (optional)
     * @param string|null $scheduledAt When to send the email (null for immediate)
     * @return int|bool The ID of the queued email or false on failure
     */
    public function queueEmail($toEmail, $subject, $body, $toName = '', $priority = 5, $metadata = [], $scheduledAt = null)
    {
        $this->db->query('INSERT INTO email_queue (to_email, to_name, subject, body, priority, metadata, scheduled_at) 
                          VALUES (:to_email, :to_name, :subject, :body, :priority, :metadata, :scheduled_at)');
        
        $this->db->bind(':to_email', $toEmail);
        $this->db->bind(':to_name', $toName);
        $this->db->bind(':subject', $subject);
        $this->db->bind(':body', $body);
        $this->db->bind(':priority', $priority);
        $this->db->bind(':metadata', json_encode($metadata));
        $this->db->bind(':scheduled_at', $scheduledAt);
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     * Get pending emails from the queue
     * 
     * @param int $limit Maximum number of emails to retrieve
     * @return array Array of pending emails
     */
    public function getPendingEmails($limit = 10)
    {
        $now = date('Y-m-d H:i:s');
        
        $this->db->query('SELECT * FROM email_queue 
                          WHERE status = :pending 
                          AND (scheduled_at IS NULL OR scheduled_at <= :now)
                          AND attempts < max_attempts
                          ORDER BY priority ASC, created_at ASC
                          LIMIT :limit');
        
        $this->db->bind(':pending', 'pending');
        $this->db->bind(':now', $now);
        $this->db->bind(':limit', $limit, \PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    /**
     * Mark an email as processing
     * 
     * @param int $id Email ID
     * @return bool Success or failure
     */
    public function markAsProcessing($id)
    {
        $this->db->query('UPDATE email_queue 
                          SET status = :status, 
                              attempts = attempts + 1,
                              last_attempt = NOW()
                          WHERE id = :id');
        
        $this->db->bind(':status', 'processing');
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    /**
     * Mark an email as sent
     * 
     * @param int $id Email ID
     * @return bool Success or failure
     */
    public function markAsSent($id)
    {
        $this->db->query('UPDATE email_queue 
                          SET status = :status, 
                              sent_at = NOW()
                          WHERE id = :id');
        
        $this->db->bind(':status', 'sent');
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    /**
     * Mark an email as failed
     * 
     * @param int $id Email ID
     * @param string $errorMessage Error message
     * @return bool Success or failure
     */
    public function markAsFailed($id, $errorMessage)
    {
        $this->db->query('UPDATE email_queue 
                          SET status = :status, 
                              error_message = :error_message
                          WHERE id = :id');
        
        $this->db->bind(':status', 'failed');
        $this->db->bind(':error_message', $errorMessage);
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    /**
     * Reset failed emails to pending for retry
     * 
     * @param int $olderThanHours Only reset emails older than this many hours
     * @param int $maxAttempts Only reset emails with fewer than this many attempts
     * @return int Number of emails reset
     */
    public function resetFailedEmails($olderThanHours = 1, $maxAttempts = 3)
    {
        $cutoffTime = date('Y-m-d H:i:s', strtotime("-{$olderThanHours} hours"));
        
        $this->db->query('UPDATE email_queue 
                          SET status = :pending_status
                          WHERE status = :failed_status
                          AND last_attempt < :cutoff_time
                          AND attempts < :max_attempts');
        
        $this->db->bind(':pending_status', 'pending');
        $this->db->bind(':failed_status', 'failed');
        $this->db->bind(':cutoff_time', $cutoffTime);
        $this->db->bind(':max_attempts', $maxAttempts);
        
        $this->db->execute();
        
        return $this->db->rowCount();
    }

    /**
     * Get email queue statistics
     * 
     * @return array Statistics about the email queue
     */
    public function getStatistics()
    {
        $this->db->query('SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                            SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing,
                            SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
                            SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                            SUM(CASE WHEN status = "failed" AND attempts >= max_attempts THEN 1 ELSE 0 END) as max_attempts_reached
                          FROM email_queue');
        
        return $this->db->single();
    }

    /**
     * Clean up old sent emails
     * 
     * @param int $olderThanDays Delete sent emails older than this many days
     * @return int Number of emails deleted
     */
    public function cleanupOldEmails($olderThanDays = 30)
    {
        $cutoffTime = date('Y-m-d H:i:s', strtotime("-{$olderThanDays} days"));
        
        $this->db->query('DELETE FROM email_queue 
                          WHERE status = :sent_status
                          AND sent_at < :cutoff_time');
        
        $this->db->bind(':sent_status', 'sent');
        $this->db->bind(':cutoff_time', $cutoffTime);
        
        $this->db->execute();
        
        return $this->db->rowCount();
    }

    /**
     * Process the email queue
     * 
     * @param int $limit Maximum number of emails to process
     * @return array Processing results
     */
    public function processQueue($limit = 10)
    {
        $results = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        // Get pending emails
        $pendingEmails = $this->getPendingEmails($limit);
        
        if (empty($pendingEmails)) {
            return $results;
        }
        
        foreach ($pendingEmails as $email) {
            $results['processed']++;
            
            // Mark as processing
            $this->markAsProcessing($email['id']);
            
            try {
                // Send the email
                $success = $this->sendEmail($email);
                
                if ($success) {
                    // Mark as sent
                    $this->markAsSent($email['id']);
                    $results['success']++;
                } else {
                    // Mark as failed
                    $this->markAsFailed($email['id'], 'Failed to send email');
                    $results['failed']++;
                    $results['errors'][] = "Email ID {$email['id']}: Failed to send email";
                }
            } catch (\Exception $e) {
                // Mark as failed with error message
                $this->markAsFailed($email['id'], $e->getMessage());
                $results['failed']++;
                $results['errors'][] = "Email ID {$email['id']}: {$e->getMessage()}";
            }
        }
        
        return $results;
    }

    /**
     * Send an email using PHPMailer
     * 
     * @param array $emailData Email data from the queue
     * @return bool Success or failure
     */
    private function sendEmail($emailData)
    {
        try {
            // Create a new PHPMailer instance
            $mail = new PHPMailer(true);
            
            // Server settings
            if (defined('MAIL_DEBUG') && MAIL_DEBUG > 0) {
                $mail->SMTPDebug = MAIL_DEBUG;
            }
            
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            
            // Set encryption based on configuration
            if (defined('MAIL_ENCRYPTION') && MAIL_ENCRYPTION === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            $mail->Port = MAIL_PORT;
            
            // Sender
            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addReplyTo(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            
            // Recipient
            $mail->addAddress($emailData['to_email'], $emailData['to_name']);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $emailData['subject'];
            $mail->Body    = $emailData['body'];
            $mail->CharSet = 'UTF-8';
            
            // Add attachments if any (from metadata)
            $metadata = json_decode($emailData['metadata'], true);
            if (isset($metadata['attachments']) && is_array($metadata['attachments'])) {
                foreach ($metadata['attachments'] as $attachment) {
                    if (isset($attachment['path']) && file_exists($attachment['path'])) {
                        $mail->addAttachment(
                            $attachment['path'],
                            $attachment['name'] ?? basename($attachment['path']),
                            $attachment['encoding'] ?? 'base64',
                            $attachment['type'] ?? ''
                        );
                    }
                }
            }
            
            // Send the email
            return $mail->send();
            
        } catch (\Exception $e) {
            error_log('Error sending email from queue: ' . $e->getMessage());
            throw $e;
        }
    }
}