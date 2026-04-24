<?php
require_once __DIR__ . '/../backend/config/database.php';
try {
    $pdo->exec("ALTER TABLE invitations ADD COLUMN used TINYINT(1) DEFAULT 0 AFTER branch_id");
    echo "Column 'used' added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
