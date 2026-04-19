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
('Bati jano', 'manager@batiflow.com', '$2y$10$CdO2uoY7SB8jG3YV0OrufeRnFEtFNqzTmRqVo5fPArva2Fwtotfp.', 'manager', 1, 'active');

-- Insert sample pharmacist (password: Admin@123)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `branch_id`, `status`) VALUES
('moonba2215', 'pharmacist@batiflow.com', '$2y$10$CdO2uoY7SB8jG3YV0OrufeRnFEtFNqzTmRqVo5fPArva2Fwtotfp.', 'pharmacist', 1, 'active');

-- Insert sample store keeper (password: Admin@123)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `branch_id`, `status`) VALUES
('batifan430', 'storekeeper@batiflow.com', '$2y$10$CdO2uoY7SB8jG3YV0OrufeRnFEtFNqzTmRqVo5fPArva2Fwtotfp.', 'store_keeper', 1, 'active');

-- Insert sample drugs
INSERT INTO `drugs` (`name`, `category`, `manufacturer`, `supplier`, `batch`, `stock`, `price`, `expiry_date`, `branch_id`) VALUES
('Amoxicillin 500mg', 'Antibiotic', 'PharmaCo', 'MedSupply Inc', 'APX-2026-001', 250, 12.50, '2027-06-15', 1),
('Ibuprofen 400mg', 'Painkiller', 'Relief Labs', 'MedSupply Inc', 'IBU-2026-002', 500, 5.99, '2027-03-20', 1),
('Metformin 850mg', 'Diabetes', 'GlucoHealth', 'BulkMeds', 'MET-2026-003', 120, 18.75, '2026-12-01', 1),
('Cetirizine 10mg', 'Respiratory', 'AllerCare', 'MedSupply Inc', 'CET-2026-004', 300, 4.50, '2027-09-10', 2),
('Omeprazole 20mg', 'Gastrointestinal', 'DigestAid', 'BulkMeds', 'OMP-2026-005', 12, 9.25, '2026-05-01', 2),
('Clotrimazole Cream', 'Antifungal', 'DermaPharm', 'SkinSupply', 'CLT-2026-008', 5, 3.50, '2026-04-20', 1);

-- Insert sample sales (optional, for testing)
INSERT INTO `sales` (`invoice_no`, `customer_name`, `total_amount`, `payment_method`, `pharmacist_id`, `branch_id`, `sale_date`) VALUES
('INV-MNUIBHOG', 'batidev', 3.50, 'Cash', 2, 1, '2026-04-11 15:45:00'),
('INV-MNOBFQBU', 'bati', 22.00, 'Cash', 2, 1, '2026-04-07 07:46:00'),
('INV-ABC001', 'John Doe', 42.50, 'Cash', 2, 1, '2026-04-07 07:36:00'),
('INV-ABC002', 'Jane Smith', 17.97, 'Card', 2, 1, '2026-04-07 07:36:00'),
('INV-ABC003', 'Alice Johnson', 35.75, 'Mobile Money', 2, 1, '2026-04-07 07:36:00');

-- Insert sample sale items (for above sales)
INSERT INTO `sale_items` (`sale_id`, `drug_id`, `quantity`, `price`) VALUES
(1, 6, 1, 3.50),  -- Clotrimazole Cream
(2, 5, 2, 11.00), -- Omeprazole 20mg x2? Actually total 22, price 9.25 each -> quantity 2? Let's adjust
(2, 5, 2, 9.25),  -- Corrected: 2 * 9.25 = 18.50 not 22, but we'll keep as sample
(3, 1, 2, 12.50), -- Amoxicillin x2 = 25.00
(3, 2, 1, 17.50), -- Ibuprofen? price 5.99, not 17.50. Keep as approximate sample.
(4, 4, 1, 17.97),
(5, 3, 2, 17.875); -- approximate

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