document.addEventListener("DOMContentLoaded", function () {
    const messageForm = document.getElementById("messageForm");
    const messageInput = document.getElementById("messageInput");
    const chatBox = document.getElementById("chatBox");
    const receiverId = document.getElementById("receiverId").value;

    messageForm.addEventListener("submit", function (e) {
        e.preventDefault();  // Ngăn chặn load lại trang

        const messageText = messageInput.value.trim();
        if (messageText === "") return;

        fetch("?url=message/send", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `receiver_id=${receiverId}&message=${encodeURIComponent(messageText)}`
        })
            .then(response => response.json()) // Chuyển phản hồi về JSON
            .then(data => {
                if (data.status === "success") {
                    messageInput.value = "";
                    fetchMessages(); // Gọi hàm cập nhật tin nhắn ngay lập tức
                }
            })
            .catch(error => console.error("Error sending message:", error));
    });

    function fetchMessages() {
        fetch(`?url=message/getMessages&receiver_id=${receiverId}`)
            .then(response => response.json())
            .then(data => {
                chatBox.innerHTML = ""; // Xóa nội dung cũ
                data.messages.forEach(msg => {
                    appendMessage(msg);
                });
                chatBox.scrollTop = chatBox.scrollHeight; // Cuộn xuống cuối
            });
    }

    function appendMessage(msg) {
        const messageElement = document.createElement("div");
        messageElement.classList.add("message", msg.sender_id === parseInt(receiverId) ? "received" : "sent");
        messageElement.innerHTML = `<div class="bubble">${msg.message_text}</div>
                                    <div class="timestamp">${new Date(msg.sent_at).toLocaleTimeString()}</div>`;
        chatBox.appendChild(messageElement);
    }

    setInterval(fetchMessages, 3000); // Cập nhật tin nhắn mỗi 3 giây
});