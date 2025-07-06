<?php

namespace App\Models;

use App\Core\Model;

class SMSTemplate extends Model
{
    protected $table = 'sms_templates';
    protected $primaryKey = 'id';

    // Template categories
    const CATEGORIES = [
        'welcome' => 'Welcome Messages',
        'abandoned_cart' => 'Abandoned Cart',
        'order_confirmation' => 'Order Confirmation',
        'shipping' => 'Shipping Updates',
        'delivery' => 'Delivery Notifications',
        'review_request' => 'Review Requests',
        'win_back' => 'Win Back Campaigns',
        'birthday' => 'Birthday Offers',
        'promotional' => 'Promotional Messages',
        'restock' => 'Restock Alerts',
        'loyalty' => 'Loyalty Rewards',
        'upsell' => 'Upsell Offers',
        'cross_sell' => 'Cross-sell Offers'
    ];

    /**
     * Get all templates with pagination and filtering
     *
     * @param int $limit
     * @param int $offset
     * @param string|null $category
     * @param bool|null $isActive
     * @return array
     */
    public function getAllTemplates(int $limit = 20, int $offset = 0, ?string $category = null, ?bool $isActive = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        if ($isActive !== null) {
            $sql .= " AND is_active = ?";
            $params[] = $isActive ? 1 : 0;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->query($sql)->bind($params)->all();
    }

    /**
     * Get total number of templates with filtering
     *
     * @param string|null $category
     * @param bool|null $isActive
     * @return int
     */
    public function getTotalTemplates(?string $category = null, ?bool $isActive = null): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        if ($isActive !== null) {
            $sql .= " AND is_active = ?";
            $params[] = $isActive ? 1 : 0;
        }

        $result = $this->db->query($sql)->bind($params)->single();
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Find template by ID
     *
     * @param mixed $id
     * @return array|false
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->query($sql)->bind([$id])->single();
    }

    /**
     * Get templates by category
     *
     * @param string $category
     * @param bool $activeOnly
     * @return array
     */
    public function getByCategory(string $category, bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE category = ?";
        $params = [$category];

        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }

        $sql .= " ORDER BY priority DESC, created_at DESC";

