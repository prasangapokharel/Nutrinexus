<?php
namespace App\Models;

use App\Core\Model;
use App\Helpers\CacheHelper;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    /**
     * Find user by email
     *
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        return $this->db->query($sql)->bind([$email])->single();
    }
    
    /**
     * Find user by username
     *
     * @param string $username
     * @return array|false
     */
    public function findByUsername($username)
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = ?";
        return $this->db->query($sql)->bind([$username])->single();
    }
    
    /**
     * Find user by column value
     *
     * @param string $column
     * @param mixed $value
     * @return array|false
     */
    public function findOneBy($column, $value)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        return $this->db->query($sql)->bind([$value])->single();
    }
    
    /**
     * Find user by reset token
     *
     * @param string $token
     * @return array|false
     */
    public function findByResetToken($token)
    {
        $sql = "SELECT * FROM {$this->table} WHERE reset_token = ? AND reset_expires > NOW()";
        return $this->db->query($sql)->bind([$token])->single();
    }
    
    /**
     * Authenticate user
     *
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function authenticate($email, $password)
    {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Register new user
     *
     * @param array $data
     * @return int|bool
     */
    public function register($data)
    {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default role
        $data['role'] = 'customer';
        
        // Set default referral earnings
        $data['referral_earnings'] = 0;
        
        // Generate referral code if not provided
        if (!isset($data['referral_code']) || empty($data['referral_code'])) {
            $data['referral_code'] = $this->generateReferralCode();
        }
        
        // Set created_at and updated_at
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $userId = $this->create($data);
        
        // Clear user cache if caching is enabled
        if ($userId && class_exists('App\Helpers\CacheHelper')) {
            $cache = CacheHelper::getInstance();
            $cache->delete($cache->generateKey('user_count', []));
        }
        
        return $userId;
    }
    
    /**
     * Save reset token
     *
     * @param int $userId
     * @param string $token
     * @param string $expires
     * @return bool
     */
    public function saveResetToken($userId, $token, $expires)
    {
        $sql = "UPDATE {$this->table} SET reset_token = ?, reset_expires = ? WHERE id = ?";
        return $this->db->query($sql)->bind([$token, $expires, $userId])->execute();
    }
    
    /**
     * Clear reset token
     *
     * @param int $userId
     * @return bool
     */
    public function clearResetToken($userId)
    {
        $sql = "UPDATE {$this->table} SET reset_token = NULL, reset_expires = NULL WHERE id = ?";
        return $this->db->query($sql)->bind([$userId])->execute();
    }
    
    /**
     * Update password
     *
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function updatePassword($userId, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE {$this->table} SET password = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql)->bind([$hashedPassword, $userId])->execute();
    }
    
    /**
     * Get user count
     *
     * @return int
     */
    public function getUserCount()
    {
        // Try to get from cache if available
        if (class_exists('App\Helpers\CacheHelper')) {
            $cache = CacheHelper::getInstance();
            $cacheKey = $cache->generateKey('user_count', []);
            $count = $cache->get($cacheKey);
            
            if ($count !== null) {
                return $count;
            }
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->query($sql)->single();
        $count = $result ? (int)$result['count'] : 0;
        
        // Store in cache if available
        if (isset($cache) && isset($cacheKey)) {
            $cache->set($cacheKey, $count, 3600); // Cache for 1 hour
        }
        
        return $count;
    }
    
    /**
     * Get users referred by a user
     *
     * @param int $userId
     * @return array
     */
    public function getReferrals($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE referred_by = ? ORDER BY created_at DESC";
        return $this->db->query($sql)->bind([$userId])->all();
    }
    
    /**
     * Get referral count for a user
     *
     * @param int $userId
     * @return int
     */
    public function getReferralCount($userId)
    {
        // Try to get from cache if available
        if (class_exists('App\Helpers\CacheHelper')) {
            $cache = CacheHelper::getInstance();
            $cacheKey = $cache->generateKey('user_referral_count', ['user_id' => $userId]);
            $count = $cache->get($cacheKey);
            
            if ($count !== null) {
                return $count;
            }
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE referred_by = ?";
        $result = $this->db->query($sql)->bind([$userId])->single();
        $count = $result ? (int)$result['count'] : 0;
        
        // Store in cache if available
        if (isset($cache) && isset($cacheKey)) {
            $cache->set($cacheKey, $count, 1800); // Cache for 30 minutes
        }
        
        return $count;
    }
    
    /**
     * Add referral earnings to user's balance
     *
     * @param int $userId
     * @param float $amount
     * @return bool
     */
    public function addReferralEarnings($userId, $amount)
    {
        // Get current user data
        $user = $this->find($userId);
        
        if (!$user) {
            error_log("Failed to add referral earnings: User ID {$userId} not found");
            return false;
        }
        
        // Calculate new earnings
        $currentEarnings = (float)($user['referral_earnings'] ?? 0);
        $newEarnings = $currentEarnings + $amount;
        
        // Update user's referral earnings
        $data = [
            'referral_earnings' => $newEarnings,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->update($userId, $data);
        
        if (!$result) {
            error_log("Failed to update referral earnings for User ID {$userId}");
            return false;
        }
        
        // Clear user cache if caching is enabled
        if (class_exists('App\Helpers\CacheHelper')) {
            $cache = CacheHelper::getInstance();
            $cache->delete($cache->generateKey('user', ['id' => $userId]));
            $cache->delete($cache->generateKey('user_referral_stats', ['user_id' => $userId]));
        }
        
        return true;
    }
    
    /**
     * Deduct referral earnings from user's balance
     *
     * @param int $userId
     * @param float $amount
     * @return bool
     */
    public function deductReferralEarnings($userId, $amount)
    {
        // Get current user data
        $user = $this->find($userId);
        
        if (!$user) {
            error_log("Failed to deduct referral earnings: User ID {$userId} not found");
            return false;
        }
        
        // Calculate new earnings (ensure it doesn't go below zero)
        $currentEarnings = (float)($user['referral_earnings'] ?? 0);
        $newEarnings = max(0, $currentEarnings - $amount);
        
        // Update user's referral earnings
        $data = [
            'referral_earnings' => $newEarnings,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->update($userId, $data);
        
        if (!$result) {
            error_log("Failed to update referral earnings for User ID {$userId}");
            return false;
        }
        
        // Clear user cache if caching is enabled
        if (class_exists('App\Helpers\CacheHelper')) {
            $cache = CacheHelper::getInstance();
            $cache->delete($cache->generateKey('user', ['id' => $userId]));
            $cache->delete($cache->generateKey('user_referral_stats', ['user_id' => $userId]));
        }
        
        return true;
    }
    
    /**
     * Get user's referral statistics
     *
     * @param int $userId
     * @return array
     */
    public function getReferralStats($userId)
    {
        // Try to get from cache if available
        if (class_exists('App\Helpers\CacheHelper')) {
            $cache = CacheHelper::getInstance();
            $cacheKey = $cache->generateKey('user_referral_stats', ['user_id' => $userId]);
            $stats = $cache->get($cacheKey);
            
            if ($stats !== null) {
                return $stats;
            }
        }
        
        // Get user
        $user = $this->find($userId);
        
        if (!$user) {
            return [
                'total_referrals' => 0,
                'total_earnings' => 0,
                'available_balance' => 0,
                'referral_code' => '',
                'referral_link' => ''
            ];
        }
        
        // Get referral count
        $referralCount = $this->getReferralCount($userId);
        
        // Get total earnings (from ReferralEarning model if available)
        $totalEarnings = 0;
        if (class_exists('App\Models\ReferralEarning')) {
            $referralEarningModel = new ReferralEarning();
            $totalEarnings = $referralEarningModel->getTotalEarnings($userId);
        } else {
            $totalEarnings = (float)($user['referral_earnings'] ?? 0);
        }
        
        // Get available balance
        $availableBalance = (float)($user['referral_earnings'] ?? 0);
        
        // Get referral code
        $referralCode = $user['referral_code'] ?? '';
        
        // Generate referral link
        $baseUrl = $this->getBaseUrl();
        $referralLink = $baseUrl . '/register?ref=' . $referralCode;
        
        $stats = [
            'total_referrals' => $referralCount,
            'total_earnings' => $totalEarnings,
            'available_balance' => $availableBalance,
            'referral_code' => $referralCode,
            'referral_link' => $referralLink
        ];
        
        // Store in cache if available
        if (isset($cache) && isset($cacheKey)) {
            $cache->set($cacheKey, $stats, 1800); // Cache for 30 minutes
        }
        
        return $stats;
    }
    
    /**
     * Generate a unique referral code
     *
     * @return string
     */
    public function generateReferralCode()
    {
        // Generate a random string
        $code = substr(str_shuffle(md5(time())), 0, 8);
        
        // Check if code already exists
        $existingUser = $this->findOneBy('referral_code', $code);
        
        // If code exists, generate a new one
        if ($existingUser) {
            return $this->generateReferralCode();
        }
        
        return $code;
    }
    
    /**
     * Find user by referral code
     *
     * @param string $code
     * @return array|false
     */
    public function findByReferralCode($code)
    {
        $sql = "SELECT * FROM {$this->table} WHERE referral_code = ?";
        return $this->db->query($sql)->bind([$code])->single();
    }
    
    /**
     * Get user's referrals with details
     *
     * @param int $userId
     * @return array
     */
    public function getUserReferrals($userId)
    {
        $sql = "SELECT u.id, u.first_name, u.last_name, u.email, u.created_at,
                (SELECT COUNT(*) FROM orders WHERE user_id = u.id AND status = 'paid') as order_count,
                (SELECT SUM(amount) FROM referral_earnings WHERE user_id = ? AND order_id IN 
                    (SELECT id FROM orders WHERE user_id = u.id)
                ) as earnings
                FROM {$this->table} u
                WHERE u.referred_by = ?
                ORDER BY u.created_at DESC";
        
        return $this->db->query($sql)->bind([$userId, $userId])->all();
    }
    
    /**
     * Count user's referrals
     *
     * @param int $userId
     * @return int
     */
    public function countUserReferrals($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE referred_by = ?";
        $result = $this->db->query($sql)->bind([$userId])->single();
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Fix zero ID issue
     *
     * @param int $userId
     * @return int|bool
     */
    public function fixZeroId($userId)
    {
        if ($userId !== 0) {
            return $userId;
        }
        
        // Get the last inserted ID
        $sql = "SELECT MAX(id) as max_id FROM {$this->table}";
        $result = $this->db->query($sql)->single();
        
        if (!$result || !isset($result['max_id'])) {
            return false;
        }
        
        return (int)$result['max_id'];
    }
    
    /**
     * Get base URL for the application
     * 
     * @return string
     */
    private function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script_name = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        
        // Fix for localhost - ensure the path is correct
        if ($host === 'localhost' || strpos($host, '127.0.0.1') !== false) {
            // For localhost, construct the path based on the current directory structure
            $base_path = $protocol . "://" . $host;
            
            // If script_name is just a slash, don't add it to avoid double slashes
            if ($script_name !== '/' && $script_name !== '\\') {
                // Remove any trailing slashes
                $script_name = rtrim($script_name, '/\\');
                $base_path .= $script_name;
            }
            
            $base_url = $base_path;
        } else {
            // For production servers
            $base_url = $protocol . "://" . $host . $script_name;
        }
        
        // Ensure base_url doesn't have a trailing slash
        return rtrim($base_url, '/');
    }
}
