<?php
session_start();
require 'config/database.php';
require 'core/Auth.php';
require 'core/Template.php';
require 'core/UserProfile.php';

$auth = new Auth($pdo);
$template = new Template(__DIR__ . '/templates');
$userProfile = new UserProfile($pdo);

try {
    // Проверка авторизации
    if (!$auth->isAuthenticated()) {
        header('Location: login.php');
        exit;
    }

    // Получение логина из URL
    $login = filter_input(INPUT_GET, 'login', FILTER_SANITIZE_STRING);

    if (!$login) {
        throw new Exception('Не указан логин пользователя');
    }

    // Получение информации о пользователе
    $user = $userProfile->getUserInfo($login);

    if (!$user) {
        throw new Exception('Пользователь не найден');
    }

    // Получение последней активности
    $lastActivity = $userProfile->getLastActivity($user['id']);

    // Получение последних диалогов
    $recentDialogs = $userProfile->getRecentDialogs($user['id']);

    // Передача данных в шаблон
    $template->assign('user', $user);
    $template->assign('lastActivity', $lastActivity);
    $template->assign('recentDialogs', $recentDialogs);
    $template->assign('isOwnProfile', $_SESSION['login'] === $login);
    $template->assign('title', "Профиль пользователя " . $login);
    $template->assign('header', "Профиль пользователя");

    $template->show('profile');

} catch (Exception $e) {
    // Логирование ошибки
    error_log("Profile error: " . $e->getMessage());
    
    // Передача ошибки в шаблон
    $template->assign('error', $e->getMessage());
    $template->assign('title', 'Ошибка');
    $template->assign('header', 'Произошла ошибка');
    
    $template->show('error');
}
?>
