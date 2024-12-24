<?php
require 'config/database.php';
require 'core/Auth.php';
require 'core/helpers.php';

// Инициализация сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!isset($data['message_ids']) || !isset($data['sender_id'])) {
            throw new Exception('Missing required parameters');
        }

        $messageIds = array_map('intval', $data['message_ids']);
        $senderId = (int)$data['sender_id'];
        $recipientId = $_SESSION['user_id'];

        if (empty($messageIds) || !$recipientId) {
            throw new Exception('Invalid parameters');
        }

        // Обновление статуса сообщений
        $placeholders = str_repeat('?,', count($messageIds) - 1) . '?';
        $stmt = $pdo->prepare("
            UPDATE messages 
            SET is_read = 1 
            WHERE id IN ($placeholders)
            AND sender_id = ?
            AND recipient_id = ?
        ");

        $params = array_merge($messageIds, [$senderId, $recipientId]);
        $stmt->execute($params);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        error_log("Error updating message status: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to update message status'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>