<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/email.php';

class UserController
{
    private $userModel;
    private $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
        $this->userModel = new User($pdo);
        AuthMiddleware::check();
        AuthMiddleware::requireRole(['manager']);
    }

    public function index()
    {
        $users = $this->userModel->getAll();
        sendSuccess($users);
    }

    public function show($id)
    {
        $user = $this->userModel->findById($id);
        if (!$user) {
            sendError('User not found', 404);
            return;
        }
        sendSuccess($user);
    }

    public function create()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? '';
        $branchId = $data['branch_id'] ?? null;
        $status = $data['status'] ?? 'active';

        if (empty($name) || empty($email) || empty($password) || empty($role)) {
            sendError('Name, email, password and role are required', 400);
            return;
        }
        if (!in_array($role, ['manager', 'pharmacist', 'store_keeper'])) {
            sendError('Invalid role', 400);
            return;
        }
        if ($this->userModel->findByEmail($email)) {
            sendError('Email already exists', 409);
            return;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $id = $this->userModel->create($name, $email, $hashed, $role, $branchId, $status);
        sendSuccess(['id' => $id], 'User created successfully');
    }

    public function invite()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = trim($data['email'] ?? '');
        $role = $data['role'] ?? '';
        $branchId = $data['branch_id'] ?? null;

        if (empty($email) || empty($role)) {
            sendError('Email and role are required', 400);
            return;
        }
        if (!in_array($role, ['manager', 'pharmacist', 'store_keeper'])) {
            sendError('Invalid role', 400);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendError('Invalid email format', 400);
            return;
        }
        if ($this->userModel->findByEmail($email)) {
            sendError('A user with this email already exists', 409);
            return;
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+7 days'));

        try {
            $stmt = $this->db->prepare("INSERT INTO invitations (email, token, role, branch_id, expires_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$email, $token, $role, $branchId, $expires]);
        } catch (PDOException $e) {
            sendError('Database error: ' . $e->getMessage(), 500);
            return;
        }

        $resetLink = "http://localhost/pharmacy-management-system/frontend/pages/auth/set-password.php?token=$token&email=" . urlencode($email);
        $subject = "Invitation to join BatiFlow Pharma";
        $message = "You have been invited as a $role. Click the link to set your password and activate your account:<br><a href='$resetLink'>$resetLink</a>";

        $emailSent = sendEmail($email, $subject, $message);
        if (!$emailSent) {
            // Log the link for manual retrieval
            $logFile = __DIR__ . '/../logs/invite.log';
            file_put_contents($logFile, $resetLink . PHP_EOL, FILE_APPEND);
            sendSuccess(null, 'Invitation saved but email failed. Link logged in backend/logs/invite.log');
        } else {
            sendSuccess(null, 'Invitation sent successfully');
        }
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? null;
        $role = $data['role'] ?? null;
        $branchId = $data['branch_id'] ?? null;
        $status = $data['status'] ?? null;

        $updated = $this->userModel->update($id, $name, $role, $branchId, $status);
        if ($updated) {
            sendSuccess(null, 'User updated successfully');
        } else {
            sendError('User not found or update failed', 404);
        }
    }

    public function delete($id)
    {
        $deleted = $this->userModel->delete($id);
        if ($deleted) {
            sendSuccess(null, 'User deleted successfully');
        } else {
            sendError('User not found', 404);
        }
    }

    public function activate($id)
    {
        $updated = $this->userModel->updateStatus($id, 'active');
        if ($updated) {
            sendSuccess(null, 'User activated');
        } else {
            sendError('User not found', 404);
        }
    }

    public function deactivate($id)
    {
        $updated = $this->userModel->updateStatus($id, 'inactive');
        if ($updated) {
            sendSuccess(null, 'User deactivated');
        } else {
            sendError('User not found', 404);
        }
    }
}
