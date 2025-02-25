<?php
$title = "User Profile"; // Tiêu đề trang
ob_start();
?>

    <div class="container mt-4">
        <h2 class="text-center"><i class="bi bi-person-circle"></i> User Profile</h2>

        <div class="card mx-auto shadow" style="max-width: 450px;">
            <div class="card-body text-center">
                <h4 class="card-title"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></h4>
                <p class="card-text"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p class="card-text"><strong>Role:</strong> <?= ucfirst(htmlspecialchars($user['role'])) ?></p>

                <!-- Thông báo tin nhắn mới -->
                <?php if (!empty($unreadNotifications)): ?>
                    <div class="alert alert-warning text-start mt-3">
                        <strong>📢 Notifications:</strong>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($unreadNotifications as $notification): ?>
                                <li><i class="bi bi-chat-dots-fill"></i> <?= htmlspecialchars($notification['notification_text']) ?>
                                    <small class="text-muted">(<?= $notification['created_at'] ?>)</small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Nút nhắn tin nếu người dùng không phải là chính mình -->
                <?php if ($_SESSION['user']['user_id'] != $user['user_id']): ?>
                    <a href="?url=message/chat&receiver_id=<?= $user['user_id'] ?>" class="btn btn-primary mt-3">
                        <i class="bi bi-chat-text"></i> Message
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>