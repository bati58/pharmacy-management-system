<?php
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class NotificationController
{
    private $notificationModel;

    public function __construct()
    {
        global $pdo;
        $this->notificationModel = new Notification($pdo);
        AuthMiddleware::check();
    }

    public function index()
    {
        $userId = $_SESSION['user_id'];
        $unreadOnly = $_GET['unread_only'] ?? false;
        $notifications = $this->notificationModel->getByUser($userId, $unreadOnly);
        sendSuccess($notifications);
    }

    public function markAsRead($id)
    {
        $updated = $this->notificationModel->markAsRead($id, $_SESSION['user_id']);
        if ($updated) {
            sendSuccess(null, 'Notification marked as read');
        } else {
            sendError('Notification not found or not yours', 404);
        }
    }

    public function markAllRead()
    {
        $this->notificationModel->markAllAsRead($_SESSION['user_id']);
        sendSuccess(null, 'All notifications marked as read');
    }
}
