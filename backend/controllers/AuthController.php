<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/response.php';
// session_start() is already started in backend/index.php

class AuthController
{
    private $userModel;
    private $db; // optional, but useful for direct queries if needed

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
        require_once __DIR__ . '/../helpers/email.php';

        $data = json_decode(file_get_contents('php://input'), true);
        $email = trim($data['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendError('A valid email is required', 400);
            return;
        }

        $user = $this->userModel->findByEmail($email);
        // Always return success to avoid user enumeration issues, even if email doesn't exist. The reset link will only be sent if the email is valid and exists.
        if (!$user) {
            sendSuccess(null, 'If this email exists in our system, a reset link has been sent.');
            return;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+2 hours'));

        // Delete any existing tokens for this email
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);

        // Save new token
        $stmt = $this->db->prepare(
            "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)"
        );
        $stmt->execute([$email, $token, $expiresAt]);

        // Build the base URL dynamically from current request
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') .
            '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $scriptDir = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/pharmacy-management-system/backend/index.php'));
        $resetLink = $baseUrl . $scriptDir . '/frontend/pages/auth/reset-password.php?token=' . $token . '&email=' . urlencode($email);
        $subject = "Reset Your PharmaFlow System Password";
        $message = "
            <html><body style='font-family:Arial,sans-serif;color:#111827;line-height:1.5;'>
                <h2 style='margin:0 0 12px;'>Password Reset Request</h2>
                <p>Hello " . htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . ",</p>
                <p>We received a request to reset your password. Click the button below to set a new password.</p>
                <p style='margin:20px 0;'>
                    <a href='" . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . "' 
                       style='background:#2563eb;color:#fff;text-decoration:none;padding:10px 16px;border-radius:6px;display:inline-block;'>
                        Reset Password
                    </a>
                </p>
                <p>This link expires in <strong>1 hour</strong>.</p>
                <p>If you did not request a password reset, please ignore this email.</p>
                <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                <p style='font-size:12px;color:#6b7280;'>PharmaFlow System</p>
            </body></html>
        ";

        $emailSent = sendEmail($email, $subject, $message);

        if (!$emailSent) {
            // Log the reset link so admin can share it manually
            $logFile = __DIR__ . '/../logs/reset.log';
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] $email => $resetLink" . PHP_EOL, FILE_APPEND);
            $emailError = getLastEmailError();
            sendSuccess(
                ['link' => $resetLink],
                'Reset link created but email delivery failed. Link logged in backend/logs/reset.log. ' . $emailError
            );
            return;
        }

        sendSuccess(null, 'A password reset link has been sent to your email. It expires in 1 hour.');
    }

    /**
     * Confirm password reset — validates token and sets new password
     */
    public function confirmReset()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $token = trim($data['token'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($token) || empty($email) || empty($password)) {
            sendError('Token, email and new password are required', 400);
            return;
        }
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            sendError('Password must be at least 8 characters and include letters and numbers', 400);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT id, expires_at FROM password_resets
            WHERE email = ? AND token = ?
            LIMIT 1
        ");
        $stmt->execute([$email, $token]);
        $record = $stmt->fetch();

        if (!$record) {
            sendError('Invalid or expired reset link. Please request a new one.', 400);
            return;
        }
        if (strtotime($record['expires_at']) < time()) {
            sendError('This reset link has expired. Please request a new one.', 400);
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            sendError('User not found', 404);
            return;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt2 = $this->db->prepare("UPDATE users SET password = ? WHERE email = ?");
        $ok = $stmt2->execute([$hashed, $email]);

        if ($ok && $stmt2->rowCount() > 0) {
            // Delete used token
            $this->db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
            sendSuccess(null, 'Password reset successfully. You can now log in.');
        } else {
            sendError('Failed to update password', 500);
        }
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
            // Mark the audit record in invitations table as used
            $stmt3 = $this->db->prepare("UPDATE invitations SET used = 1 WHERE token = ?");
            $stmt3->execute([$token]);
            
            sendSuccess(null, 'Account activated successfully. Please log in.');
        } else {
            sendError('Failed to activate account', 500);
        }
    }
}
