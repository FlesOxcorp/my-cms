<?php

class DialogHandler {
    private PDO $db;
    private const ONLINE_THRESHOLD = 300; // 5 минут

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getUserDialogs(int $userId, ?string $searchQuery = null): array {
        $query = "
            SELECT DISTINCT
                u.id,
                u.login,
                u.avatar_color,
                u.last_activity,
                (SELECT MAX(m.sent_at)
                 FROM messages m
                 WHERE (m.sender_id = u.id AND m.recipient_id = :userId)
                    OR (m.sender_id = :userId AND m.recipient_id = u.id)) as last_message_time
            FROM users u
            JOIN messages m ON (m.sender_id = u.id OR m.recipient_id = u.id)
            WHERE u.id != :userId
            AND (m.sender_id = :userId OR m.recipient_id = :userId)
        ";

        if ($searchQuery) {
            $query .= " AND u.login LIKE :search";
        }

        $query .= " ORDER BY last_message_time DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        
        if ($searchQuery) {
            $stmt->bindValue(':search', "%{$searchQuery}%", PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadCount(int $userId, int $senderId): int {
        $stmt = $this->db->prepare('
            SELECT COUNT(*)
            FROM messages
            WHERE recipient_id = ?
            AND sender_id = ?
            AND is_read = 0
        ');
        $stmt->execute([$userId, $senderId]);
        return (int)$stmt->fetchColumn();
    }

    public function getLastMessage(int $userId, int $partnerId): ?array {
        $stmt = $this->db->prepare('
            SELECT 
                m.message,
                m.sent_at,
                m.sender_id,
                m.is_read,
                u.login as sender_login
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE (m.sender_id = ? AND m.recipient_id = ?)
               OR (m.sender_id = ? AND m.recipient_id = ?)
            ORDER BY m.sent_at DESC
            LIMIT 1
        ');
        
        $stmt->execute([$userId, $partnerId, $partnerId, $userId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$message) {
            return null;
        }

        // Форматируем время сообщения
        $message['formatted_time'] = $this->formatMessageTime($message['sent_at']);
        $message['is_own'] = $message['sender_id'] == $userId;

        return $message;
    }

    public function getUserStatus(int $userId): array {
        $stmt = $this->db->prepare('
            SELECT last_activity
            FROM users
            WHERE id = ?
        ');
        $stmt->execute([$userId]);
        $lastActivity = strtotime($stmt->fetchColumn());

        if (!$lastActivity) {
            return ['status' => 'offline', 'text' => 'не в сети'];
        }

        $timeDiff = time() - $lastActivity;

        if ($timeDiff < self::ONLINE_THRESHOLD) {
            return ['status' => 'online', 'text' => 'в сети'];
        } else {
            return [
                'status' => 'offline',
                'text' => 'был(а) ' . $this->formatLastActivity($timeDiff)
            ];
        }
    }

    private function formatMessageTime(string $timestamp): string {
        $messageTime = strtotime($timestamp);
        $now = time();
        $diff = $now - $messageTime;
        
        if ($diff < 60) {
            return 'только что';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return "{$minutes} " . $this->pluralize($minutes, 'минуту', 'минуты', 'минут') . " назад";
        } elseif ($diff < 86400) {
            return date('H:i', $messageTime);
        } elseif ($diff < 604800) { // неделя
            $days = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
            return $days[date('w', $messageTime)];
        } else {
            return date('d.m.Y', $messageTime);
        }
    }

    private function formatLastActivity(int $seconds): string {
        if ($seconds < 60) {
            return "только что";
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            return "{$minutes} " . $this->pluralize($minutes, 'минуту', 'минуты', 'минут') . " назад";
        } elseif ($seconds < 86400) {
            $hours = floor($seconds / 3600);
            return "{$hours} " . $this->pluralize($hours, 'час', 'часа', 'часов') . " назад";
        } else {
            $days = floor($seconds / 86400);
            return "{$days} " . $this->pluralize($days, 'день', 'дня', 'дней') . " назад";
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
}
?>