-- ======================================================
-- Safe extra seed (idempotent)
-- Import this on an existing database without overwriting data.
-- ======================================================

USE `pharmacy_db`;

-- ======================================================
-- 1) Extra branches (insert only if not present)
-- ======================================================
INSERT INTO `branches` (`name`, `address`, `phone`)
SELECT 'Southgate Branch', '901 Lake Road, Southgate', '+1-555-0105'
WHERE NOT EXISTS (
    SELECT 1 FROM `branches` WHERE `name` = 'Southgate Branch'
);

INSERT INTO `branches` (`name`, `address`, `phone`)
SELECT 'Airport Branch', '44 Terminal Avenue, Airport District', '+1-555-0106'
WHERE NOT EXISTS (
    SELECT 1 FROM `branches` WHERE `name` = 'Airport Branch'
);

-- ======================================================
-- 2) Extra users (safe by unique email)
-- Password hash is for Admin@123
-- ======================================================
INSERT INTO `users` (`name`, `email`, `password`, `role`, `branch_id`, `status`, `invite_token`, `token_expiry`)
SELECT
    'selam.south',
    'pharmacist.south@batiflow.com',
    '$2y$10$CdO2uoY7SB8jG3YV0OrufeRnFEtFNqzTmRqVo5fPArva2Fwtotfp.',
    'pharmacist',
    b.id,
    'active',
    NULL,
    NULL
FROM `branches` b
WHERE b.name = 'Southgate Branch'
  AND NOT EXISTS (
      SELECT 1 FROM `users` u WHERE u.email = 'pharmacist.south@batiflow.com'
  );

INSERT INTO `users` (`name`, `email`, `password`, `role`, `branch_id`, `status`, `invite_token`, `token_expiry`)
SELECT
    'dawit.south',
    'storekeeper.south@batiflow.com',
    '$2y$10$CdO2uoY7SB8jG3YV0OrufeRnFEtFNqzTmRqVo5fPArva2Fwtotfp.',
    'store_keeper',
    b.id,
    'active',
    NULL,
    NULL
FROM `branches` b
WHERE b.name = 'Southgate Branch'
  AND NOT EXISTS (
      SELECT 1 FROM `users` u WHERE u.email = 'storekeeper.south@batiflow.com'
  );

INSERT INTO `users` (`name`, `email`, `password`, `role`, `branch_id`, `status`, `invite_token`, `token_expiry`)
SELECT
    'pending.airport',
    'pending.airport@batiflow.com',
    NULL,
    'pharmacist',
    b.id,
    'pending',
    'seedextra_8d9f1a2b3c4d5e6f77889900aabbccddeeff00112233445566778899aabb',
    DATE_ADD(NOW(), INTERVAL 24 HOUR)
FROM `branches` b
WHERE b.name = 'Airport Branch'
  AND NOT EXISTS (
      SELECT 1 FROM `users` u WHERE u.email = 'pending.airport@batiflow.com'
  );

-- ======================================================
-- 3) Extra drugs (safe by unique batch values)
-- ======================================================
INSERT INTO `drugs` (`name`, `category`, `manufacturer`, `supplier`, `batch`, `stock`, `price`, `expiry_date`, `branch_id`)
SELECT
    'Amlodipine 5mg',
    'Cardiovascular',
    'CardioLife',
    'South Medical Traders',
    'AML-SOUTH-2026-001',
    180,
    8.40,
    '2027-10-10',
    b.id
FROM `branches` b
WHERE b.name = 'Southgate Branch'
  AND NOT EXISTS (
      SELECT 1 FROM `drugs` d WHERE d.batch = 'AML-SOUTH-2026-001'
  );

INSERT INTO `drugs` (`name`, `category`, `manufacturer`, `supplier`, `batch`, `stock`, `price`, `expiry_date`, `branch_id`)
SELECT
    'Zinc Sulfate 20mg',
    'Supplement',
    'NutriLife',
    'South Medical Traders',
    'ZINC-SOUTH-2026-002',
    240,
    3.60,
    '2028-01-15',
    b.id
FROM `branches` b
WHERE b.name = 'Southgate Branch'
  AND NOT EXISTS (
      SELECT 1 FROM `drugs` d WHERE d.batch = 'ZINC-SOUTH-2026-002'
  );

