USE `pharmacy_db`;

ALTER TABLE `users`
    MODIFY COLUMN `password` VARCHAR(255) NULL,
    MODIFY COLUMN `status` ENUM('pending', 'active', 'inactive') NOT NULL DEFAULT 'pending',
    ADD COLUMN `invite_token` VARCHAR(64) NULL AFTER `status`,
    ADD COLUMN `token_expiry` DATETIME NULL AFTER `invite_token`,
    ADD UNIQUE KEY `idx_users_invite_token` (`invite_token`);
