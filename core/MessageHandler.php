<?php

class MessageHandler {
    private PDO $db;
    private const MESSAGE_LIMIT = 50; // Лимит сообщений для загрузки
    private const ONLINE_THRESHOLD = 300; // 5 минут для статуса "онлайн"

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getRecipientInfo(string $login): ?array {
        $stmt = $this->db->prepare('
            SELECT id, login, avatar_color, last_activity 
            FROM users 
            WHERE login = ?
        ');
        $stmt->execute([$login]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMessageHistory(int $senderId, int $recipientId, ?int $lastMessageId = null): array {
        $query = '
            SELECT 
                m.id,
                m.message,
                m.sent_at,
                m.is_read,
                u.login AS sender,
                u.avatar_color,
                u.last_activity
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE (m.sender_id = :senderId AND m.recipient_id = :recipientId)
               OR (m.sender_id = :recipientId AND m.recipient_id = :senderId)
        ';

        if ($lastMessageId) {
            $query .= ' AND m.id > :lastMessageId';
        }

        $query .= ' ORDER BY m.sent_at DESC LIMIT :limit';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':senderId', $senderId, PDO::PARAM_INT);
        $stmt->bindValue(':recipientId', $recipientId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', self::MESSAGE_LIMIT, PDO::PARAM_INT);

        if ($lastMessageId) {
            $stmt->bindValue(':lastMessageId', $lastMessageId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function markMessagesAsRead(int $recipientId, int $senderId): void {
        $stmt = $this->db->prepare('
            UPDATE messages 
            SET is_read = 1 
            WHERE recipient_id = ? AND sender_id = ? AND is_read = 0
        ');
        $stmt->execute([$recipientId, $senderId]);
    }

    public function getUserStatus(int $userId): string {
        $stmt = $this->db->prepare('
            SELECT last_activity 
            FROM users 
            WHERE id = ?
        ');
        $stmt->execute([$userId]);
        $lastActivity = strtotime($stmt->fetchColumn());

        if (!$lastActivity) {
            return 'offline';
        }

        $timeDiff = time() - $lastActivity;
        
        if ($timeDiff < self::ONLINE_THRESHOLD) {
            return 'online';
        } elseif ($timeDiff < 86400) { // 24 часа
            return 'был(а) ' . $this->formatLastActivity($timeDiff);
        } else {
            return 'offline';
        }
    }

    private function formatLastActivity(int $seconds): string {
        if ($seconds < 60) {
            return "только что";
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            return "{$minutes} " . $this->pluralize($minutes, 'минуту', 'минуты', 'минут') . " назад";
        } else {
            $hours = floor($seconds / 3600);
            return "{$hours} " . $this->pluralize($hours, 'час', 'часа', 'часов') . " назад";
        }
    }

    private function pluralize(int $number, string $one, string $two, string $many): string {
        if ($number % 10 == 1 && $number % 100 != 11) {
            return $one;
        }
        if ($number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 10 || $number % 100 >= 20)) {
            return $two;
        }
        return $many;
    }

    public function saveMessage(int $senderId, int $recipientId, string $message): array {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO messages (sender_id, recipient_id, message, sent_at)
                VALUES (:sender_id, :recipient_id, :message, NOW())
            ');

            $stmt->execute([
                ':sender_id' => $senderId,
                ':recipient_id' => $recipientId,
                ':message' => $message
            ]);

            $messageId = $this->db->lastInsertId();

            // Получаем информацию о сообщении для ответа
            $stmt = $this->db->prepare('
                SELECT 
                    m.id,
                    m.message,
                    m.sent_at,
                    u.login as sender,
                    u.avatar_color
                FROM messages m
                JOIN users u ON u.id = m.sender_id
                WHERE m.id = ?
            ');
            $stmt->execute([$messageId]);
            
            return [
                'success' => true,
                'data' => $stmt->fetch(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {
            error_log("Error saving message: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Не удалось отправить сообщение'
            ];
        }
    }
}
?>