<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Message;
use App\Models\Notification;

class MessageController extends Controller
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

        // Khi người dùng mở cửa sổ chat, đánh dấu các thông báo của chính họ đã đọc
        // (Nếu bạn muốn chỉ đánh dấu thông báo liên quan đến cuộc hội thoại cụ thể thì cần chỉnh sửa thêm)
        $this->notificationModel->markAsRead($senderId);

        $receiver = $this->userModel->getUserById($receiverId);
        $receiverName = $receiver ? $receiver['first_name'] . " " . $receiver['last_name'] : "Unknown";

        $this->view('message/chat', [
            'messages' => $messages,
            'receiverId' => $receiverId,
            'receiverName' => $receiverName
        ]);
        // Lưu ý: Dòng dưới có vẻ thừa nếu đã gọi view bên trên, bạn có thể bỏ đi nếu không cần thiết
        $this->view('message/chat', ['messages' => $messages, 'receiverId' => $receiverId]);
    }

    // Phương thức send() cho chat realtime, tích hợp tạo thông báo mới sau khi gửi tin nhắn
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
            $messageId = $this->messageModel->sendMessage($senderId, $receiverId, $messageText);

            if ($messageId) {
                // Tạo thông báo cho người nhận
                // Sử dụng tên người gửi để tạo thông báo thân thiện
                $notificationText = "You have a new message from  " . $_SESSION['user']['first_name'];
                $this->notificationModel->createNotification($receiverId, $notificationText);

                echo json_encode(["status" => "success", "message" => "Message sent successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to send message"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid request"]);
        }
        exit;
    }

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

    // Phương thức getMessages() cho chat realtime
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

        $messages = $this->messageModel->getNewMessages($senderId, $receiverId, $lastMessageId);

        echo json_encode(["status" => "success", "messages" => $messages]);
        exit;
    }

    // Thêm endpoint để lấy số thông báo chưa đọc của người dùng
    public function getUnreadCount()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            echo json_encode(["status" => "error", "message" => "User not logged in"]);
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $unreadNotifications = $this->notificationModel->getUnreadNotifications($userId);
        $unreadCount = count($unreadNotifications);
        echo json_encode(["status" => "success", "unread_count" => $unreadCount]);
        exit;
    }
}