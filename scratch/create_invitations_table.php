<?php
require_once __DIR__ . '/../backend/config/database.php';

$sql = "CREATE TABLE IF NOT EXISTS `invitations` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `token` VARCHAR(255) NOT NULL,
    `role` ENUM('manager', 'pharmacist', 'store_keeper') NOT NULL,
    `branch_id` INT(11) DEFAULT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_invitations_email` (`email`),
    KEY `idx_invitations_token` (`token`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

try {
    $pdo->exec($sql);
    echo "Table 'invitations' created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
