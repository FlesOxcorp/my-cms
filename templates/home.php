<?php
$title = 'Главная';
$header = 'Добро пожаловать!';

ob_start();
?>

<div class="dashboard-container">

<div class="page-header mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="page-pretitle">
                    Панель управления
                </div>
                <h2 class="page-title">
                    Добро пожаловать!
                </h2>
            </div>
        </div>
    </div>
</div>

    <!-- Приветствие и статистика -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card welcome-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="user-avatar me-3" 
                             style="background-color: <?= htmlspecialchars($userInfo['avatar_color']) ?>">
                            <?= strtoupper($userInfo['login'][0]) ?>
                        </div>
                        <div>
                            <h2 class="mb-1">Привет, <?= htmlspecialchars($userInfo['login']) ?>!</h2>
                            <p class="text-muted mb-0">
                                На сайте с <?= date('d.m.Y', strtotime($userInfo['created_at'])) ?>
                            </p>
                        </div>
                    </div>

                    <div class="row stats">
                        <div class="col-4">
                            <div class="stat-item">
                                <i class="bi bi-chat-dots"></i>
                                <h3><?= $stats['messages_count'] ?></h3>
                                <p>Сообщений</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <i class="bi bi-people"></i>
                                <h3><?= $stats['dialogs_count'] ?></h3>
                                <p>Диалогов</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <i class="bi bi-clock-history"></i>
                                <h3><?= $stats['days_registered'] ?></h3>
                                <p>Дней на сайте</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Быстрые действия</h5>
                    <div class="d-grid gap-2">
                        <a href="profile.php?login=<?= urlencode($_SESSION['login']) ?>" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-person"></i> Мой профиль
                        </a>
                        <a href="dialogs.php" class="btn btn-outline-primary">
                            <i class="bi bi-chat"></i> Мои диалоги
                        </a>
                        <a href="users.php" class="btn btn-outline-primary">
                            <i class="bi bi-people"></i> Найти пользователей
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Последние диалоги -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Последние диалоги</h5>
                    <a href="dialogs.php" class="btn btn-sm btn-primary">Все диалоги</a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recentDialogs)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentDialogs as $dialog): ?>
                                <a href="message.php?to=<?= urlencode($dialog['login']) ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3" 
                                             style="background-color: <?= htmlspecialchars($dialog['avatar_color']) ?>">
                                            <?= strtoupper($dialog['login'][0]) ?>
                                        </div>
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><?= htmlspecialchars($dialog['login']) ?></h6>
                                                <small class="text-muted">
                                                    <?= formatTime($dialog['last_message_time']) ?>
                                                </small>
                                            </div>
                                            <p class="text-muted text-truncate mb-0">
                                                <?= htmlspecialchars($dialog['last_message']) ?>
                                            </p>
                                        </div>
                                        <?php if ($dialog['unread_count'] > 0): ?>
                                            <span class="badge bg-primary rounded-pill">
                                                <?= $dialog['unread_count'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">У вас пока нет диалогов</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Активные пользователи -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Сейчас онлайн</h5>
                    <a href="users.php" class="btn btn-sm btn-primary">Все пользователи</a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($onlineUsers)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($onlineUsers as $user): ?>
                                <a href="profile.php?login=<?= urlencode($user['login']) ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3" 
                                             style="background-color: <?= htmlspecialchars($user['avatar_color']) ?>">
                                            <?= strtoupper($user['login'][0]) ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($user['login']) ?></h6>
                                            <small class="text-success">
                                                <i class="bi bi-circle-fill"></i> Онлайн
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Сейчас никого нет онлайн</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
}

.welcome-card {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border: none;
    overflow: hidden;
    position: relative;
}

.welcome-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg ...>'); /* Добавьте красивый паттерн */
    opacity: 0.1;
}

.user-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    background-color: rgba(255, 255, 255, 0.2);
}

.stat-item {
    padding: 20px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    backdrop-filter: blur(10px);
    transition: transform 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-5px);
}

.quick-actions .btn {
    padding: 15px;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.quick-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Добавить анимaciones */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: slideIn 0.5s ease-out forwards;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

.card {
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.card-header {
    background-color: transparent;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.list-group-item {
    border: none;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.list-group-item:last-child {
    border-bottom: none;
}

.badge {
    padding: 0.5em 0.8em;
}
</style>

<script>
function updateOnlineStatus() {
    fetch('get_online_users.php')
        .then(response => response.json())
        .then(data => {
            const onlineContainer = document.querySelector('.online-users-container');
            if (data.users && data.users.length > 0) {
                onlineContainer.innerHTML = data.users.map(user => `
                    <a href="profile.php?login=${encodeURIComponent(user.login)}" 
                       class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <div class="avatar me-3" 
                                 style="background-color: ${user.avatar_color}">
                                ${user.login.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <h6 class="mb-0">${user.login}</h6>
                                <small class="text-success">
                                    <i class="bi bi-circle-fill"></i> Онлайн
                                </small>
                            </div>
                        </div>
                    </a>
                `).join('');
            }
        });
}

// Обновлять статус каждые 30 секунд
setInterval(updateOnlineStatus, 30000);
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>