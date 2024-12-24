<?php
require 'config/database.php';
require 'core/Auth.php';
require 'core/helpers.php';

// Инициализация сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем JSON данные
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $recipientId = filter_var($data['recipient_id'] ?? null, FILTER_VALIDATE_INT);
    $message = trim($data['message'] ?? '');
    $senderId = $_SESSION['user_id'] ?? null;

    if (!$senderId) {
        echo json_encode(['success' => false, 'error' => 'Необходима авторизация']);
        exit;
    }

    if (!$recipientId) {
        echo json_encode(['success' => false, 'error' => 'Некорректный получатель']);
        exit;
    }

    if (empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Сообщение не может быть пустым']);
        exit;
    }

    try {
        // Вставляем новое сообщение в базу
        $stmt = $pdo->prepare('
            INSERT INTO messages (sender_id, recipient_id, message, sent_at)
            VALUES (:sender_id, :recipient_id, :message, NOW())
        ');
        
        $stmt->execute([
            ':sender_id' => $senderId,
            ':recipient_id' => $recipientId,
            ':message' => $message
        ]);

        $messageId = $pdo->lastInsertId();

        // Получаем данные отправителя
        $stmt = $pdo->prepare('
            SELECT login, avatar_color 
            FROM users 
            WHERE id = ?
        ');
        $stmt->execute([$senderId]);
        $sender = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $messageId,
                'message' => htmlspecialchars($message), // Экранирование для безопасности
                'sender' => htmlspecialchars($sender['login']), // Экранирование
                'avatar_color' => htmlspecialchars($sender['avatar_color']), // Экранирование
                'sent_at' => date('Y-m-d H:i:s'),
                'is_read' => false
            ]
        ]);
    } catch (Exception $e) {
        error_log("Error sending message: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Произошла ошибка при отправке сообщения'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Неверный метод запроса'
    ]);
}
?>