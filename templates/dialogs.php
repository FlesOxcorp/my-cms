<?php ob_start(); ?>

<div class="dialogs-container">
    <!-- Поиск диалогов -->
    <div class="search-box mb-4">
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control" 
                   placeholder="Поиск диалогов..." 
                   value="<?= htmlspecialchars($searchQuery ?? '') ?>">
            <button type="submit" class="btn btn-primary ms-2">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>

    <!-- Список диалогов -->
    <?php if (empty($dialogs)): ?>
        <div class="text-center text-muted my-5">
            <i class="bi bi-chat-dots display-4"></i>
            <h5 class="mt-3">
                <?= $searchQuery ? 'По вашему запросу ничего не найдено' : 'У вас пока нет активных диалогов' ?>
            </h5>
            <p class="mb-0">Начните общение с новыми людьми!</p>
        </div>
    <?php else: ?>
        <div class="dialog-list">
            <?php foreach ($dialogs as $dialog): ?>
                <a href="message.php?to=<?= urlencode($dialog['login']) ?>" 
                   class="dialog-item <?= $dialog['unread_count'] > 0 ? 'unread' : '' ?>"
                   data-user-id="<?= $dialog['id'] ?>">
                    
                    <div class="dialog-avatar">
                        <!-- Аватар -->
                        <div class="avatar" style="background-color: <?= htmlspecialchars($dialog['avatar_color']) ?>">
                            <?= strtoupper($dialog['login'][0]) ?>
                        </div>
                        <!-- Индикатор онлайн статуса -->
                        <?php if ($dialog['online_status']['status'] === 'online'): ?>
                            <span class="online-indicator"></span>
                        <?php endif; ?>
                    </div>

                    <div class="dialog-content">
                        <div class="dialog-header">
                            <h6 class="dialog-title"><?= htmlspecialchars($dialog['login']) ?></h6>
                            <?php if ($dialog['last_message']): ?>
                                <span class="dialog-time">
                                    <?= htmlspecialchars($dialog['last_message']['formatted_time']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="dialog-body">
                            <?php if ($dialog['last_message']): ?>
                                <p class="last-message">
                                    <?php if ($dialog['last_message']['is_own']): ?>
                                        <i class="bi bi-check<?= $dialog['last_message']['is_read'] ? '-all' : '' ?>"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars(mb_strimwidth($dialog['last_message']['message'], 0, 50, "...")) ?>
                                </p>
                            <?php else: ?>
                                <p class="text-muted">Нет сообщений</p>
                            <?php endif; ?>

                            <?php if ($dialog['unread_count'] > 0): ?>
                                <span class="unread-badge">
                                    <?= $dialog['unread_count'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обновление статуса диалогов
    function updateDialogStatuses() {
        document.querySelectorAll('.dialog-item').forEach(dialog => {
            const userId = dialog.dataset.userId;
            fetch(`get_user_status.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const onlineIndicator = dialog.querySelector('.online-indicator');
                        if (data.status === 'online') {
                            if (!onlineIndicator) {
                                const indicator = document.createElement('span');
                                indicator.className = 'online-indicator';
                                dialog.querySelector('.dialog-avatar').appendChild(indicator);
                            }
                        } else if (onlineIndicator) {
                            onlineIndicator.remove();
                        }
                    }
                });
        });
    }

    // Обновление каждые 30 секунд
    setInterval(updateDialogStatuses, 30000);
});
</script>

<style>
.dialogs-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.search-box {
    position: sticky;
    top: 0;
    background-color: var(--light-color);
    z-index: 100;
    padding-top: 1rem;
}

.dialog-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.dialog-item {
    display: flex;
    align-items: center;
    padding: 1.25rem;
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    background-color: white;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
}

.dialog-item:hover {
    transform: translateX(5px);
    background-color: var(--light-color);
}

.dialog-item.unread {
    background-color: #f0f7ff;
}

.dialog-avatar {
    position: relative;
    margin-right: 1.25rem;
}

.avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
    font-size: 1.2em;
}

.online-indicator {
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 12px;
    height: 12px;
    background-color: var(--success-color);
    border: 2px solid white;
    border-radius: 50%;
}

.dialog-content {
    flex: 1;
    min-width: 0;
}

.dialog-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.dialog-title {
    margin: 0;
    font-weight: 600;
    font-size: 1rem;
}

.dialog-time {
    font-size: 0.85rem;
    color: var(--gray-color);
}

.last-message {
    margin: 0;
    color: var(--gray-color);
    font-size: 0.95rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 80%;
}

.unread-badge {
    background-color: var(--primary-color);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    font-size: 0.8rem;
}

.bi-check, .bi-check-all {
    color: var(--primary-color);
    margin-right: 0.25rem;
}

@media (max-width: 768px) {
    .dialogs-container {
        padding: 1rem;
    }

    .dialog-item {
        padding: 1rem;
    }

    .avatar {
        width: 44px;
        height: 44px;
        font-size: 1rem;
    }

    .dialog-title {
        font-size: 0.95rem;
    }

    .last-message {
        font-size: 0.9rem;
    }
}
</style>

<?php 
$content = ob_get_clean();
include 'layout.php';
?>