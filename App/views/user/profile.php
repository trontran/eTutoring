<?php
$title = "User Profile";
ob_start();
?>

    <div class="container mt-4">
        <h2 class="text-center text-primary mb-4">
            <i class="bi bi-person-circle"></i> User Profile
        </h2>

        <div class="card mx-auto shadow border-0" style="max-width: 500px;">
            <div class="card-body text-center">
                <?php

                $firstName = $user['first_name'] ?? '';
                $lastName = $user['last_name'] ?? '';
                $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                ?>
                <!-- Avatar (chữ cái đầu) -->
                <div class="avatar d-inline-flex mb-3"
                     style="width: 100px; height: 100px; border-radius: 50%; background-color: #007bff; color: #fff; font-size: 2.5rem; justify-content: center; align-items: center;">
                    <?= htmlspecialchars($initials) ?>
                </div>

                <!-- Tên người dùng -->
                <h4 class="card-title fw-bold">
                    <?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?>
                </h4>
                <!-- Email -->
                <p class="card-text text-muted mb-1">
                    <i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($user['email']) ?>
                </p>
                <!-- Role -->
                <p class="card-text">
                <span class="badge bg-primary">
                    <?= ucfirst(htmlspecialchars($user['role'])) ?>
                </span>
                </p>

                <!-- Thông báo tin nhắn mới -->
                <?php if (!empty($unreadNotifications)): ?>
                    <div class="alert alert-warning text-start mt-4">
                        <h6 class="mb-2"><i class="bi bi-bell-fill"></i> Notifications</h6>
                        <ul class="list-unstyled mb-0 ps-2">
                            <?php foreach ($unreadNotifications as $notification): ?>
                                <li class="mb-1">
                                    <i class="bi bi-chat-dots-fill text-info"></i>
                                    <?= htmlspecialchars($notification['notification_text']) ?>
                                    <small class="text-muted">
                                        (<?= $notification['created_at'] ?>)
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Nút nhắn tin nếu không phải profile của chính mình -->
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