-- ======================================================
-- Seed Data for Pharmacy Management System
-- ======================================================

USE `pharmacy_db`;

-- Insert default branches
INSERT INTO `branches` (`name`, `address`, `phone`) VALUES
('Main Branch - Downtown', '123 Health Avenue, Downtown', '+1-555-0101'),
('Westside Pharmacy', '456 Oak Street, Westside', '+1-555-0102'),
('Northgate Branch', '789 Pine Road, Northgate', '+1-555-0103');

-- Insert manager user (password: Admin@123)
-- Hash generated using password_hash('Admin@123', PASSWORD_DEFAULT)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `branch_id`, `status`) VALUES
('Yonas', 'admin@pharmaflow.system', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'manager', 1, 'active');

-- Insert sample pharmacist
INSERT INTO `users` (`name`, `email`, `password`, `role`, `branch_id`, `status`) VALUES
('Abel', 'pharmacist@PharmaFlow.com', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'pharmacist', 1, 'active');

-- Insert sample store keeper
INSERT INTO `users` (`name`, `email`, `password`, `role`, `branch_id`, `status`) VALUES
('Hana', 'storekeeper@PharmaFlow.com', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'store_keeper', 1, 'active');

-- Insert sample drugs (with new SRS fields: Cost Price, Manufacturer, Supplier)
INSERT INTO `drugs` (`name`, `category`, `batch`, `stock`, `price`, `cost_price`, `manufacturer`, `supplier`, `expiry_date`, `branch_id`) VALUES
('Amoxicillin 500mg', 'Antibiotic', 'APX-2026-001', 250, 6.00, 4.00, 'HealthCorp', 'Global Meds', '2027-06-15', 1),
('Ibuprofen 400mg', 'Painkiller', 'IBU-2026-002', 500, 3.50, 2.30, 'ReliefPharma', 'Local Distrib', '2027-03-20', 1),
('Metformin 850mg', 'Diabetes', 'MET-2026-003', 120, 5.50, 3.70, 'BioCare', 'UniHealth', '2026-12-01', 1),
('Cetirizine 10mg', 'Respiratory', 'CET-2026-004', 300, 3.50, 2.30, 'AllergySoft', 'QuickMeds', '2027-09-10', 2),
('Omeprazole 20mg', 'Gastrointestinal', 'OMP-2026-005', 12, 5.50, 3.70, 'DigestiveCare', 'UniHealth', '2026-05-01', 2),
('Clotrimazole Cream', 'Antifungal', 'CLT-2026-008', 5, 40.00, 30.00, 'SkinMed', 'Local Distrib', '2026-04-20', 1);

-- Insert sample notifications
INSERT INTO `notifications` (`user_id`, `type`, `message`, `is_read`, `created_at`) VALUES
(1, 'expiry', 'Clotrimazole Cream (Batch: CLT-2026-008) expires on Apr 20, 2026.', 0, DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(1, 'low_stock', 'Clotrimazole Cream (Batch: CLT-2026-008) has only 5 units remaining.', 0, DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(1, 'expiry', 'Omeprazole 20mg (Batch: OMP-2026-005) expires on May 01, 2026.', 0, DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(2, 'low_stock', 'Clotrimazole Cream has only 5 units left.', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'expiry', 'Omeprazole 20mg expires on May 01, 2026.', 0, DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Insert sample stock transfer
INSERT INTO `transfers` (`drug_id`, `quantity`, `from_location`, `to_location`, `branch_id`, `created_by`, `status`, `transfer_date`) VALUES
(5, 4, 'store', 'dispensary', 2, 3, 'completed', '2026-04-07 07:56:00');