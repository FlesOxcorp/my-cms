<?php ob_start(); ?>

<div class="chat-container">
    <!-- Заголовок чата -->
    <div class="chat-header bg-primary text-white">
        <div class="d-flex align-items-center gap-3 p-3">
            <div class="chat-avatar avatar" style="background-color: <?= htmlspecialchars($recipientAvatarColor) ?>">
                <?= strtoupper($recipient[0]) ?>
            </div>
            <div class="chat-info flex-grow-1">
                <h3 class="mb-0 fs-5"><?= htmlspecialchars($recipient) ?></h3>
                <small class="text-light user-status" data-user-id="<?= $recipientId ?>">
                    <?= htmlspecialchars($recipientStatus) ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Контейнер сообщений -->
    <div id="messageContainer" class="message-container">
        <?php if ($messages): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="message <?= $msg['sender'] == $_SESSION['login'] ? 'sent' : 'received' ?>"
                     data-message-id="<?= $msg['id'] ?>">
                    <div class="message-avatar">
                        <div class="avatar" style="background-color: <?= htmlspecialchars($msg['avatar_color']) ?>;">
                            <?= strtoupper($msg['sender'][0]) ?>
                        </div>
                    </div>
                    <div class="message-bubble">
                        <div class="message-text">
                            <?= htmlspecialchars($msg['message']) ?>
                        </div>
                        <div class="message-meta">
                            <span class="message-time">
                                <?= date('H:i', strtotime($msg['sent_at'])) ?>
                            </span>
                            <?php if ($msg['sender'] == $_SESSION['login']): ?>
                                <span class="message-status">
                                    <i class="bi bi-check<?= $msg['is_read'] ? '-all' : '' ?>"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="chat-empty-state">
                <div class="text-center text-muted p-4">
                    <i class="bi bi-chat-dots display-4"></i>
                    <p class="mt-3">Начните диалог первым</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Форма отправки -->
    <div class="chat-footer bg-light">
        <form id="messageForm" class="message-form p-3">
            <div class="input-group">
                <textarea id="messageInput" 
                         class="form-control" 
                         placeholder="Введите сообщение..." 
                         rows="1"
                         required></textarea>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Стили специфичные для чата -->
<style>
.chat-container {
    background: #ffffff;
    border-radius: 1rem;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    height: calc(100vh - 180px);
    display: flex;
    flex-direction: column;
}

.chat-header {
    background: var(--bs-primary);
    color: white;
    border-bottom: 1px solid #ddd;
    border-radius: 1rem 1rem 0 0;
}

.chat-avatar {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    border-radius: 50%;
}

.message-container {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    background: #f9f9f9;
}

.message {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.message.sent {
    flex-direction: row-reverse;
}

.message-avatar .avatar {
    width: 32px;
    height: 32px;
    font-size: 0.875rem;
}

.message-bubble {
    max-width: calc(100% - 50px);
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    word-wrap: break-word;
    word-break: break-word;
}

.message.sent .message-bubble {
    background: #7791b7;
    color: white;
}

.message-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: #666;
}

.message.sent .message-meta {
    justify-content: flex-end;
    color: rgba(255, 255, 255, 0.8);
}

.message-status i {
    font-size: 1rem;
}

.chat-footer {
    background: #f1f1f1;
    border-top: 1px solid #ddd;
    border-radius: 0 0 1rem 1rem;
}

.message-form .input-group {
    background: #ffffff;
    border-radius: 2rem;
    padding: 0.25rem;
}

.message-form textarea {
    border: none;
    background: transparent;
    resize: none;
    max-height: 100px;
    color: #333;
}

.message-form textarea:focus {
    box-shadow: none;
}

