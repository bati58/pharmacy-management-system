<?php
require_once 'backend/config/database.php';

$hash = password_hash('Admin@123', PASSWORD_DEFAULT);
$emails = ['manager@batiflow.com', 'pharmacist@batiflow.com', 'storekeeper@batiflow.com'];

foreach ($emails as $email) {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hash, $email]);
    echo "Updated $email with hash: $hash\n";
}
