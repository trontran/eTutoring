<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Message;
use App\Models\Notification;
class   MessageController extends Controller
{
    private $messageModel;
    private $notificationModel;
    private $userModel;

    public function __construct()
    {
        $this->messageModel = new \App\Models\Message();
        $this->notificationModel = new Notification();
        $this->userModel = new \App\Models\User();
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
        $receiver = $this->userModel->getUserById($receiverId);

        $receiverName = $receiver ? $receiver['first_name'] . " " . $receiver['last_name'] : "Unknown";

        $this->view('message/chat', [
            'messages' => $messages,
            'receiverId' => $receiverId,
            'receiverName' => $receiverName
        ]);
        $this->view('message/chat', ['messages' => $messages, 'receiverId' => $receiverId]);
    }

//    public function send()
//    {
//        if (session_status() === PHP_SESSION_NONE) {
//            session_start();
//        }
//        if (!isset($_SESSION['user'])) {
//            header("Location: ?url=login");
//            exit;
//        }
//
//        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//            $senderId = $_SESSION['user']['user_id'];
//            $receiverId = $_POST['receiver_id'] ?? null;
//            $messageText = $_POST['message'] ?? '';
//
//            if (!$receiverId || empty($messageText)) {
//                die("Missing message details.");
//            }
//
//            $this->messageModel->sendMessage($senderId, $receiverId, $messageText);
//            $this->notificationModel->createNotification($receiverId, "You have a new message from user ID: $senderId");
//
//            header("Location: ?url=message/chat&receiver_id=" . $receiverId);
//            exit;
//        }
//    }

    public function chatList()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $chatUsers = $this->messageModel->getChatUsers($userId);

        $this->view('message/chat_list', ['chatUsers' => $chatUsers]);
    }

//    test chat realtime
    public function send()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            echo json_encode(["status" => "error", "message" => "User not logged in"]);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $senderId = $_SESSION['user']['user_id'];
            $receiverId = $_POST['receiver_id'] ?? null;
            $messageText = $_POST['message'] ?? '';

            if (!$receiverId || empty($messageText)) {
                echo json_encode(["status" => "error", "message" => "Missing message details"]);
                exit;
            }

            // Gọi Model để lưu tin nhắn
            $messageModel = new \App\Models\Message();
            $messageId = $messageModel->sendMessage($senderId, $receiverId, $messageText);

            if ($messageId) {
                echo json_encode(["status" => "success", "message" => "Message sent successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to send message"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid request"]);
        }
        exit;
    }
//
    public function getMessages()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            echo json_encode(["status" => "error", "message" => "User not logged in"]);
            exit;
        }

        $senderId = $_SESSION['user']['user_id'];
        $receiverId = $_GET['receiver_id'] ?? null;
        $lastMessageId = $_GET['last_message_id'] ?? 0;

        if (!$receiverId) {
            echo json_encode(["status" => "error", "message" => "Receiver ID missing"]);
            exit;
        }

        $messageModel = new \App\Models\Message();
        $messages = $messageModel->getNewMessages($senderId, $receiverId, $lastMessageId);

        echo json_encode(["status" => "success", "messages" => $messages]);
        exit;
    }
//    test chat realtime



}