<?php ob_start(); ?>

<div class="users-container">
    <!-- Búsqueda -->
    <div class="search-box mb-4">
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control" 
                   placeholder="Поиск пользователей..." 
                   value="<?= htmlspecialchars($search ?? '') ?>">
            <button type="submit" class="btn btn-primary ms-2">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>

    <!-- Список пользователей -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($users as $user): ?>
            <div class="col">
                <div class="card user-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="user-avatar" 
                                 style="background-color: <?= htmlspecialchars($user['avatar_color'] ?? '#6c757d') ?>">
                                <?= strtoupper($user['login'][0]) ?>
                            </div>
                            <div class="ms-3">
                                <h5 class="card-title mb-1">
                                    <a href="profile.php?login=<?= urlencode($user['login']) ?>" 
                                       class="text-decoration-none">
                                        <?= htmlspecialchars($user['login']) ?>
                                    </a>
                                </h5>
                                <p class="text-muted small mb-0">
                                    <?php if (isset($user['created_at']) && $user['created_at']): ?>
                                        Зарегистрирован <?= date('d.m.Y', strtotime($user['created_at'])) ?>
                                    <?php else: ?>
                                        Дата регистрации неизвестна
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <?php if ($user['login'] !== $_SESSION['login']): ?>
                            <div class="mt-auto">
                                <a href="message.php?to=<?= urlencode($user['login']) ?>" 
                                   class="btn btn-outline-primary btn-sm w-100">
                                    <i class="bi bi-chat-dots"></i> Отправить сообщение
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Пагинация -->
    <?php if ($totalPages > 1): ?>
    <div class="d-flex justify-content-center mt-4">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php if ($currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1<?= isset($search) ? '&search=' . urlencode($search) : '' ?>">
                            <i class="bi bi-chevron-double-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <?php
                $start = max(1, $currentPage - 2);
                $end = min($totalPages, $currentPage + 2);
                
                for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= isset($search) ? '&search=' . urlencode($search) : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $totalPages ?><?= isset($search) ? '&search=' . urlencode($search) : '' ?>">
                            <i class="bi bi-chevron-double-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<style>
.users-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.user-card {
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.user-card:hover {
    transform: translateY(-5px);
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2em;
}

.pagination {
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    border-radius: 30px;
    padding: 5px;
}

.page-link {
    border: none;
    margin: 0 5px;
    border-radius: 50% !important;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<?php
$content = ob_get_clean();
include 'layout.php';
?>