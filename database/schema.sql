-- ======================================================
-- Pharmacy Management System - Database Schema
-- MySQL / MariaDB
-- ======================================================

CREATE DATABASE IF NOT EXISTS `pharmacy_db`;
USE `pharmacy_db`;

-- ======================================================
-- 1. Branches table
-- ======================================================
CREATE TABLE `branches` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `address` TEXT,
    `phone` VARCHAR(20),
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 2. Users table
-- ======================================================
CREATE TABLE `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('manager', 'pharmacist', 'store_keeper') NOT NULL,
    `branch_id` INT(11) DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_users_email` (`email`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_status` (`status`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 3. Drugs table
-- ======================================================
CREATE TABLE `drugs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `category` VARCHAR(50) DEFAULT NULL,
    `manufacturer` VARCHAR(150) DEFAULT NULL,
    `supplier` VARCHAR(150) DEFAULT NULL,
    `batch` VARCHAR(50) NOT NULL,
    `stock` INT(11) NOT NULL DEFAULT 0,
    `price` DECIMAL(10,2) NOT NULL,
    `expiry_date` DATE NOT NULL,
    `branch_id` INT(11) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_drugs_batch` (`batch`),
    KEY `idx_drugs_expiry` (`expiry_date`),
    KEY `idx_drugs_stock` (`stock`),
    KEY `idx_drugs_name` (`name`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 4. Sales table
-- ======================================================
CREATE TABLE `sales` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `invoice_no` VARCHAR(20) NOT NULL UNIQUE,
    `customer_name` VARCHAR(100) DEFAULT 'Walk-in customer',
    `total_amount` DECIMAL(10,2) NOT NULL,
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `payment_method` ENUM('Cash', 'Card', 'Mobile Money') NOT NULL,
    `prescription_reference` VARCHAR(100) DEFAULT NULL,
    `pharmacist_id` INT(11) NOT NULL,
    `branch_id` INT(11) NOT NULL,
    `sale_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sales_invoice` (`invoice_no`),
    KEY `idx_sales_date` (`sale_date`),
    KEY `idx_sales_pharmacist` (`pharmacist_id`),
    KEY `idx_sales_branch` (`branch_id`),
    FOREIGN KEY (`pharmacist_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 5. Sale Items table
-- ======================================================
CREATE TABLE `sale_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `sale_id` INT(11) NOT NULL,
    `drug_id` INT(11) NOT NULL,
    `quantity` INT(11) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_sale_items_sale` (`sale_id`),
    KEY `idx_sale_items_drug` (`drug_id`),
    FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`drug_id`) REFERENCES `drugs`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 6. Stock Transfers table
-- ======================================================
CREATE TABLE `transfers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `drug_id` INT(11) NOT NULL,
    `quantity` INT(11) NOT NULL,
    `from_location` ENUM('store', 'dispensary') NOT NULL,
    `to_location` ENUM('store', 'dispensary') NOT NULL,
    `branch_id` INT(11) NOT NULL,
    `created_by` INT(11) NOT NULL,
    `status` ENUM('pending', 'completed', 'cancelled') DEFAULT 'completed',
    `transfer_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_transfers_drug` (`drug_id`),
    KEY `idx_transfers_branch` (`branch_id`),
    KEY `idx_transfers_created_by` (`created_by`),
    KEY `idx_transfers_date` (`transfer_date`),
    FOREIGN KEY (`drug_id`) REFERENCES `drugs`(`id`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 7. Notifications table
-- ======================================================
CREATE TABLE `notifications` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `type` ENUM('low_stock', 'expiry', 'system') NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notifications_user` (`user_id`),
    KEY `idx_notifications_read` (`is_read`),
    KEY `idx_notifications_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 8. Password Resets table (for reset tokens)
-- ======================================================
CREATE TABLE `password_resets` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_password_resets_email` (`email`),
    KEY `idx_password_resets_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Invitations (manager invites staff to set password)
-- ======================================================
CREATE TABLE `invitations` (
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

-- ======================================================
-- Optional: Stock Movements log (for audit trail)
-- ======================================================
CREATE TABLE `stock_movements` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `drug_id` INT(11) NOT NULL,
    `quantity_change` INT(11) NOT NULL,
    `reason` VARCHAR(100) DEFAULT NULL,
    `user_id` INT(11) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_stock_movements_drug` (`drug_id`),
    FOREIGN KEY (`drug_id`) REFERENCES `drugs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;