<?php
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/alert.php';

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
        // Automatically check for expiring drugs whenever notifications are fetched (SRS §3.4)
        if ($_SESSION['role'] === 'manager' || $_SESSION['role'] === 'store_keeper') {
            checkAndNotifyExpiringDrugs();
        }
        
        $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
        $notifications = $this->notificationModel->getByUser($_SESSION['user_id'], $unreadOnly);
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
