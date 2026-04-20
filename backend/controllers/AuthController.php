<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/response.php';
// session_start() is already started in backend/index.php

class AuthController
{
    private $userModel;
    private $db; // optional, but useful for direct queries

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
        $this->userModel = new User($pdo);
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            sendError('Email and password are required', 400);
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || empty($user['password']) || !password_verify($password, $user['password'])) {
            sendError('Invalid credentials', 401);
            return;
        }

        if ($user['status'] !== 'active') {
            if ($user['status'] === 'pending') {
                sendError('Your account is pending activation. Please check your invitation email.', 403);
                return;
            }
            sendError('Your account is inactive. Contact manager.', 403);
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['branch_id'] = $user['branch_id'];
        $_SESSION['name'] = $user['name'];

        sendSuccess([
            'role' => $user['role'],
            'name' => $user['name'],
            'branch_id' => $user['branch_id']
        ], 'Login successful');
    }

    public function logout()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $isJson = (strpos($accept, 'application/json') !== false)
            || ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';

        if ($isJson) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Logged out', 'data' => null]);
            exit;
        }

        header('Location: /pharmacy-management-system/frontend/pages/auth/login.php');
        exit;
    }

    public function resetPassword()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';

        if (empty($email)) {
            sendError('Email is required', 400);
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            sendError('Email not found', 404);
            return;
        }

        $token = bin2hex(random_bytes(32));
        $resetLink = "http://localhost/pharmacy-management-system/frontend/pages/auth/reset.html?token=$token&email=$email";

        $subject = "Reset your BatiFlow password";
        $message = "Click this link to reset your password: $resetLink";
        mail($email, $subject, $message);

        sendSuccess(null, 'Password reset link sent to your email');
    }

    public function register()
    {
        sendError('Public registration is disabled. Contact your manager for an invitation.', 403);
    }

    /**
     * Activate an invitation – creates user account after user sets password
     */
    public function activateInvitation()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($token) || empty($password)) {
            sendError('Missing data', 400);
            return;
        }
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            sendError('Password must be at least 8 characters and include letters and numbers', 400);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT id, status, token_expiry
            FROM users
            WHERE invite_token = ?
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            sendError('Invalid or expired invitation', 400);
            return;
        }
        if ($user['status'] !== 'pending') {
            sendError('This invitation has already been used.', 400);
            return;
        }
        if (empty($user['token_expiry']) || strtotime($user['token_expiry']) < time()) {
            sendError('This invitation link has expired. Contact your manager for a new invitation.', 400);
            return;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt2 = $this->db->prepare("
            UPDATE users
            SET password = ?, status = 'active', invite_token = NULL, token_expiry = NULL
            WHERE id = ? AND status = 'pending'
        ");
        $ok = $stmt2->execute([$hashed, $user['id']]);

        if ($ok && $stmt2->rowCount() > 0) {
            sendSuccess(null, 'Account activated successfully. Please log in.');
        } else {
            sendError('Failed to activate account', 500);
        }
    }
}
