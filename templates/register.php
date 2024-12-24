<?php
$title = 'Регистрация';
$header = 'Создать аккаунт';

ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-md">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Регистрация</h2>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Имя пользователя</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   required 
                                   minlength="3" 
                                   maxlength="50"
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                            <div class="form-text">От 3 до 50 символов</div>
                        </div>

                        <div class="mb-3">
                            <label for="login" class="form-label">Логин</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="login" 
                                   name="login" 
                                   required 
                                   minlength="<?= Auth::LOGIN_MIN_LENGTH ?>" 
                                   maxlength="<?= Auth::LOGIN_MAX_LENGTH ?>"
                                   pattern="[a-zA-Z0-9_-]+"
                                   value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
                            <div class="form-text">Только буквы, цифры, тире и подчеркивание</div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Пароль</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   minlength="<?= Auth::PASSWORD_MIN_LENGTH ?>">
                            <div class="form-text">
                                Минимум <?= Auth::PASSWORD_MIN_LENGTH ?> символов, включая заглавные и строчные буквы, цифры
                            </div>
                        </div>

                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                        <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                    </form>

                    <div class="text-center mt-3">
                        <p class="mb-0">Уже есть аккаунт? <a href="login.php">Войти</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Валидация формы на стороне клиента
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>