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
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $role = $data['role'] ?? '';
        $branchId = $data['branch_id'] ?? null;

        if (empty($name) || empty($email) || empty($role)) {
            sendError('Name, email and role are required', 400);
            return;
        }
        if (!in_array($role, ['pharmacist', 'store_keeper'])) {
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
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, password, role, branch_id, status, invite_token, token_expiry)
                VALUES (?, ?, NULL, ?, ?, 'pending', ?, ?)
            ");
            $stmt->execute([$name, $email, $role, $branchId, $token, $expires]);
        } catch (PDOException $e) {
            sendError('Database error: ' . $e->getMessage(), 500);
            return;
        }

        $setupLink = "http://localhost/pharmacy-management-system/frontend/pages/auth/set-password.php?token=$token";
        $subject = "Welcome to BatiFlow - Set up your account";
        $message = "
            <html><body style='font-family:Arial,sans-serif;color:#111827;line-height:1.5;'>
                <h2 style='margin:0 0 12px;'>Welcome to BatiFlow Pharmacy Management System</h2>
                <p>Hello " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ",</p>
                <p>You have been invited to join <strong>BatiFlow</strong> as <strong>" . htmlspecialchars(str_replace('_', ' ', $role), ENT_QUOTES, 'UTF-8') . "</strong>.</p>
                <p>Please click the button below to set your password and activate your account.</p>
                <p style='margin:20px 0;'>
                    <a href='" . htmlspecialchars($setupLink, ENT_QUOTES, 'UTF-8') . "' style='background:#2563eb;color:#fff;text-decoration:none;padding:10px 16px;border-radius:6px;display:inline-block;'>Set Up Password</a>
                </p>
                <p>This invitation link expires in 24 hours.</p>
                <p>If the button does not work, copy and paste this URL:</p>
                <p><a href='" . htmlspecialchars($setupLink, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($setupLink, ENT_QUOTES, 'UTF-8') . "</a></p>
                <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                <p style='font-size:12px;color:#6b7280;'>If you were not expecting this invitation, please ignore this email.</p>
            </body></html>
        ";

        $emailSent = sendEmail($email, $subject, $message);
        if (!$emailSent) {
            $logFile = __DIR__ . '/../logs/invite.log';
            file_put_contents($logFile, $setupLink . PHP_EOL, FILE_APPEND);
            $emailError = getLastEmailError();
            sendError(
                'Invitation created but email delivery failed. ' .
                ($emailError !== '' ? $emailError . ' ' : '') .
                'Setup link logged in backend/logs/invite.log',
                500
            );
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
        try {
            $this->db->beginTransaction();

            // Delete dependent transactional rows first.
            $stmt = $this->db->prepare("SELECT id FROM sales WHERE pharmacist_id = ?");
            $stmt->execute([$id]);
            $saleIds = array_column($stmt->fetchAll(), 'id');
            if (!empty($saleIds)) {
                $in = implode(',', array_fill(0, count($saleIds), '?'));
                $stmtItems = $this->db->prepare("DELETE FROM sale_items WHERE sale_id IN ($in)");
                $stmtItems->execute($saleIds);

                $stmtSales = $this->db->prepare("DELETE FROM sales WHERE id IN ($in)");
                $stmtSales->execute($saleIds);
            }

            $stmt = $this->db->prepare("DELETE FROM transfers WHERE created_by = ?");
            $stmt->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM stock_movements WHERE user_id = ?");
            $stmt->execute([$id]);

            $deleted = $this->userModel->delete($id);
            if ($deleted) {
                $this->db->commit();
                sendSuccess(null, 'User deleted successfully (including related records).');
            } else {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                sendError('User not found', 404);
            }
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            sendError('Failed to delete user. ' . $e->getMessage(), 500);
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
