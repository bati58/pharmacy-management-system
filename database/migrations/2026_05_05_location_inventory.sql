USE `pharmacy_db`;

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `inventory` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `drug_id` INT(11) NOT NULL,
    `branch_id` INT(11) NOT NULL,
    `location` ENUM('store', 'dispensary') NOT NULL,
    `quantity` INT(11) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_inventory_drug_branch_location` (`drug_id`, `branch_id`, `location`),
    KEY `idx_inventory_branch_location` (`branch_id`, `location`),
    KEY `idx_inventory_quantity` (`quantity`),
    CONSTRAINT `chk_inventory_quantity_non_negative` CHECK (`quantity` >= 0),
    FOREIGN KEY (`drug_id`) REFERENCES `drugs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `inventory` (`drug_id`, `branch_id`, `location`, `quantity`)
SELECT `id`, `branch_id`, 'store', GREATEST(COALESCE(`stock`, 0), 0)
FROM `drugs`
ON DUPLICATE KEY UPDATE `quantity` = VALUES(`quantity`);

INSERT INTO `inventory` (`drug_id`, `branch_id`, `location`, `quantity`)
SELECT `id`, `branch_id`, 'dispensary', GREATEST(COALESCE(`dispensary_stock`, 0), 0)
FROM `drugs`
ON DUPLICATE KEY UPDATE `quantity` = VALUES(`quantity`);

ALTER TABLE `transfers`
    CHANGE COLUMN `from_location` `source_location` ENUM('store', 'dispensary') NOT NULL,
    CHANGE COLUMN `to_location` `destination_location` ENUM('store', 'dispensary') NOT NULL;

ALTER TABLE `stock_movements`
    ADD COLUMN `branch_id` INT(11) NULL AFTER `drug_id`,
    ADD COLUMN `location` ENUM('store', 'dispensary') DEFAULT NULL AFTER `branch_id`;

UPDATE `stock_movements` sm
JOIN `drugs` d ON d.`id` = sm.`drug_id`
SET sm.`branch_id` = d.`branch_id`
WHERE sm.`branch_id` IS NULL;

ALTER TABLE `stock_movements`
    MODIFY COLUMN `branch_id` INT(11) NOT NULL,
    ADD KEY `idx_stock_movements_branch_location` (`branch_id`, `location`),
    ADD CONSTRAINT `fk_stock_movements_branch`
        FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE;

ALTER TABLE `drugs`
    DROP COLUMN `stock`,
    DROP COLUMN `dispensary_stock`;

COMMIT;
