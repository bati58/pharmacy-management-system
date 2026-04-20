USE `pharmacy_db`;

ALTER TABLE `sales`
    ADD COLUMN `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `total_amount`,
    ADD COLUMN `prescription_reference` VARCHAR(100) DEFAULT NULL AFTER `payment_method`;
