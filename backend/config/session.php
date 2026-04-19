<?php

/**
 * Session configuration and management
 * Handles session start, timeout, security
 */

// Ensure constants are loaded
if (!defined('SESSION_TIMEOUT')) {
    require_once __DIR__ . '/constants.php';
}

// Set session name (avoid conflicts with other apps)
session_name(SESSION_NAME);

// Secure session settings (for production with HTTPS)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check session timeout and destroy if expired
 * Call this function on every page that requires authentication
 */
function checkSessionTimeout()
{
    if (isset($_SESSION['last_activity'])) {
        $inactiveTime = time() - $_SESSION['last_activity'];
        if ($inactiveTime > SESSION_TIMEOUT) {
            // Session expired - destroy and redirect to login
            session_unset();
            session_destroy();

            // If this is an API request, return JSON error
            if (strpos($_SERVER['REQUEST_URI'], '/backend/') !== false) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
                exit();
            } else {
                // For frontend pages, redirect to login
                header('Location: /pharmacy-management-system/frontend/pages/auth/login.html');
                exit();
            }
        }
    }
    // Update last activity time
    $_SESSION['last_activity'] = time();
}

/**
 * Regenerate session ID to prevent fixation attacks
 * Call after successful login
 */
function regenerateSession()
{
    session_regenerate_id(true);
}

/**
 * Destroy session completely (logout)
 */
function destroySession()
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}

// If this file is included in an authenticated context, check timeout
// (You can call checkSessionTimeout() manually where needed instead of auto-running)
// To avoid auto-checking in every include, we don't call it here automatically.