        return $this->db->query($sql)->bind($params)->all();
    }

    /**
     * Create new template
     *
     * @param mixed $data
     * @return int|false
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (name, category, content, variables, is_active, priority, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $variables = isset($data['variables']) ? json_encode($data['variables']) : null;

        $result = $this->db->query($sql)->bind([
            htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8'),
            $data['category'] ?? 'promotional',
            htmlspecialchars($data['content'], ENT_QUOTES, 'UTF-8'),
            $variables,
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            $data['priority'] ?? 1
        ])->execute();

        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Update template
     *
     * @param mixed $id
     * @param mixed $data
     * @return bool
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET name = ?, category = ?, content = ?, variables = ?, 
                is_active = ?, priority = ?, updated_at = NOW() WHERE {$this->primaryKey} = ?";

        $variables = isset($data['variables']) ? json_encode($data['variables']) : null;

        return $this->db->query($sql)->bind([
            htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8'),
            $data['category'] ?? 'promotional',
            htmlspecialchars($data['content'], ENT_QUOTES, 'UTF-8'),
            $variables,
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            $data['priority'] ?? 1,
            $id
        ])->execute();
    }

    /**
     * Delete template
     *
     * @param mixed $id
     * @return bool
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->query($sql)->bind([$id])->execute();
    }

    /**
     * Toggle template active status
     *
     * @param mixed $id
     * @return bool
     */
    public function toggleActive($id): bool
    {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active, updated_at = NOW() WHERE {$this->primaryKey} = ?";
        return $this->db->query($sql)->bind([$id])->execute();
    }

    /**
     * Process template with variables
     *
     * @param mixed $templateId
     * @param array $variables
     * @return string|false
     */
    public function processTemplate($templateId, array $variables = []): string|false
    {
        $template = $this->find($templateId);
        if (!$template) {
            return false;
        }

        $content = $template['content'];

        // Replace variables in template
        foreach ($variables as $key => $value) {
            $content = str_replace('{' . $key . '}', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $content);
        }

        return $content;
    }

    /**
     * Get template variables
     *
     * @param mixed $templateId
     * @return array
     */
    public function getTemplateVariables($templateId): array
    {
        $template = $this->find($templateId);
        if (!$template || !$template['variables']) {
            return [];
        }

        return json_decode($template['variables'], true) ?? [];
    }

    /**
     * Log SMS sending attempt
     *
     * @param array $data
     * @return bool
     */
    public function logSMS(array $data): bool
    {
        $sql = "INSERT INTO sms_logs (user_id, phone_number, template_id, campaign_id, message, 
                status, provider_response, cost, error_message, sent_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $providerResponse = isset($data['provider_response']) ? json_encode($data['provider_response']) : null;

        return $this->db->query($sql)->bind([
            $data['user_id'] ?? null,
            htmlspecialchars($data['phone_number'], ENT_QUOTES, 'UTF-8'),
            $data['template_id'] ?? null,
            $data['campaign_id'] ?? null,
            htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8'),
            $data['status'] ?? 'queued',
            $providerResponse,
            $data['cost'] ?? 0.00,
            $data['error_message'] ? htmlspecialchars($data['error_message'], ENT_QUOTES, 'UTF-8') : null
        ])->execute();
    }

    /**
     * Get SMS logs with pagination
     *
     * @param int $limit
     * @param int $offset
     * @param array $filters
     * @return array
     */
    public function getSMSLogs(int $limit = 20, int $offset = 0, array $filters = []): array
    {
        $sql = "SELECT sl.*, st.name as template_name, sc.name as campaign_name 
                FROM sms_logs sl 
                LEFT JOIN sms_templates st ON sl.template_id = st.id
                LEFT JOIN sms_campaigns sc ON sl.campaign_id = sc.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND sl.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND sl.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['phone_number'])) {
            $sql .= " AND sl.phone_number = ?";
            $params[] = $filters['phone_number'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND sl.created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND sl.created_at <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY sl.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->query($sql)->bind($params)->all();
    }

    /**
     * Get SMS statistics
     *
     * @param array $filters
     * @return array
     */
    public function getSMSStats(array $filters = []): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_sent,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed,
                    COUNT(CASE WHEN status = 'bounce' THEN 1 END) as bounced,
                    ROUND(COUNT(CASE WHEN status = 'delivered' THEN 1 END) * 100.0 / COUNT(*), 2) as delivery_rate,
                    SUM(cost) as total_cost
                FROM sms_logs WHERE 1=1";
        $params = [];

        if (!empty($filters['date_from'])) {
            $sql .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['campaign_id'])) {
            $sql .= " AND campaign_id = ?";
            $params[] = $filters['campaign_id'];
        }

        return $this->db->query($sql)->bind($params)->single();
    }

    /**
     * Check if user can receive SMS
     *
     * @param mixed $userId
     * @param string $category
     * @return bool
     */
    public function canUserReceiveSMS($userId, string $category = 'promotional'): bool
    {
        $sql = "SELECT is_subscribed, marketing_consent, transactional_consent, categories
                FROM user_sms_preferences WHERE user_id = ?";
        $preferences = $this->db->query($sql)->bind([$userId])->single();

        if (!$preferences) {
            return false; // No preferences set
        }

        if (!$preferences['is_subscribed']) {
            return false; // User unsubscribed
        }

        // Check consent based on category
        if (in_array($category, ['promotional', 'win_back', 'birthday', 'loyalty', 'upsell', 'cross_sell'])) {
            if (!$preferences['marketing_consent']) {
                return false; // No marketing consent
            }
        } else {
            if (!$preferences['transactional_consent']) {
                return false; // No transactional consent
            }
        }

        // Check specific category preferences
        if ($preferences['categories']) {
            $allowedCategories = json_decode($preferences['categories'], true);
            if (!in_array($category, $allowedCategories)) {
                return false; // Category not allowed
            }
        }

        // Check if phone number is blacklisted
        $sql = "SELECT COUNT(*) as count FROM sms_blacklist sb
                JOIN user_sms_preferences usp ON sb.phone_number = usp.phone_number
                WHERE usp.user_id = ?";
        $blacklistCheck = $this->db->query($sql)->bind([$userId])->single();

        if ($blacklistCheck['count'] > 0) {
            return false; // Phone number is blacklisted
        }

        return true;
    }

    /**
     * Get template usage statistics
     *
     * @param mixed $templateId
     * @return array
     */
    public function getTemplateUsageStats($templateId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_uses,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed,
                    ROUND(COUNT(CASE WHEN status = 'delivered' THEN 1 END) * 100.0 / COUNT(*), 2) as delivery_rate,
                    SUM(cost) as total_cost,
                    MAX(created_at) as last_used
                FROM sms_logs WHERE template_id = ?";

        return $this->db->query($sql)->bind([$templateId])->single();
    }

    /**
     * Duplicate template
     *
     * @param mixed $templateId
     * @param string $newName
     * @return int|false
     */
    public function duplicateTemplate($templateId, string $newName): int|false
    {
        $template = $this->find($templateId);
        if (!$template) {
            return false;
        }

        $newTemplate = [
            'name' => $newName,
            'category' => $template['category'],
            'content' => $template['content'],
            'variables' => json_decode($template['variables'], true),
            'is_active' => 0, // Set as inactive by default
            'priority' => $template['priority']
        ];

        return $this->create($newTemplate);
    }

    /**
     * Get popular templates (most used)
     *
     * @param int $limit
     * @return array
     */
    public function getPopularTemplates(int $limit = 10): array
    {
        $sql = "SELECT st.*, COUNT(sl.id) as usage_count,
                ROUND(COUNT(CASE WHEN sl.status = 'delivered' THEN 1 END) * 100.0 / COUNT(sl.id), 2) as delivery_rate
                FROM sms_templates st
                LEFT JOIN sms_logs sl ON st.id = sl.template_id
                WHERE st.is_active = 1
                GROUP BY st.id
                ORDER BY usage_count DESC, delivery_rate DESC
                LIMIT ?";

        return $this->db->query($sql)->bind([$limit])->all();
    }

    /**
     * Validate template content
     *
     * @param string $content
     * @return array
     */
    public function validateTemplate(string $content): array
    {
        $errors = [];

        // Check length (SMS limit is usually 160 characters for single SMS)
        if (strlen($content) > 160) {
            $errors[] = "Template content exceeds 160 characters (" . strlen($content) . " characters)";
        }

        // Check for unmatched braces
        preg_match_all('/\{([^}]+)\}/', $content, $matches);
        $variables = $matches[1];

        foreach ($variables as $variable) {
            if (empty(trim($variable))) {
                $errors[] = "Empty variable placeholder found";
            }
        }

        // Check for common issues
        if (strpos($content, '{{') !== false || strpos($content, '}}') !== false) {
            $errors[] = "Double braces found - use single braces for variables";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'variables' => array_unique($variables),
            'character_count' => strlen($content),
            'estimated_sms_parts' => ceil(strlen($content) / 160)
        ];
    }

    public function hasAutomaticReminder($userId, $orderId)
    {
        $sql = "SELECT COUNT(*) as count FROM sms_logs 
                WHERE user_id = :user_id 
                AND order_id = :order_id 
                AND is_automatic = 1 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 28 DAY)";
        $params = [
            ':user_id' => $userId,
            ':order_id' => $orderId
        ];

        $result = $this->db->query($sql, $params)->fetch();
        return $result['count'] > 0;
    }
    
}