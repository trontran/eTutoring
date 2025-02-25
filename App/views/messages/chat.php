<?php $title = "Chat"; ob_start(); ?>
    <h2 class="text-center">Chat</h2>
    <div class="chat-box border p-3 mb-3" style="height: 400px; overflow-y: scroll; background: #f8f9fa;">
        <?php foreach ($messages as $msg): ?>
            <div class="p-2 mb-2 <?= ($msg['sender_id'] == $_SESSION['user']['user_id']) ? 'text-end' : 'text-start' ?>">
                <strong><?= ($msg['sender_id'] == $_SESSION['user']['user_id']) ? 'You' : 'Them' ?>:</strong>
                <p class="bg-light p-2 rounded"><?= htmlspecialchars($msg['message_text']) ?></p>
                <small class="text-muted"><?= $msg['sent_at'] ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <form action="?url=message/send" method="POST">
        <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
        <div class="input-group">
            <input type="text" class="form-control" name="message_text" placeholder="Type a message..." required>
            <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Send</button>
        </div>
    </form>


<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>