INSERT INTO `drugs` (`name`, `category`, `manufacturer`, `supplier`, `batch`, `stock`, `price`, `expiry_date`, `branch_id`)
SELECT
    'Insulin Aspart',
    'Diabetes',
    'GlucoHealth',
    'Airport Pharma Supply',
    'INS-AIR-2026-011',
    16,
    48.00,
    '2026-11-20',
    b.id
FROM `branches` b
WHERE b.name = 'Airport Branch'
  AND NOT EXISTS (
      SELECT 1 FROM `drugs` d WHERE d.batch = 'INS-AIR-2026-011'
  );

INSERT INTO `drugs` (`name`, `category`, `manufacturer`, `supplier`, `batch`, `stock`, `price`, `expiry_date`, `branch_id`)
SELECT
    'ORS Sachet',
    'General',
    'LifeCare',
    'Airport Pharma Supply',
    'ORS-AIR-2026-021',
    300,
    1.10,
    '2028-03-01',
    b.id
FROM `branches` b
WHERE b.name = 'Airport Branch'
  AND NOT EXISTS (
      SELECT 1 FROM `drugs` d WHERE d.batch = 'ORS-AIR-2026-021'
  );

-- ======================================================
-- 4) Extra sales + items (safe by invoice_no uniqueness)
-- ======================================================
INSERT INTO `sales` (`invoice_no`, `customer_name`, `total_amount`, `discount_amount`, `payment_method`, `prescription_reference`, `pharmacist_id`, `branch_id`, `sale_date`)
SELECT
    'INV-EXTRA-0001',
    'South Demo Customer',
    20.40,
    0.00,
    'Cash',
    NULL,
    u.id,
    u.branch_id,
    DATE_SUB(NOW(), INTERVAL 2 DAY)
FROM `users` u
WHERE u.email = 'pharmacist.south@batiflow.com'
  AND NOT EXISTS (
      SELECT 1 FROM `sales` s WHERE s.invoice_no = 'INV-EXTRA-0001'
  );

INSERT INTO `sales` (`invoice_no`, `customer_name`, `total_amount`, `discount_amount`, `payment_method`, `prescription_reference`, `pharmacist_id`, `branch_id`, `sale_date`)
SELECT
    'INV-EXTRA-0002',
    'Airport Walk-in',
    49.10,
    2.50,
    'Card',
    'RX-AIR-110',
    u.id,
    u.branch_id,
    DATE_SUB(NOW(), INTERVAL 1 DAY)
FROM `users` u
WHERE u.email = 'pharmacist.east@batiflow.com'
  AND NOT EXISTS (
      SELECT 1 FROM `sales` s WHERE s.invoice_no = 'INV-EXTRA-0002'
  );

INSERT INTO `sale_items` (`sale_id`, `drug_id`, `quantity`, `price`)
SELECT s.id, d.id, 2, 8.40
FROM `sales` s
JOIN `drugs` d ON d.batch = 'AML-SOUTH-2026-001'
WHERE s.invoice_no = 'INV-EXTRA-0001'
  AND NOT EXISTS (
      SELECT 1 FROM `sale_items` si WHERE si.sale_id = s.id AND si.drug_id = d.id
  );

INSERT INTO `sale_items` (`sale_id`, `drug_id`, `quantity`, `price`)
SELECT s.id, d.id, 1, 3.60
FROM `sales` s
JOIN `drugs` d ON d.batch = 'ZINC-SOUTH-2026-002'
WHERE s.invoice_no = 'INV-EXTRA-0001'
  AND NOT EXISTS (
      SELECT 1 FROM `sale_items` si WHERE si.sale_id = s.id AND si.drug_id = d.id
  );

INSERT INTO `sale_items` (`sale_id`, `drug_id`, `quantity`, `price`)
SELECT s.id, d.id, 1, 48.00
FROM `sales` s
JOIN `drugs` d ON d.batch = 'INS-AIR-2026-011'
WHERE s.invoice_no = 'INV-EXTRA-0002'
  AND NOT EXISTS (
      SELECT 1 FROM `sale_items` si WHERE si.sale_id = s.id AND si.drug_id = d.id
  );

