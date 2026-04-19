<?php

/**
 * Frontend Entry Point
 * Redirects to dashboard if user is logged in, otherwise to login page
 */
require_once __DIR__ . '/includes/init_session.php';

// Check if user is authenticated
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // Redirect to dashboard
    header('Location: pages/dashboard.php');
    exit;
} else {
    // Redirect to login page
    header('Location: pages/auth/login.php');
    exit;
}
