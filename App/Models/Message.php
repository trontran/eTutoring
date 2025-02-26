<?php

namespace App\Models;
use App\Core\Database;

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
}