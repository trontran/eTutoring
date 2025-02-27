<?php

namespace App\Models;
use App\Core\Database;
use PDO;

class Message
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getMessages($senderId, $receiverId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM Messages 
                                WHERE (sender_id = :sender AND receiver_id = :receiver) 
                                   OR (sender_id = :receiver AND receiver_id = :sender) 
                                ORDER BY sent_at ASC");
        $stmt->bindParam(':sender', $senderId, \PDO::PARAM_INT);
        $stmt->bindParam(':receiver', $receiverId, \PDO::PARAM_INT);
        $stmt->execute();

        $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Debug để kiểm tra xem có lấy được tin nhắn không
        if (empty($messages)) {
            error_log("No messages found for Sender: $senderId and Receiver: $receiverId");
        }

        return $messages;
    }

    public function sendMessage($senderId, $receiverId, $messageText)
    {
        $stmt = $this->db->prepare("INSERT INTO Messages (sender_id, receiver_id, message_text) 
                                    VALUES (:sender, :receiver, :message)");
        $stmt->bindParam(':sender', $senderId, \PDO::PARAM_INT);
        $stmt->bindParam(':receiver', $receiverId, \PDO::PARAM_INT);
        $stmt->bindParam(':message', $messageText, \PDO::PARAM_STR);
        $stmt->execute();

        return $this->db->lastInsertId();
    }

    public function getChatUsers($userId): array
    {
        $query = "SELECT u.user_id, u.first_name, u.last_name, u.email, 
                (SELECT message_text FROM Messages 
                 WHERE (sender_id = u.user_id AND receiver_id = :userId) 
                    OR (sender_id = :userId AND receiver_id = u.user_id) 
                 ORDER BY sent_at DESC LIMIT 1) AS last_message,
                
                (SELECT sent_at FROM Messages 
                 WHERE (sender_id = u.user_id AND receiver_id = :userId) 
                    OR (sender_id = :userId AND receiver_id = u.user_id) 
                 ORDER BY sent_at DESC LIMIT 1) AS last_message_time

              FROM Users u
              WHERE u.user_id IN 
                (SELECT DISTINCT sender_id FROM Messages WHERE receiver_id = :userId 
                 UNION 
                 SELECT DISTINCT receiver_id FROM Messages WHERE sender_id = :userId)
              ORDER BY last_message_time DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



}