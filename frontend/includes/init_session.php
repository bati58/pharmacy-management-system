<?php
/**
 * Shared session bootstrap so API (backend) and pages share the same session cookie.
 */
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../../backend/config/database.php';

    $stmt = $pdo->prepare('SELECT role, branch_id, status, name FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user && $user['status'] === 'active') {
        $_SESSION['role'] = $user['role'];
        $_SESSION['branch_id'] = $user['branch_id'];
        $_SESSION['name'] = $user['name'];
    }
}
