-- Run this if you already created the DB from an older schema.sql
USE `pharmacy_db`;

ALTER TABLE `drugs`
    ADD COLUMN `manufacturer` VARCHAR(150) DEFAULT NULL AFTER `category`,
    ADD COLUMN `supplier` VARCHAR(150) DEFAULT NULL AFTER `manufacturer`;

CREATE TABLE IF NOT EXISTS `invitations` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `role` ENUM('manager', 'pharmacist', 'store_keeper') NOT NULL,
    `branch_id` INT(11) DEFAULT NULL,
    `used` TINYINT(1) NOT NULL DEFAULT 0,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_invitations_token` (`token`),
    KEY `idx_invitations_email` (`email`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
