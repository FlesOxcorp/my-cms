<?php
$title = 'Вход';
$header = 'Войти в аккаунт';

ob_start();
?>
<h2>Вход в систему</h2>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST" class="row g-3">
    <div class="col-md-6">
        <input type="text" name="username" class="form-control" placeholder="Имя пользователя" required>
    </div>
    <div class="col-12">
        <input type="password" name="password" class="form-control" placeholder="Пароль" required>
    </div>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Войти</button>
    </div>
</form>
<?php
$content = ob_get_clean();
include 'layout.php';
?>