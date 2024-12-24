<?php
session_start();
require 'config/database.php';
require 'core/Auth.php';
require 'core/Template.php';
require 'core/helpers.php';
require 'core/DialogHandler.php';

$auth = new Auth($pdo);
$template = new Template(__DIR__ . '/templates');
$dialogHandler = new DialogHandler($pdo);

checkAuthentication($auth);
regenerateSessionId();

$userId = $_SESSION['user_id'];
$searchQuery = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);

try {
    // Получаем диалоги с учетом поиска
    $dialogs = $dialogHandler->getUserDialogs($userId, $searchQuery);
    
    // Получаем количество непрочитанных сообщений
    foreach ($dialogs as &$dialog) {
        $dialog['unread_count'] = $dialogHandler->getUnreadCount($userId, $dialog['id']);
        $dialog['last_message'] = $dialogHandler->getLastMessage($userId, $dialog['id']);
        $dialog['online_status'] = $dialogHandler->getUserStatus($dialog['id']);
    }

    $template->assign('dialogs', $dialogs);
    $template->assign('searchQuery', $searchQuery);
    $template->assign('title', 'Ваши диалоги');
    $template->assign('header', 'Ваши диалоги');
    
    $template->show('dialogs');
    
} catch (Exception $e) {
    error_log("Error in dialogs.php: " . $e->getMessage());
    $template->assign('error', 'Произошла ошибка при загрузке диалогов');
    $template->show('error');
}
?>