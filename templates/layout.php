<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Мое приложение') ?></title>
    
    <!-- Внешние стили -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <link href="/my_cms/templates/assets/css/styles.css" rel="stylesheet">
    <script src="/my_cms/templates/assets/js/app.js"></script>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
            <div class="container">
                <a href="index.php" class="navbar-brand">
                    <img src="/my_cms/templates/assets/img/logo.png" alt="Логотип" height="30">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Переключить навигацию">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto gap-2">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item">
                                <a href="profile.php?login=<?= htmlspecialchars($_SESSION['login']) ?>" class="nav-link">Профиль</a>
                            </li>
                            <li class="nav-item">
                                <a href="users.php" class="nav-link">Пользователи</a>
                            </li>
                            <li class="nav-item">
                                <a href="dialogs.php" class="nav-link">Диалоги</a>
                            </li>
                            <li class="nav-item">
                                <a href="logout.php" class="nav-link">Выход</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a href="login.php" class="nav-link">Войти</a>
                            </li>
                            <li class="nav-item">
                                <a href="register.php" class="nav-link">Регистрация</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container py-5">
        <?= $content ?? '' ?>
    </main>

    <footer class="footer mt-auto py-3 bg-primary text-white">
        <div class="container text-center">
            <span>&copy; <?= date('Y') ?> Мое приложение</span>
        </div>
    </footer>
</body>
</html>