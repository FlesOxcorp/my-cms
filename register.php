<?php
session_start();
require 'config/database.php';
require 'core/Auth.php';
require 'core/CSRF.php';
require 'core/Template.php';

// Если пользователь уже авторизован, перенаправляем на главную
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$auth = new Auth($pdo);
$template = new Template(__DIR__ . '/templates');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Проверка CSRF токена
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Недействительный токен безопасности');
        }

        // Получение и валидация данных
        $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
        $login = trim(filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING));
        $password = $_POST['password'] ?? '';

        // Проверка обязательных полей
        if (empty($username) || empty($login) || empty($password)) {
            throw new Exception('Все поля обязательны для заполнения');
        }

        // Попытка регистрации
        $result = $auth->register($username, $login, $password);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Регистрация успешно завершена! Теперь вы можете войти.';
            header('Location: login.php');
            exit;
        } else {
            $template->assign('error', implode('<br>', $result['errors']));
        }
    }
} catch (Exception $e) {
    $template->assign('error', $e->getMessage());
}

// Генерация нового CSRF токена
$template->assign('csrf_token', CSRF::generateToken());
$template->show('register');
?>