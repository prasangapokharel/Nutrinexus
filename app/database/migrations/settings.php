<?php
// Create settings table
$sql = "CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(255) NOT NULL UNIQUE,
    value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Execute the query
$db->rawQuery($sql);

// Insert default settings
$defaultSettings = [
    ['referral_commission_rate', '10'],
    ['min_withdrawal_amount', '100'],
    ['auto_approve_referrals', '0'],
    ['withdrawal_processing_time', '3'],
    ['withdrawal_payment_methods', json_encode(['bank_transfer', 'upi', 'paytm'])]
];

foreach ($defaultSettings as $setting) {
    $sql = "INSERT IGNORE INTO settings (`key`, value, created_at, updated_at) 
            VALUES (?, ?, NOW(), NOW())";
    $db->query($sql)->bind($setting)->execute();
}

echo "Settings table created and initialized successfully.\n";