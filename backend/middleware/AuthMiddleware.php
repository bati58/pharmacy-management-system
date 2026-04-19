<?php
class AuthMiddleware
{
    /**
     * Check if user is logged in (starts session if not already active)
     */
    public static function check()
    {
        // Start session only if not already active (no warnings)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
            exit();
        }
        return true;
    }

    /**
     * Check role requirement
     */
    public static function requireRole($roles = null)
    {
        self::check();

        if ($roles === null) {
            return true;
        }

        $roles = is_array($roles) ? $roles : [$roles];
        if (!in_array($_SESSION['role'], $roles)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden: Insufficient permissions.']);
            exit();
        }
        return true;
    }

    /**
     * Get current user info
     */
    public static function currentUser()
    {
        self::check();
        return [
            'id' => $_SESSION['user_id'],
            'role' => $_SESSION['role'],
            'branch_id' => $_SESSION['branch_id'] ?? null,
            'name' => $_SESSION['name'] ?? ''
        ];
    }
}
