<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Message;
use App\Models\Notification;
class MessageController extends Controller
{
    private $messageModel;
    private $notificationModel;

    public function __construct()
    {
        $this->messageModel = new \App\Models\Message();
        $this->notificationModel = new Notification();
    }

    public function chat() {
        if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $senderId = $_SESSION['user']['user_id'];
        $receiverId = $_GET['receiver_id'] ?? null;

        if (!$receiverId) {
            die("Receiver ID is missing.");
        }

        $messages = $this->messageModel->getMessages($senderId, $receiverId);

        // Đánh dấu tin nhắn đã đọc
        $this->notificationModel->markAsRead($senderId);

        $this->view('message/chat', ['messages' => $messages, 'receiverId' => $receiverId]);
    }

    public function send()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $senderId = $_SESSION['user']['user_id'];
            $receiverId = $_POST['receiver_id'] ?? null;
            $messageText = $_POST['message'] ?? '';

            if (!$receiverId || empty($messageText)) {
                die("Missing message details.");
            }

            $this->messageModel->sendMessage($senderId, $receiverId, $messageText);
            $this->notificationModel->createNotification($receiverId, "You have a new message from user ID: $senderId");

            header("Location: ?url=message/chat&receiver_id=" . $receiverId);
            exit;
        }
    }
}