<?php $title = "Chat"; ob_start(); ?>
    <!-- Import chat CSS -->
    <link rel="stylesheet" href="/eTutoring/public/Css/chat.css">
    <!-- Import Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <div class="chat-container">
        <!-- Chat Header -->
        <div class="chat-header">
            <a href="?url=message/chatList" class="btn btn-light btn-sm back-btn">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="chat-header-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($receiverName, 0, 1)); ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?= htmlspecialchars($receiverName) ?></div>
                </div>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="chat-box" id="chatBox">
        </div>

        <!-- Message Input -->
        <div class="chat-input-container">
            <form id="messageForm" class="chat-input">
                <input type="hidden" id="receiverId" value="<?= $receiverId ?>">
                <input type="text" id="messageInput" placeholder="Type a message..." required>
                <button type="submit" class="send-btn"><i class="bi bi-send-fill"></i></button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const chatBox = document.getElementById("chatBox");
            const messageForm = document.getElementById("messageForm");
            const messageInput = document.getElementById("messageInput");
            const receiverId = document.getElementById("receiverId").value;
            let lastMessageId = 0;


            function fetchMessages() {
                fetch(`?url=message/getMessages&receiver_id=${receiverId}&last_message_id=${lastMessageId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success" && data.messages.length > 0) {
                            data.messages.forEach(msg => {
                                appendMessage(msg);
                                lastMessageId = msg.message_id;
                            });
                            chatBox.scrollTop = chatBox.scrollHeight;
                        }
                    })
                    .catch(error => console.error("Error fetching messages:", error));
            }


            function appendMessage(msg) {
                const messageElement = document.createElement("div");
                messageElement.classList.add("message", msg.sender_id === parseInt(receiverId) ? "received" : "sent");
                messageElement.innerHTML = `
                    <div class="bubble">${msg.message_text}</div>
                    <div class="timestamp">${new Date(msg.sent_at).toLocaleTimeString()}</div>
                `;
                chatBox.appendChild(messageElement);
            }


            messageForm.addEventListener("submit", function (e) {
                e.preventDefault();

                const messageText = messageInput.value.trim();
                if (messageText === "") return;

                fetch("?url=message/send", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `receiver_id=${receiverId}&message=${encodeURIComponent(messageText)}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success") {
                            messageInput.value = "";
                            fetchMessages();
                        }
                    })
                    .catch(error => console.error("Error sending message:", error));
            });


            setInterval(fetchMessages, 2000);
            fetchMessages();
        });
    </script>

<?php $content = ob_get_clean(); include '../app/views/partials/layout.php'; ?>