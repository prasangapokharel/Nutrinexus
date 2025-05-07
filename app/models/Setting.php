<?php
namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    
    protected $table = 'settings';
    protected $primaryKey = 'id';

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default Default value if setting not found
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $sql = "SELECT value FROM {$this->table} WHERE `key` = ? LIMIT 1";
        $result = $this->db->query($sql)->bind([$key])->single();
        
        if ($result) {
            // Check if value is JSON
            $decoded = json_decode($result['value'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            
            return $result['value'];
        }
        
        return $default;
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set($key, $value)
    {
        // Check if value needs to be JSON encoded
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }
        
        // Check if setting already exists
        $sql = "SELECT id FROM {$this->table} WHERE `key` = ? LIMIT 1";
        $existing = $this->db->query($sql)->bind([$key])->single();
        
        if ($existing) {
            // Update existing setting
            $sql = "UPDATE {$this->table} SET value = ?, updated_at = NOW() WHERE id = ?";
            return $this->db->query($sql)->bind([$value, $existing['id']])->execute();
        } else {
            // Create new setting
            $sql = "INSERT INTO {$this->table} (`key`, value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
            return $this->db->query($sql)->bind([$key, $value])->execute();
        }
    }

    /**
     * Get all settings
     *
     * @return array
     */
    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table}";
        $results = $this->db->query($sql)->all();
        
        $settings = [];
        foreach ($results as $row) {
            // Check if value is JSON
            $decoded = json_decode($row['value'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $settings[$row['key']] = $decoded;
            } else {
                $settings[$row['key']] = $row['value'];
            }
        }
        
        return $settings;
    }

    /**
     * Get referral settings
     *
     * @return array
     */
    public function getReferralSettings()
    {
        $defaults = [
            'commission_rate' => 10, // Default 10%
            'min_withdrawal' => 100, // Default ₹100
            'auto_approve' => false, // Default false
            'processing_time' => 3, // Default 3 days
            'payment_methods' => ['bank_transfer', 'upi', 'paytm'] // Default all methods
        ];
        
        $settings = [];
        
        // Get commission rate
        $settings['commission_rate'] = (float)$this->get('referral_commission_rate', $defaults['commission_rate']);
        
        // Get minimum withdrawal amount
        $settings['min_withdrawal'] = (float)$this->get('min_withdrawal_amount', $defaults['min_withdrawal']);
        
        // Get auto approve setting
        $settings['auto_approve'] = (bool)$this->get('auto_approve_referrals', $defaults['auto_approve']);
        
        // Get processing time
        $settings['processing_time'] = (int)$this->get('withdrawal_processing_time', $defaults['processing_time']);
        
        // Get payment methods
        $settings['payment_methods'] = $this->get('withdrawal_payment_methods', $defaults['payment_methods']);
        
        return $settings;
    }

    /**
     * Save referral settings
     *
     * @param array $settings
     * @return bool
     */
    public function saveReferralSettings($settings)
    {
        $success = true;
        
        // Set commission rate
        if (isset($settings['commission_rate'])) {
            $success = $success && $this->set('referral_commission_rate', $settings['commission_rate']);
        }
        
        // Set minimum withdrawal amount
        if (isset($settings['min_withdrawal'])) {
            $success = $success && $this->set('min_withdrawal_amount', $settings['min_withdrawal']);
        }
        
        // Set auto approve setting
        if (isset($settings['auto_approve'])) {
            $success = $success && $this->set('auto_approve_referrals', $settings['auto_approve'] ? 1 : 0);
        }
        
        // Set processing time
        if (isset($settings['processing_time'])) {
            $success = $success && $this->set('withdrawal_processing_time', $settings['processing_time']);
        }
        
        // Set payment methods
        if (isset($settings['payment_methods'])) {
            $success = $success && $this->set('withdrawal_payment_methods', $settings['payment_methods']);
        }
        
        return $success;
    }

    /**
     * Create settings table if it doesn't exist
     *
     * @return bool
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            `key` VARCHAR(255) NOT NULL UNIQUE,
            value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        return $this->db->rawQuery($sql) !== false;
    }

    /**
     * Initialize default settings
     *
     * @return bool
     */
    public function initializeDefaults()
    {
        $defaults = [
            'referral_commission_rate' => 10,
            'min_withdrawal_amount' => 100,
            'auto_approve_referrals' => 0,
            'withdrawal_processing_time' => 3,
            'withdrawal_payment_methods' => ['bank_transfer', 'upi', 'paytm']
        ];
        
        $success = true;
        
        foreach ($defaults as $key => $value) {
            // Only set if not already exists
            $sql = "SELECT id FROM {$this->table} WHERE `key` = ? LIMIT 1";
            $existing = $this->db->query($sql)->bind([$key])->single();
            
            if (!$existing) {
                $success = $success && $this->set($key, $value);
            }
        }
        
        return $success;
    }
}