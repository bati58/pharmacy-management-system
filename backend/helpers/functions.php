<?php

/**
 * Format currency (USD)
 * @param float $amount
 * @return string
 */
function formatCurrency($amount)
{
    return '$' . number_format($amount, 2);
}

/**
 * Format date to readable format
 * @param string $date MySQL date (Y-m-d)
 * @param string $format Output format (default 'M d, Y')
 * @return string
 */
function formatDate($date, $format = 'M d, Y')
{
    return date($format, strtotime($date));
}

/**
 * Format datetime
 * @param string $datetime MySQL datetime
 * @return string
 */
function formatDateTime($datetime)
{
    return date('M d, Y H:i', strtotime($datetime));
}

/**
 * Generate a unique invoice number
 * @return string
 */
function generateInvoiceNo()
{
    return 'INV-' . strtoupper(uniqid());
}

/**
 * Generate a random password
 * @param int $length
 * @return string
 */
function generateRandomPassword($length = 10)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Check if a drug is expired or near expiry
 * @param string $expiryDate Y-m-d
 * @param int $warningDays Days before expiry to trigger warning
 * @return array ['status' => 'expired|expiring_soon|ok', 'days_left' => int]
 */
function checkExpiryStatus($expiryDate, $warningDays = 30)
{
    $today = new DateTime();
    $expiry = new DateTime($expiryDate);
    $diff = $today->diff($expiry);
    $daysLeft = $expiry > $today ? (int)$diff->format('%r%a') : -(int)$diff->format('%r%a');

    if ($daysLeft < 0) {
        return ['status' => 'expired', 'days_left' => $daysLeft];
    } elseif ($daysLeft <= $warningDays) {
        return ['status' => 'expiring_soon', 'days_left' => $daysLeft];
    } else {
        return ['status' => 'ok', 'days_left' => $daysLeft];
    }
}

/**
 * Get the current user's role from session
 * @return string|null
 */
function currentUserRole()
{
    session_start();
    return $_SESSION['role'] ?? null;
}

/**
 * Check if current user is manager
 * @return bool
 */
function isManager()
{
    return currentUserRole() === 'manager';
}

/**
 * Check if current user is pharmacist
 * @return bool
 */
function isPharmacist()
{
    return currentUserRole() === 'pharmacist';
}

/**
 * Check if current user is store keeper
 * @return bool
 */
function isStoreKeeper()
{
    return currentUserRole() === 'store_keeper';
}

/**
 * Log activity (simple file logging)
 * @param string $action
 * @param string $details
 */
function logActivity($action, $details = '')
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $user = $_SESSION['name'] ?? 'Guest';
    $logEntry = "[$timestamp] User: $user | Action: $action | Details: $details" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
