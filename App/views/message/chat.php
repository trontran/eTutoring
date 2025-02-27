<?php $title = "Chat"; ob_start(); ?>
    <!-- Import chat CSS -->
    <link rel="stylesheet" href="/eTutoring/public/Css/chat.css">
    <!-- Import Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <div class="chat-container">
        <!-- Chat Header -->
        <div class="chat-header">
            <!-- Nút Back -->
            <a href="?url=message/chatList" class="btn btn-light btn-sm back-btn">
                <i class="bi bi-arrow-left"></i>
            </a>

            <div class="chat-header-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($receiverName, 0, 1)); ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?= htmlspecialchars($receiverName) ?></div>
                </div>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="chat-box" id="chatBox">
            <?php
            $currentDate = '';
            $messages = isset($messages) ? $messages : []; // Ensure $messages is defined
            foreach ($messages as $message):
                $messageDate = date('d/m/Y', strtotime($message['sent_at']));

                // Add date separator if date changes
                if ($messageDate != $currentDate) {
                    $currentDate = $messageDate;
                    $displayDate = (date('d/m/Y') == $messageDate) ? 'Today' : $messageDate;
                    if ($message !== reset($messages)):
                        ?>
                        <div class="chat-date-divider">
                            <span><?= $displayDate ?></span>
                        </div>
                    <?php
                    endif;
                }
                ?>
                <div class="message <?= $message['sender_id'] == $_SESSION['user']['user_id'] ? 'sent' : 'received' ?>">
                    <?php if ($message['sender_id'] != $_SESSION['user']['user_id']): ?>
                        <div class="avatar-wrapper">
                            <div class="message-avatar">
                                <?php echo strtoupper(substr($receiverName, 0, 1)); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="message-content">
                        <div class="bubble">
                            <?= htmlspecialchars($message['message_text']) ?>
                        </div>
                        <div class="timestamp">
                            <?= date('H:i - d/m/Y', strtotime($message['sent_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Message Input -->
        <div class="chat-input-container">
            <form action="?url=message/send" method="POST" class="chat-input">
                <input type="hidden" name="receiver_id" value="<?= $receiverId ?>">
                <input type="text" name="message" placeholder="Type a message..." required>
                <button type="submit" class="send-btn"><i class="bi bi-send-fill"></i></button>
            </form>
        </div>
    </div>

    <script>
        // Auto scroll to newest message
        document.addEventListener('DOMContentLoaded', function() {
            var chatBox = document.getElementById('chatBox');
            chatBox.scrollTop = chatBox.scrollHeight;
        });
    </script>

<?php $content = ob_get_clean(); include '../app/views/partials/layout.php'; ?>