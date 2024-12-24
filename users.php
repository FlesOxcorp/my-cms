<?php
session_start();
require 'config/database.php';
require 'core/Auth.php';
require 'core/Template.php';
require 'core/helpers.php';

$auth = new Auth($pdo);
$template = new Template(__DIR__ . '/templates');

// Проверка авторизации
if (!$auth->isAuthenticated()) {
    header('Location: login.php');
    exit;
}

try {
    $limit = 10;
    $page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
    $offset = ($page - 1) * $limit;
    $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);

    // Подготовка базового запроса
    $query = "SELECT id, login, username, created_at, avatar_color, last_activity FROM users";
    $countQuery = "SELECT COUNT(*) FROM users";
    $params = [];

    // Добавление поиска, если есть
    if ($search) {
        $query .= " WHERE login LIKE :search OR username LIKE :search";
        $countQuery .= " WHERE login LIKE :search OR username LIKE :search";
        $params[':search'] = "%$search%";
    }

    // Добавление сортировки и лимита
    $query .= " ORDER BY created_at DESC LIMIT :offset, :limit";

    // Получение общего количества пользователей
    $stmt = $pdo->prepare($countQuery);
    if ($search) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $stmt->execute();
    $totalUsers = $stmt->fetchColumn();

    // Получение пользователей для текущей страницы
    $stmt = $pdo->prepare($query);
    if ($search) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Вычисление общего количества страниц
    $totalPages = ceil($totalUsers / $limit);

    // Передача данных в шаблон
    $template->assign('users', $users);
    $template->assign('totalPages', $totalPages);
    $template->assign('currentPage', $page);
    $template->assign('search', $search);
    $template->assign('title', 'Пользователи');
    $template->assign('header', 'Список пользователей');

    $template->show('users');

} catch (Exception $e) {
    $template->assign('error', $e->getMessage());
    $template->show('error');
}