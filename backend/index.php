<?php

/**
 * Front Controller - Entry point for all API requests
 * Routes requests to appropriate controllers
 */

if (!ob_get_level()) {
    ob_start();
}

// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON content type for all responses
header('Content-Type: application/json');

// Handle CORS (for development; restrict in production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Session cookie visible to frontend and API under same host
ini_set('session.cookie_path', '/');
ini_set('session.cookie_samesite', 'Lax');

// Start session (needed for authentication) – only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/helpers/response.php';

// Load routes
require_once __DIR__ . '/routes/web.php';

// Get request URI and method
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Route the request
route($requestUri, $requestMethod);