.message-form .btn {
    border-radius: 50%;
    width: 40px;
    height: 40px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-empty-state {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #666;
}

@media (prefers-color-scheme: dark) {
    .chat-header,
    .chat-footer {
        background: #dee2e6;
    }

    .message-container {
        background: #dee2e6;
    }

    .message-bubble {
        background: #2b3561e8;
        color: #dee2e6;
    }

    .message-form .input-group {
        background: #dee2e6;
    }
}

@media (max-width: 768px) {
    .chat-container {
        height: calc(100vh - 140px);
        margin: -1rem;
        border-radius: 0;
    }

    .chat-header,
    .chat-footer {
        border-radius: 0;
    }

    .message-bubble {
        max-width: 85%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const messageContainer = document.getElementById('messageContainer');
    const recipientId = <?= $recipientId ?>;
    let lastMessageId = <?= empty($messages) ? 0 : $messages[count($messages)-1]['id'] ?>;

    // Автоматическая высота текстового поля
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });

    // Отправка сообщения
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = messageInput.value.trim();
        
        if (message) {
            sendMessage(message);
            messageInput.value = '';
            messageInput.style.height = 'auto';
        }
    });

    async function sendMessage(message) {
        try {
            const response = await fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    recipient_id: recipientId,
                    message: message
                })
            });

            const data = await response.json();
            
            if (data.success) {
                appendMessage(data.data);
                lastMessageId = data.data.id;
                scrollToBottom();
            } else {
                console.error('Ошибка отправки сообщения:', data.error);
            }
        } catch (error) {
            console.error('Ошибка:', error);
        }
    }

    async function checkNewMessages() {
        try {
            const response = await fetch(`fetch_messages.php?recipient_id=${recipientId}&last_message_id=${lastMessageId}`);
            const data = await response.json();

            if (data.messages && data.messages.length > 0) {
                const isAtBottom = isScrolledToBottom();
                data.messages.forEach(message => {
                    if (!document.querySelector(`.message[data-message-id="${message.id}"]`)) {
                        appendMessage(message);
                        lastMessageId = Math.max(lastMessageId, message.id);
                    }
                });
                if (isAtBottom) {
                    scrollToBottom();
                }
            }
        } catch (error) {
            console.error('Ошибка при получении новых сообщений:', error);
        }
    }

    function appendMessage(message) {
        if (document.querySelector(`.message[data-message-id="${message.id}"]`)) {
            return;
        }

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${message.sender === '<?= $_SESSION['login'] ?>' ? 'sent' : 'received'}`;
        messageDiv.dataset.messageId = message.id;
        
        messageDiv.innerHTML = `
            <div class="message-avatar">
                <div class="avatar" style="background-color: ${message.avatar_color};">
                    ${message.sender.charAt(0).toUpperCase()}
                </div>
            </div>
            <div class="message-bubble">
                <div class="message-text">${escapeHtml(message.message)}</div>
                <div class="message-meta">
                    <span class="message-time">${formatTime(message.sent_at)}</span>
                    ${message.sender === '<?= $_SESSION['login'] ?>' ? 
                        '<span class="message-status"><i class="bi bi-check"></i></span>' : ''}
                </div>
            </div>
        `;

        messageContainer.appendChild(messageDiv);
    }

    function isScrolledToBottom() {
        return messageContainer.scrollHeight - messageContainer.scrollTop <= messageContainer.clientHeight + 10;
    }

    function scrollToBottom() {
        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    function updateMessageStatus() {
        const messageIds = Array.from(document.querySelectorAll('.message.received[data-message-id]'))
            .map(msg => parseInt(msg.dataset.messageId, 10));

        if (messageIds.length > 0) {
            fetch('update_message_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message_ids: messageIds,
                    sender_id: recipientId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Ошибка обновления статуса сообщений:', data.error);
                }
            })
            .catch(error => console.error('Ошибка сети при обновлении статуса сообщений:', error));
        }
    }

    function updateUserStatus() {
        // Ваша логика обновления статуса пользователя
        console.log('Обновление статуса пользователя');
    }

    // Обновление статусов
    setInterval(checkNewMessages, 3000);
    setInterval(updateMessageStatus, 5000);
    setInterval(updateUserStatus, 30000);

    scrollToBottom();
});
</script>

<?php 
$content = ob_get_clean();
include 'layout.php';
?>