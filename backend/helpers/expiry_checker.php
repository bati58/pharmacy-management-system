<?php

/**
 * Cron job script: Check for expiring drugs and low stock,
 * create notifications, and optionally send emails.
 * 
 * Run this script daily via cron (e.g., every morning at 8 AM):
 * 0 8 * * * php /path/to/xampp/htdocs/pharmacy-management-system/backend/helpers/expiry_checker.php
 * 
 * For Windows Task Scheduler, use:
 * C:\xampp\php\php.exe C:\xampp\htdocs\pharmacy-management-system\backend\helpers\expiry_checker.php
 */

// Load required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Drug.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/email.php';

// Initialize models
$drugModel = new Drug($pdo);
$notificationModel = new Notification($pdo);
$userModel = new User($pdo);

// Get all active users (we'll create notifications for relevant roles)
$allUsers = $userModel->getAll();

// Counters for summary
$lowStockCount = 0;
$expiringCount = 0;

// ========== 1. CHECK LOW STOCK ==========
$lowStockThreshold = 10; // units
$lowStockDrugs = $drugModel->getLowStock($lowStockThreshold);

if (!empty($lowStockDrugs)) {
    $lowStockCount = count($lowStockDrugs);
    foreach ($lowStockDrugs as $drug) {
        $message = "{$drug['name']} (Batch: {$drug['batch']}) has only {$drug['stock']} units remaining at {$drug['branch_name']}.";

        // Notify store keepers and managers
        foreach ($allUsers as $user) {
            if (in_array($user['role'], ['manager', 'store_keeper']) && $user['status'] === 'active') {
                // Avoid duplicate notifications for same drug (optional: check last 24 hours)
                $notificationModel->create($user['id'], 'low_stock', $message);
            }
        }

        // Send email to all managers
        $managers = array_filter($allUsers, function ($u) {
            return $u['role'] === 'manager' && $u['status'] === 'active';
        });
        foreach ($managers as $manager) {
            sendLowStockAlert($manager['email'], [$drug]);
        }
    }
}

// ========== 2. CHECK EXPIRING DRUGS ==========
$expiryWarningDays = 30;
$expiringDrugs = $drugModel->getExpiringSoon($expiryWarningDays);

if (!empty($expiringDrugs)) {
    $expiringCount = count($expiringDrugs);
    foreach ($expiringDrugs as $drug) {
        $expiryDate = formatDate($drug['expiry_date']);
        $message = "{$drug['name']} (Batch: {$drug['batch']}) expires on {$expiryDate} at {$drug['branch_name']}.";

        // Notify managers and store keepers
        foreach ($allUsers as $user) {
            if (in_array($user['role'], ['manager', 'store_keeper']) && $user['status'] === 'active') {
                $notificationModel->create($user['id'], 'expiry', $message);
            }
        }

        // Send email to all managers
        $managers = array_filter($allUsers, function ($u) {
            return $u['role'] === 'manager' && $u['status'] === 'active';
        });
        foreach ($managers as $manager) {
            sendExpiryAlert($manager['email'], [$drug]);
        }
    }
}

// ========== 3. CLEANUP OLD NOTIFICATIONS ==========
$deleted = $notificationModel->deleteOld(30); // keep only last 30 days

// ========== 4. LOG EXECUTION ==========
$logMessage = "Expiry checker executed. Low stock: $lowStockCount, Expiring soon: $expiringCount, Old notifications deleted: " . ($deleted ? 'yes' : 'none');
logActivity('Expiry Checker', $logMessage);

// Output for cron log (optional)
echo date('Y-m-d H:i:s') . " - " . $logMessage . PHP_EOL;
