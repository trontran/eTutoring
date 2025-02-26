<?php

namespace App\Models;
use PDO;
use App\Core\Database;

class Notification
{
    private $db;

    public function __construct()
    {
        // Kiểm tra xem Database có tồn tại không
        if (!class_exists(Database::class)) {
            die("Lỗi: Lớp Database chưa được import đúng cách.");
        }

        $this->db = Database::getInstance()->getConnection();
    }


    public function createNotification($userId, $text): bool
    {
        $stmt = $this->db->prepare("INSERT INTO Notifications (user_id, notification_text) 
                                    VALUES (:user_id, :text)");
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':text', $text, \PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function getUnreadNotifications($userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM Notifications WHERE user_id = :userId AND status = 'unread'");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function markAsRead($userId): bool
    {
        $stmt = $this->db->prepare("UPDATE Notifications SET status = 'read' WHERE user_id = :userId AND status = 'unread'");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}