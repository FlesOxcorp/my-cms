<?php
session_start();
require 'config/database.php';
require 'core/Auth.php';
require 'core/Template.php';
require 'core/helpers.php';
require 'core/MessageHandler.php';

$auth = new Auth($pdo);
$template = new Template(__DIR__ . '/templates');
$messageHandler = new MessageHandler($pdo);

// Проверка аутентификации
if (!$auth->isAuthenticated()) {
    header('Location: login.php');
    exit;
}

// Получаем логин получателя из URL
$recipientLogin = filter_input(INPUT_GET, 'to', FILTER_SANITIZE_STRING);
$senderId = $_SESSION['user_id'] ?? null;

if (!$recipientLogin || !$senderId) {
    header('Location: index.php');
    exit;
}

try {
    // Получаем информацию о получателе
    $recipient = $messageHandler->getRecipientInfo($recipientLogin);
    
    if (!$recipient) {
        throw new Exception('Получатель не найден.');
    }

    $recipientId = $recipient['id'];

    // Проверка на самоотправку
    if ($recipientId == $senderId) {
        throw new Exception('Вы не можете отправить сообщение самому себе.');
    }

    // Получаем историю сообщений
    $messages = $messageHandler->getMessageHistory($senderId, $recipientId);
    
    // Помечаем сообщения как прочитанные
    $messageHandler->markMessagesAsRead($senderId, $recipientId);

    // Получаем информацию о статусе пользователя
    $recipientStatus = $messageHandler->getUserStatus($recipientId);

    // Передаем данные в шаблон
    $template->assign('recipient', htmlspecialchars($recipientLogin));
    $template->assign('recipientAvatarColor', $recipient['avatar_color']);
    $template->assign('recipientStatus', $recipientStatus);
    $template->assign('senderId', $senderId);
    $template->assign('recipientId', $recipientId);
    $template->assign('messages', $messages);
    $template->assign('title', "Диалог с " . $recipientLogin);
    $template->assign('header', "Диалог с " . $recipientLogin);

    $template->show('message');

} catch (Exception $e) {
    $template->assign('error', $e->getMessage());
    $template->show('error');
}
?>