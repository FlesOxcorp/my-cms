<?php
// Настройки сессии должны быть установлены до session_start()
ini_set('session.gc_maxlifetime', 3600); // Время жизни сессии на сервере
ini_set('session.cookie_lifetime', 0);   // Время жизни cookie сессии - до закрытия браузера

// Запуск сессии
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require 'config/database.php';
require 'core/Auth.php';
require 'core/Template.php';
require 'core/helpers.php';

// Создание соединения с базой данных
$config = include 'config/config.php';
$dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8";
$pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);

$auth = new Auth($pdo);
$template = new Template(__DIR__ . '/templates');

// Проверка авторизации
if (!$auth->isAuthenticated()) {
    header('Location: login.php');
    exit;
}

try {
    // Получение информации о пользователе
    $stmt = $pdo->prepare('
        SELECT id, login, username, created_at, avatar_color
        FROM users 
        WHERE id = ?
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    // Получение статистики
    $stats = [
        'messages_count' => 0,
        'dialogs_count' => 0,
        'days_registered' => 0
    ];

    // Подсчет сообщений
    $stmt = $pdo->prepare('
        SELECT COUNT(*) 
        FROM messages 
        WHERE sender_id = ?
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $stats['messages_count'] = $stmt->fetchColumn();

    // Подсчет уникальных диалогов
    $stmt = $pdo->prepare('
        SELECT COUNT(DISTINCT 
            CASE 
                WHEN sender_id = ? THEN recipient_id 
                WHEN recipient_id = ? THEN sender_id 
            END
        ) as dialogs_count
        FROM messages 
        WHERE sender_id = ? OR recipient_id = ?
    ');
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $stats['dialogs_count'] = $stmt->fetchColumn();

    // Изменить подсчет дней на сайте
    $registrationDate = new DateTime($userInfo['created_at']);
    $now = new DateTime();
    $interval = $registrationDate->diff($now);
    $stats['days_registered'] = max(0, $interval->days);

    // Добавить обновление статуса онлайн
    $stmt = $pdo->prepare('UPDATE users SET last_activity = NOW() WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);

    // Получение последних диалогов
    $stmt = $pdo->prepare('
        SELECT 
            u.login,
            u.avatar_color,
            m.message as last_message,
            m.sent_at as last_message_time,
            (SELECT COUNT(*) 
             FROM messages 
             WHERE sender_id = u.id 
             AND recipient_id = ? 
             AND is_read = 0) as unread_count
        FROM (
            SELECT 
                CASE 
                    WHEN sender_id = ? THEN recipient_id
                    ELSE sender_id
                END as user_id,
                MAX(id) as last_message_id
            FROM messages
            WHERE sender_id = ? OR recipient_id = ?
            GROUP BY 
                CASE 
                    WHEN sender_id = ? THEN recipient_id
                    ELSE sender_id
                END
        ) as latest
        JOIN messages m ON m.id = latest.last_message_id
        JOIN users u ON u.id = latest.user_id
        ORDER BY m.sent_at DESC
        LIMIT 5
    ');
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $recentDialogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получение пользователей онлайн
    $stmt = $pdo->prepare('
        SELECT login, avatar_color
        FROM users
        WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        AND id != ?
        ORDER BY last_activity DESC
        LIMIT 5
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $onlineUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Передача данных в шаблон
    $template->assign('userInfo', $userInfo);
    $template->assign('stats', $stats);
    $template->assign('recentDialogs', $recentDialogs);
    $template->assign('onlineUsers', $onlineUsers);
    
    $template->show('home');

} catch (Exception $e) {
    error_log("Error on homepage: " . $e->getMessage());
    $template->assign('error', 'Произошла ошибка при загрузке данных');
    $template->show('error');
}

// Функция форматирования времени
function formatTime($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'только что';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return "$minutes мин. назад";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "$hours ч. назад";
    } else {
        return date('d.m.Y H:i', $time);
    }
}
?>