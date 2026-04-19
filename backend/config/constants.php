<?php

/**
 * Application constants
 * Define roles, paths, limits, and other global settings
 */

// ========== USER ROLES ==========
define('ROLE_MANAGER', 'manager');
define('ROLE_PHARMACIST', 'pharmacist');
define('ROLE_STORE_KEEPER', 'store_keeper');

// All roles as array
define('ROLES', [ROLE_MANAGER, ROLE_PHARMACIST, ROLE_STORE_KEEPER]);

// ========== STOCK & EXPIRY ==========
define('LOW_STOCK_THRESHOLD', 10);        // units
define('EXPIRY_WARNING_DAYS', 30);        // days before expiry to alert
define('MAX_STOCK_QUANTITY', 10000);      // safety limit

// ========== FILE UPLOADS ==========
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);

// ========== SESSION ==========
define('SESSION_TIMEOUT', 7200);          // 2 hours (in seconds)
define('SESSION_NAME', 'batiflow_session');

// ========== PAGINATION ==========
define('ITEMS_PER_PAGE', 20);

// ========== DATE FORMATS ==========
define('DATE_FORMAT_DISPLAY', 'M d, Y');
define('DATE_FORMAT_DB', 'Y-m-d');
define('DATETIME_FORMAT_DISPLAY', 'M d, Y H:i');

// ========== CURRENCY ==========
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_CODE', 'USD');

// ========== EMAIL ==========
define('FROM_EMAIL', 'noreply@batiflow.com');
define('FROM_NAME', 'BatiFlow Pharma System');

// ========== SYSTEM PATHS ==========
define('BASE_URL', 'http://localhost/pharmacy-management-system');
define('API_BASE_URL', BASE_URL . '/backend');

// ========== ERROR LOGGING ==========
define('LOG_ERRORS', true);
define('ERROR_LOG_PATH', __DIR__ . '/../logs/errors.log');
