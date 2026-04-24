<?php
require_once 'backend/config/database.php';
$email = 'manager@batiflow.com';
$password = 'Admin@123';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    echo "User found: " . $user['name'] . "\n";
    echo "Stored hash: " . $user['password'] . "\n";
    if (password_verify($password, $user['password'])) {
        echo "Password verification: SUCCESS\n";
    } else {
        echo "Password verification: FAILED\n";
        // Check if it matches 'password'
        if (password_verify('password', $user['password'])) {
            echo "Matches password: 'password'\n";
        }
    }
} else {
    echo "User not found\n";
}
