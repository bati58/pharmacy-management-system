<?php
/**
 * Database Reset Script for PharmaFlow
 * WARNING: This will drop your existing database and recreate it with fresh seed data.
 */

// Database credentials
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'pms_db';

echo "<h2>PharmaFlow Database Setup</h2>";
echo "<p>Initializing fresh database setup...</p>";

try {
    // 1. Connect to MySQL without database selection
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 2. Drop and Recreate Database
    echo "Dropping old database...<br>";
    $pdo->exec("DROP DATABASE IF EXISTS `$dbname`;");
    echo "Creating fresh database `$dbname`...<br>";
    $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `$dbname`;");

    // 3. Import Schema
    echo "Importing schema...<br>";
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    // Remove lines that create database if they exist in schema
    $schema = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/i', '', $schema);
    $schema = preg_replace('/USE `pms_db`;/i', '', $schema);
    $pdo->exec($schema);

    // 4. Import Seed
    echo "Importing professional seed data...<br>";
    $seed = file_get_contents(__DIR__ . '/database/seed.sql');
    $seed = preg_replace('/USE `pms_db`;/i', '', $seed);
    
    // Split into individual queries because exec() handles one multi-statement block but sometimes fails on specific characters
    // For safety, we can run as one if the file is clean
    $pdo->exec($seed);

    echo "<p style='color: green; font-weight: bold;'>Success! Database has been reset to PharmaFlow defaults.</p>";
    echo "<p>You can now login with:</p>";
    echo "<ul>
            <li><strong>Email:</strong> manager@pharmaflow.com</li>
            <li><strong>Password:</strong> Admin@123</li>
          </ul>";
    echo "<a href='frontend/pages/auth/login.php' style='padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 5px;'>Go to Login</a>";

} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>Error during setup:</p>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
