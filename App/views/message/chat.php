<?php
$title = "Chat";
ob_start();
?>

    <h2 class="text-center">Chat</h2>

    <div class="chat-box border p-3">
        <?php foreach ($messages as $message): ?>
            <div class="<?= $message['sender_id'] == $_SESSION['user']['user_id'] ? 'text-end' : 'text-start' ?>">
                <p class="badge bg-primary"><?= htmlspecialchars($message['message_text']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <form action="?url=message/send" method="POST" class="mt-3">
        <input type="hidden" name="receiver_id" value="<?= $receiverId ?>">
        <div class="input-group">
            <input type="text" class="form-control" name="message" placeholder="Type a message..." required>
            <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
        </div>
    </form>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>