INSERT INTO `sale_items` (`sale_id`, `drug_id`, `quantity`, `price`)
SELECT s.id, d.id, 3, 1.20
FROM `sales` s
JOIN `drugs` d ON d.batch = 'ORS-AIR-2026-021'
WHERE s.invoice_no = 'INV-EXTRA-0002'
  AND NOT EXISTS (
      SELECT 1 FROM `sale_items` si WHERE si.sale_id = s.id AND si.drug_id = d.id
  );

-- ======================================================
-- 5) Extra transfer (safe by exact match check)
-- ======================================================
INSERT INTO `transfers` (`drug_id`, `quantity`, `from_location`, `to_location`, `branch_id`, `created_by`, `status`, `transfer_date`)
SELECT
    d.id,
    6,
    'store',
    'dispensary',
    d.branch_id,
    u.id,
    'completed',
    DATE_SUB(NOW(), INTERVAL 18 HOUR)
FROM `drugs` d
JOIN `users` u ON u.email = 'storekeeper.south@batiflow.com' AND u.branch_id = d.branch_id
WHERE d.batch = 'AML-SOUTH-2026-001'
  AND NOT EXISTS (
      SELECT 1 FROM `transfers` t
      WHERE t.drug_id = d.id
        AND t.quantity = 6
        AND t.from_location = 'store'
        AND t.to_location = 'dispensary'
  );

-- ======================================================
-- 6) Extra notifications (safe by message uniqueness)
-- ======================================================
INSERT INTO `notifications` (`user_id`, `type`, `message`, `is_read`, `created_at`)
SELECT
    u.id,
    'low_stock',
    'Insulin Aspart stock is low at Airport Branch.',
    0,
    DATE_SUB(NOW(), INTERVAL 2 HOUR)
FROM `users` u
WHERE u.email = 'manager@batiflow.com'
  AND NOT EXISTS (
      SELECT 1 FROM `notifications` n WHERE n.message = 'Insulin Aspart stock is low at Airport Branch.' AND n.user_id = u.id
  );

INSERT INTO `notifications` (`user_id`, `type`, `message`, `is_read`, `created_at`)
SELECT
    u.id,
    'system',
    'Southgate branch inventory has been initialized.',
    0,
    DATE_SUB(NOW(), INTERVAL 4 HOUR)
FROM `users` u
WHERE u.email = 'storekeeper.south@batiflow.com'
  AND NOT EXISTS (
      SELECT 1 FROM `notifications` n WHERE n.message = 'Southgate branch inventory has been initialized.' AND n.user_id = u.id
  );

-- ======================================================
-- 7) Extra stock movement logs (safe by unique reason + user + drug)
-- ======================================================
INSERT INTO `stock_movements` (`drug_id`, `quantity_change`, `reason`, `user_id`, `created_at`)
SELECT
    d.id,
    -2,
    'sale:INV-EXTRA-0001',
    u.id,
    DATE_SUB(NOW(), INTERVAL 2 DAY)
FROM `drugs` d
JOIN `users` u ON u.email = 'pharmacist.south@batiflow.com'
WHERE d.batch = 'AML-SOUTH-2026-001'
  AND NOT EXISTS (
      SELECT 1 FROM `stock_movements` sm
      WHERE sm.drug_id = d.id
        AND sm.user_id = u.id
        AND sm.reason = 'sale:INV-EXTRA-0001'
  );

INSERT INTO `stock_movements` (`drug_id`, `quantity_change`, `reason`, `user_id`, `created_at`)
SELECT
    d.id,
    -6,
    'transfer:store_to_dispensary',
    u.id,
    DATE_SUB(NOW(), INTERVAL 18 HOUR)
FROM `drugs` d
JOIN `users` u ON u.email = 'storekeeper.south@batiflow.com'
WHERE d.batch = 'AML-SOUTH-2026-001'
  AND NOT EXISTS (
      SELECT 1 FROM `stock_movements` sm
      WHERE sm.drug_id = d.id
        AND sm.user_id = u.id
        AND sm.reason = 'transfer:store_to_dispensary'
  );
