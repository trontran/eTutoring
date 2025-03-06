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

    // Tạo thông báo mới, trạng thái mặc định của cột status là 'unread' theo định nghĩa bảng
    public function createNotification($userId, $text): bool
    {
        $stmt = $this->db->prepare("INSERT INTO Notifications (user_id, notification_text) 
                                    VALUES (:user_id, :text)");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':text', $text, PDO::PARAM_STR);
        return $stmt->execute();
    }

    // Lấy danh sách thông báo chưa đọc của người dùng
    public function getUnreadNotifications($userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM Notifications 
                                    WHERE user_id = :userId AND status = 'unread' 
                                    ORDER BY created_at DESC");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Đánh dấu tất cả thông báo chưa đọc của người dùng thành 'read'
    public function markAsRead($userId): bool
    {
        $stmt = $this->db->prepare("UPDATE Notifications 
                                    SET status = 'read' 
                                    WHERE user_id = :userId AND status = 'unread'");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    //

    /**
     * Get all notifications for a user
     *
     * @param int $userId User ID
     * @return array All notifications for the user
     */
    public function getAllNotificationsForUser($userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM Notifications 
                                WHERE user_id = :userId 
                                ORDER BY created_at DESC");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mark a specific notification as read
     *
     * @param int $notificationId Notification ID
     * @return bool True if successful, false otherwise
     */
    public function markNotificationAsRead($notificationId): bool
    {
        $stmt = $this->db->prepare("UPDATE Notifications 
                                SET status = 'read' 
                                WHERE notification_id = :notificationId");
        $stmt->bindParam(':notificationId', $notificationId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}