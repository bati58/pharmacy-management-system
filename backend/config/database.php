<?php

/**
 * Database configuration
 * Uses PDO for secure database interactions
 */

// Database credentials
$host = 'localhost';
$dbname = 'pharmacy_db';
$username = 'root';
$password = '';

// Optional: for production, use environment variables
// $host = getenv('DB_HOST') ?: 'localhost';
// $dbname = getenv('DB_NAME') ?: 'pharmacy_db';
// $username = getenv('DB_USER') ?: 'root';
// $password = getenv('DB_PASS') ?: '';

try {
    // Create PDO instance
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // Log error (in production, log to file instead of displaying)
    error_log("Database connection failed: " . $e->getMessage());

    // Return JSON error response (for API calls)
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error. Please try again later.'
    ]);
    exit();
}
