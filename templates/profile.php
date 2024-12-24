<?php ob_start(); ?>

<div class="profile-container">
    <div class="row g-4">
        <!-- Левая колонка с основной информацией -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="profile-avatar avatar mx-auto mb-4" 
                         style="width: 120px; height: 120px; background-color: <?= htmlspecialchars($user['avatar_color'] ?? '#6c757d') ?>;">
                        <span class="display-5 text-white">
                            <?= strtoupper($user['login'][0]) ?>
                        </span>
                    </div>
                    
                    <h3 class="card-title mb-1"><?= htmlspecialchars($user['login']) ?></h3>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($lastActivity) ?></p>

                    <?php if (!$isOwnProfile): ?>
                        <a href="message.php?to=<?= urlencode($user['login']) ?>" 
                           class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-chat-dots"></i> Написать сообщение
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Статистика -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Статистика</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-calendar3"></i>
                            Регистрация: <?= isset($user['created_at']) ? date('d.m.Y', strtotime($user['created_at'])) : 'Неизвестно' ?>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-chat"></i>
                            Сообщений: <?= $user['messages_sent'] ?? 0 ?>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-people"></i>
                            Диалогов: <?= $user['dialogs_count'] ?? 0 ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Правая колонка с дополнительной информацией -->
        <div class="col-md-8">
            <?php if (!empty($recentDialogs)): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Недавние диалоги</h5>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentDialogs as $dialog): ?>
                            <a href="message.php?to=<?= urlencode($dialog['login']) ?>" 
                               class="list-group-item list-group-item-action d-flex align-items-center">
                                <div class="avatar-sm" 
                                     style="background-color: <?= htmlspecialchars($dialog['avatar_color'] ?? '#6c757d') ?>;">
                                    <?= strtoupper($dialog['login'][0]) ?>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0"><?= htmlspecialchars($dialog['login']) ?></h6>
                                    <small class="text-muted">
                                        Последнее сообщение: <?= isset($dialog['sent_at']) ? date('d.m.Y H:i', strtotime($dialog['sent_at'])) : 'Неизвестно' ?>
                                    </small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.profile-container {
    max-width: 1000px;
    margin: 0 auto;
}

.profile-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
}

.avatar-sm {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
}

@media (max-width: 768px) {
    .profile-container {
        padding: 1rem;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        font-size: 2rem;
    }
}
</style>

<?php 
$content = ob_get_clean();
include 'layout.php';
?>