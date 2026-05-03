<?php
require_once __DIR__ . '/AuthMiddleware.php';

class RoleMiddleware
{
    /**
     * Allowed roles: manager, pharmacist, store_keeper
     * Usage: RoleMiddleware::allow(['manager', 'pharmacist']);
     */
    public static function allow($roles)
    {
        AuthMiddleware::check();
        $roles = is_array($roles) ? $roles : [$roles];
        if (!in_array($_SESSION['role'], $roles)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied. You do not have permission for this action.']);
            exit();
        }
        return true;
    }

    /**
     * Deny access to specific roles (opposite of allow)
     */
    public static function deny($roles)
    {
        AuthMiddleware::check();
        $roles = is_array($roles) ? $roles : [$roles];
        if (in_array($_SESSION['role'], $roles)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied. This action is not allowed for your role.']);
            exit();
        }
        return true;
    }

    /**
     * Check if current user is manager (full access)
     */
    public static function isManager()
    {
        return self::allow('manager');
    }

    /**
     * Check if current user is pharmacist
     */
    public static function isPharmacist()
    {
        return self::allow('pharmacist');
    }

    /**
     * Check if current user is store keeper
     */
    public static function isStoreKeeper()
    {
        return self::allow('store_keeper');
    }
}
