<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../helpers/response.php';

class AuthController
{
    private User $userModel;
    private PDO $db;
    private Notification $notificationModel;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
        $this->userModel = new User($pdo);
        $this->notificationModel = new Notification($pdo);
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

    public function activateInvitation()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? '';
        $password = $data['password'] ?? '';
        $name = trim($data['name'] ?? '');

        if (empty($token) || empty($password) || empty($name)) {
            sendError('All fields (Name, Password) are required', 400);
            return;
        }
        if (strlen($password) < 6) {
            sendError('Password must be at least 6 characters', 400);
            return;
        }

        // Validate token
        $stmt = $this->db->prepare("SELECT * FROM invitations WHERE token = ? AND status = 'pending' AND expires_at > NOW()");
        $stmt->execute([$token]);
        $invite = $stmt->fetch();
        
        if (!$invite) {
            sendError('Invalid, expired, or already used invitation', 400);
            return;
        }

        // Check if user already exists (shouldn't happen if model check was done, but safe to re-check)
        if ($this->userModel->findByEmail($invite['email'])) {
            sendError('A user with this email already exists', 409);
            return;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $userId = $this->userModel->create($name, $invite['email'], $hashed, $invite['role'], $invite['branch_id'], 'active');

        if ($userId) {
            // Mark invitation as accepted and used
            $stmt2 = $this->db->prepare("UPDATE invitations SET status = 'accepted', used = 1 WHERE id = ?");
            $stmt2->execute([$invite['id']]);
            
            // Notify managers
            $allUsers = $this->userModel->getAll();
            $message = "New user '{$name}' ({$invite['role']}) has successfully registered.";
            foreach ($allUsers as $user) {
                if ($user['role'] === 'manager' && $user['status'] === 'active') {
                    $this->notificationModel->create($user['id'], 'system', $message);
                }
            }

            sendSuccess(null, 'Account created and activated successfully. You can now log in.');
        } else {
            sendError('Failed to create account', 500);
        }
    }

    public function validateInvitation()
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            sendError('Token is required', 400);
            return;
        }

        $stmt = $this->db->prepare("SELECT email, role, expires_at FROM invitations WHERE token = ? AND status = 'pending' AND expires_at > NOW()");
        $stmt->execute([$token]);
        $invite = $stmt->fetch();

        if (!$invite) {
            sendError('Invalid or expired invitation', 404);
            return;
        }

        sendSuccess($invite);
    }

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

        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            sendError('Current password is incorrect', 400);
            return;
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $_SESSION['user_id']]);

        sendSuccess(null, 'Password changed successfully');
    }
}
