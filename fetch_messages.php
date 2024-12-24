<?php
require 'config/database.php';
require 'core/Auth.php';
require 'core/helpers.php';

// Инициализация сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new Auth($pdo);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $recipientId = filter_input(INPUT_GET, 'recipient_id', FILTER_VALIDATE_INT);
        $senderId = $_SESSION['user_id'];
        $lastMessageId = filter_input(INPUT_GET, 'last_message_id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        if (!$recipientId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid recipient ID']);
            exit;
        }

        // Формируем базовый запрос для сообщений
        $query = '
            SELECT m.id, m.message, m.sent_at, u.login AS sender, u.avatar_color
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE (m.sender_id = :senderId AND m.recipient_id = :recipientId) 
               OR (m.sender_id = :recipientId AND m.recipient_id = :senderId)
        ';
        
        // Добавляем условие для получения только новых сообщений, если last_message_id передан
        if ($lastMessageId !== null) {
            $query .= ' AND m.id > :lastMessageId';
        }

        // Добавляем сортировку по времени отправки
        $query .= ' ORDER BY m.sent_at ASC';

        // Подготовка и выполнение запроса
        $stmt = $pdo->prepare($query);
        $params = ['senderId' => $senderId, 'recipientId' => $recipientId];

        // Если last_message_id передан, добавляем его в параметры
        if ($lastMessageId !== null) {
            $params['lastMessageId'] = $lastMessageId;
        }

        $stmt->execute($params);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Экранируем и отправляем сообщения
        $messages = array_map(function ($msg) {
            return [
                'id' => $msg['id'],
                'message' => htmlspecialchars($msg['message']),  // Экранирование
                'sent_at' => $msg['sent_at'],
                'sender' => htmlspecialchars($msg['sender']),  // Экранирование
                'avatar_color' => htmlspecialchars($msg['avatar_color'])  // Экранирование
            ];
        }, $messages);

        // Отправка ответа
        header('Content-Type: application/json');
        echo json_encode(['messages' => $messages]);
    }
} catch (Throwable $e) {
    error_log("Error fetching messages: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>