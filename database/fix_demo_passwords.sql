USE pharmacy_db;
-- Password for all demo accounts: Admin@123
UPDATE users SET password = '$2y$10$CdO2uoY7SB8jG3YV0OrufeRnFEtFNqzTmRqVo5fPArva2Fwtotfp.' WHERE email IN (
  'manager@batiflow.com',
  'pharmacist@batiflow.com',
  'storekeeper@batiflow.com'
);
