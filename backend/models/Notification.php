<?php
class Notification
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getByUser($userId, $unreadOnly = false)
    {
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create($userId, $type, $message)
    {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, type, message, is_read, created_at) 
            VALUES (?, ?, ?, 0, NOW())
        ");
        return $stmt->execute([$userId, $type, $message]);
    }

    public function markAsRead($id, $userId)
    {
        $stmt = $this->db->prepare("
            UPDATE notifications SET is_read = 1 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$id, $userId]);
    }

    public function markAllAsRead($userId)
    {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public function deleteOld($days = 30)
    {
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        return $stmt->execute([$days]);
    }
}
