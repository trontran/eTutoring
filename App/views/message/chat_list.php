<?php
$title = "Messages";
ob_start();
?>

    <!-- Import CSS -->
    <link rel="stylesheet" href="/eTutoring/public/Css/chat.css">

    <div class="messages-container">
        <h2 class="text-center">Messages</h2>

        <div class="chat-list">
            <?php $chatUsers = $chatUsers ?? []; foreach ($chatUsers as $user): ?>
                <a href="?url=message/chat&receiver_id=<?= htmlspecialchars($user['user_id']) ?>" class="chat-item">
                    <div class="chat-avatar">
                        <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                    </div>
                    <div class="chat-info">
                        <strong class="chat-name"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></strong>
                        <div class="chat-last-message">
                            <?= htmlspecialchars($user['last_message'] ?? "No messages yet") ?>
                        </div>
                    </div>
                    <div class="chat-meta">
                        <span class="chat-time"><?= htmlspecialchars($user['last_message_time'] ?? "") ?></span>
                        <?php if (!empty($user['unread_count'])): ?>
                            <span class="chat-badge"><?= $user['unread_count'] ?></span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>