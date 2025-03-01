<?php
require_once '../App/models/Message.php';
session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

$senderId = $_SESSION['user']['user_id'];
$receiverId = $_GET['receiver_id'] ?? null;
$lastMessageId = $_GET['last_message_id'] ?? 0; // Tin nhắn mới nhất trên giao diện

if (!$receiverId) {
    echo json_encode(["status" => "error", "message" => "Receiver ID missing"]);
    exit;
}

$messageModel = new \App\Models\Message();
$messages = $messageModel->getNewMessages($senderId, $receiverId, $lastMessageId);

echo json_encode(["status" => "success", "messages" => $messages]);
exit;
?>