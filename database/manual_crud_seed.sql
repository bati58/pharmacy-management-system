-- ======================================================
-- Rich Seed Data for Manual CRUD Testing
-- This file populates the database with a wide variety of 
-- realistic branches, users, and drugs for testing pagination, 
-- search, and CRUD operations.
-- IDs start from 101 to avoid conflicts with your main seed.sql
-- ======================================================

USE `pms_db`;

-- ======================================================
-- 1. Insert Rich Branch Data (IDs: 101 to 105)
-- ======================================================
INSERT INTO `branches` (`id`, `name`, `address`, `phone`) VALUES
(101, 'Sunrise Community Pharmacy', '842 Sunrise Blvd, East District', '+1-555-8801'),
(102, 'Evergreen Health Branch', '10 Evergreen Way, Suburbs', '+1-555-8802'),
(103, 'Metro Central Dispensary', '400 Metro Square, City Center', '+1-555-8803'),
(104, 'Riverside Care Pharmacy', '12 River Road, Riverside', '+1-555-8804'),
(105, 'Highland Medical Pharmacy', '77 Highland Drive, Uptown', '+1-555-8805')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- ======================================================
-- 2. Insert Rich User Data (IDs: 101 to 112)
-- Password for all users: Admin@123
-- ======================================================
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `branch_id`, `status`) VALUES
(101, 'Sarah Jenkins', 's.jenkins@pharmaflow.com', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'manager', 101, 'active'),
(102, 'David Chen', 'd.chen@pharmaflow.com', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'pharmacist', 101, 'active'),
(103, 'Michael Ross', 'm.ross@pharmaflow.com', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'store_keeper', 101, 'active'),

(104, 'Emily Watson', 'e.watson@pharmaflow.com', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'manager', 102, 'active'),
(105, 'Robert Lang', 'r.lang@pharmaflow.com', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'pharmacist', 102, 'inactive'),

(106, 'Amanda Cole', 'a.cole@pharmaflow.com', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'manager', 103, 'active'),
(107, 'James Smith', 'j.smith@pharmaflow.com', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'pharmacist', 103, 'active'),
(108, 'Linda Garcia', 'l.garcia@pharmaflow.com', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'store_keeper', 103, 'active'),

(109, 'William Taylor', 'w.taylor@pharmaflow.com', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'manager', 104, 'active'),
(110, 'Jessica Lee', 'j.lee@pharmaflow.com', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'pharmacist', 104, 'active'),

(111, 'Thomas Moore', 't.moore@pharmaflow.com', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'manager', 105, 'active'),
(112, 'Sandra White', 's.white@pharmaflow.com', '$2y$10$dHwT9PB3bTiAR2uvXnsfPulNpe4bXkQDsUdwo7rEqWd3EHgGKRzuK', 'pharmacist', 105, 'active')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- ======================================================
-- 3. Insert Rich Drug Data (IDs: 101 to 120)
-- Completely distinct from existing seed.sql items
-- ======================================================
INSERT INTO `drugs` (`id`, `name`, `category`, `batch`, `stock`, `price`, `cost_price`, `manufacturer`, `supplier`, `expiry_date`, `branch_id`) VALUES
(101, 'Atorvastatin 40mg', 'Cardiovascular', 'ATO-40-2026A', 500, 15.00, 8.50, 'HeartCare Pharma', 'MedSupply Inc', '2027-08-01', 101),
(102, 'Lisinopril 20mg', 'Cardiovascular', 'LIS-20-2025B', 350, 12.00, 5.20, 'VenoMed Labs', 'Global Health Dist', '2026-11-15', 101),
(103, 'Amlodipine 10mg', 'Cardiovascular', 'AML-10-2026C', 420, 14.50, 6.75, 'HeartCare Pharma', 'MedSupply Inc', '2028-02-28', 101),
(104, 'Levothyroxine 50mcg', 'Endocrine', 'LEV-50-2025A', 800, 9.99, 4.10, 'ThyroMed', 'National Med Dist', '2026-06-30', 102),
(105, 'Albuterol Inhaler 90mcg', 'Respiratory', 'ALB-90-2026A', 150, 45.00, 25.00, 'BreatheEasy Co', 'QuickMeds Dist', '2027-01-15', 102),
(106, 'Losartan 50mg', 'Cardiovascular', 'LOS-50-2025B', 275, 11.25, 5.80, 'HeartCare Pharma', 'Global Health Dist', '2026-10-10', 102),
(107, 'Gabapentin 300mg', 'Neurology', 'GAB-300-2026A', 600, 18.50, 9.25, 'NeuroPharma Labs', 'MedSupply Inc', '2027-04-20', 103),
(108, 'Sertraline 50mg', 'Psychiatry', 'SER-50-2026B', 450, 22.00, 11.50, 'MindCare Inc', 'National Med Dist', '2028-05-01', 103),
(109, 'Furosemide 40mg', 'Cardiovascular', 'FUR-40-2025A', 120, 8.50, 3.20, 'VenoMed Labs', 'QuickMeds Dist', '2026-08-15', 103),
(110, 'Pantoprazole 40mg', 'Gastrointestinal', 'PAN-40-2026A', 330, 16.75, 8.40, 'Digestive Care Co', 'Global Health Dist', '2027-03-10', 103),
(111, 'Escitalopram 10mg', 'Psychiatry', 'ESC-10-2026A', 290, 24.50, 12.80, 'MindCare Inc', 'MedSupply Inc', '2027-09-25', 104),
(112, 'Rosuvastatin 20mg', 'Cardiovascular', 'ROS-20-2026C', 180, 28.00, 14.50, 'HeartCare Pharma', 'National Med Dist', '2028-01-30', 104),
(113, 'Pravastatin 40mg', 'Cardiovascular', 'PRA-40-2025A', 210, 19.50, 9.75, 'VenoMed Labs', 'QuickMeds Dist', '2026-12-05', 104),
(114, 'Trazodone 50mg', 'Psychiatry', 'TRA-50-2026B', 140, 15.25, 7.10, 'MindCare Inc', 'Global Health Dist', '2027-07-20', 104),
(115, 'Fluticasone Nasal Spray', 'Respiratory', 'FLU-50-2025A', 85, 32.00, 18.50, 'BreatheEasy Co', 'MedSupply Inc', '2026-05-15', 105),
(116, 'Citalopram 20mg', 'Psychiatry', 'CIT-20-2026A', 310, 21.00, 10.20, 'MindCare Inc', 'National Med Dist', '2027-11-10', 105),
(117, 'Meloxicam 15mg', 'Painkillers', 'MEL-15-2026B', 400, 13.50, 6.25, 'JointCare Pharma', 'QuickMeds Dist', '2028-04-05', 105),
(118, 'Potassium Chloride 20mEq', 'Supplements', 'POT-20-2025C', 550, 9.50, 4.80, 'NutriHealth', 'Global Health Dist', '2026-09-30', 105),
(119, 'Propranolol 10mg', 'Cardiovascular', 'PRO-10-2026A', 260, 14.00, 6.90, 'HeartCare Pharma', 'MedSupply Inc', '2027-02-14', 105),
(120, 'Aspirin 81mg', 'Painkillers', 'ASP-81-2027A', 1000, 5.50, 2.10, 'Everyday Health', 'National Med Dist', '2029-01-01', 101)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Note: Sales, Notifications, and Transfers are omitted from this script 
-- so you can use this rich dataset to manually create them in the UI.
