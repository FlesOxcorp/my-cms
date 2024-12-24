<?php
require 'config/database.php';
require 'core/Auth.php';
require 'core/helpers.php';

secureSessionStart();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $userId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
        
        if (!$userId) {
            throw new Exception('Invalid user ID');
        }

        // Получение статуса пользователя
        $stmt = $pdo->prepare('
            SELECT last_activity 
            FROM users 
            WHERE id = ?
        ');
        $stmt->execute([$userId]);
        $lastActivity = $stmt->fetchColumn();

        if ($lastActivity) {
            $lastActivityTime = strtotime($lastActivity);
            $now = time();
            $diff = $now - $lastActivityTime;

            if ($diff < 300) { // 5 минут
                $status = 'В сети';
            } elseif ($diff < 3600) { // 1 час
                $minutes = floor($diff / 60);
                $status = "Был(а) $minutes минут назад";
            } elseif ($diff < 86400) { // 1 день
                $hours = floor($diff / 3600);
                $status = "Был(а) $hours часов назад";
            } else {
                $status = "Был(а) " . date('d.m.Y в H:i', $lastActivityTime);
            }
        } else {
            $status = 'Не в сети';
        }

        echo json_encode(['status' => $status]);

    } catch (Exception $e) {
        error_log("Error getting user status: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Failed to get user status'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>