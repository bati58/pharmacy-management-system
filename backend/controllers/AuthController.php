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
        if (!$user || !password_verify($password, $user['password'])) {
            sendError('Invalid credentials', 401);
            return;
        }

        if ($user['status'] !== 'active') {
            sendError('Your account is inactive. Contact manager.', 403);
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['branch_id'] = $user['branch_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];

        sendSuccess([
            'role' => $user['role'],
            'name' => $user['name'],
            'branch_id' => $user['branch_id']
        ], 'Login successful');
    }

    public function logout()
    {
        session_destroy();
        header('Location: ' . BASE_URL . '/frontend/pages/auth/login.php');
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
        $resetLink = BASE_URL . "/frontend/pages/auth/reset.html?token=$token&email=$email";

        $subject = "Reset your BatiFlow password";
        $message = "Click this link to reset your password: $resetLink";
        mail($email, $subject, $message);

        sendSuccess(null, 'Password reset link sent to your email');
    }

    public function register()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            sendError('Name, email and password are required', 400);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendError('Invalid email format', 400);
            return;
        }
        if (strlen($password) < 6) {
            sendError('Password must be at least 6 characters', 400);
            return;
        }

        $existingUser = $this->userModel->findByEmail($email);
        if ($existingUser) {
            sendError('Email already exists', 409);
            return;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $userId = $this->userModel->create($name, $email, $hashed, 'pharmacist', null, 'inactive');

        if ($userId) {
            sendSuccess(['id' => $userId], 'Account created successfully. Please wait for manager approval.');
        } else {
            sendError('Failed to create account', 500);
        }
    }

    /**
     * Activate an invitation – creates user account after user sets password
     */
    public function activateInvitation()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($token) || empty($email) || empty($password)) {
            sendError('Missing data', 400);
            return;
        }
        if (strlen($password) < 6) {
            sendError('Password must be at least 6 characters', 400);
            return;
        }

        // Validate invitation
        $stmt = $this->db->prepare("SELECT * FROM invitations WHERE token = ? AND email = ? AND used = 0 AND expires_at > NOW()");
        $stmt->execute([$token, $email]);
        $invite = $stmt->fetch();
        if (!$invite) {
            sendError('Invalid or expired invitation', 400);
            return;
        }

        // Create user (name = part of email before @)
        $name = explode('@', $email)[0];
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $userId = $this->userModel->create($name, $email, $hashed, $invite['role'], $invite['branch_id'], 'active');

        if ($userId) {
            // Mark invitation as used
            $stmt2 = $this->db->prepare("UPDATE invitations SET used = 1 WHERE id = ?");
            $stmt2->execute([$invite['id']]);
            sendSuccess(null, 'Account activated successfully. Please log in.');
        }
    }

    /**
     * Update user profile information
     */
    public function updateProfile()
    {
        if (!isset($_SESSION['user_id'])) {
            sendError('Not authenticated', 401);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            sendError('Name is required', 400);
            return;
        }

        $stmt = $this->db->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->execute([$name, $_SESSION['user_id']]);

        $_SESSION['name'] = $name;
        sendSuccess(null, 'Profile updated successfully');
    }

    /**
     * Change user password
     */
    public function changePassword()
    {
        if (!isset($_SESSION['user_id'])) {
            sendError('Not authenticated', 401);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            sendError('All fields are required', 400);
            return;
        }

        if (strlen($newPassword) < 6) {
            sendError('New password must be at least 6 characters', 400);
            return;
        }

        // Verify current password
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            sendError('Current password is incorrect', 400);
            return;
        }

        // Update to new password
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $_SESSION['user_id']]);

        sendSuccess(null, 'Password changed successfully');
    }
}
