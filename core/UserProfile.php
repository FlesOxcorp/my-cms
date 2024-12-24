<?php
class UserProfile {
    private PDO $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getUserInfo(string $login): ?array {
        $stmt = $this->db->prepare('
            SELECT 
                u.id,
                u.login,
                u.created_at,
                u.last_activity,
                u.avatar_color,
                (SELECT COUNT(*) FROM messages WHERE sender_id = u.id) as messages_sent,
                (SELECT COUNT(DISTINCT 
                    CASE 
                        WHEN sender_id = u.id THEN recipient_id 
                        WHEN recipient_id = u.id THEN sender_id 
                    END
                ) FROM messages WHERE sender_id = u.id OR recipient_id = u.id) as dialogs_count
            FROM users u
            WHERE u.login = :login
        ');
        $stmt->bindValue(':login', $login, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLastActivity(int $userId): string {
        $stmt = $this->db->prepare('
            SELECT last_activity 
            FROM users 
            WHERE id = :user_id
        ');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $lastActivity = $stmt->fetchColumn();

        if (!$lastActivity) {
            return 'Никогда не был онлайн';
        }

        $lastActivityTime = strtotime($lastActivity);
        $now = time();
        $diff = $now - $lastActivityTime;

        if ($diff < 300) { // 5 минут
            return 'В сети';
        } elseif ($diff < 3600) { // 1 час
            $minutes = floor($diff / 60);
            return "Был(а) $minutes " . $this->pluralize($minutes, 'минуту', 'минуты', 'минут') . " назад";
        } elseif ($diff < 86400) { // 1 день
            $hours = floor($diff / 3600);
            return "Был(а) $hours " . $this->pluralize($hours, 'час', 'часа', 'часов') . " назад";
        } else {
            return "Был(а) " . date('d.m.Y в H:i', $lastActivityTime);
        }
    }

    public function getRecentDialogs(int $userId): array {
        $stmt = $this->db->prepare('
            SELECT DISTINCT
                u.login,
                u.avatar_color,
                m.sent_at
            FROM messages m
            JOIN users u ON (
                CASE 
                    WHEN m.sender_id = :user_id THEN m.recipient_id = u.id
                    WHEN m.recipient_id = :user_id THEN m.sender_id = u.id
                END
            )
            WHERE m.sender_id = :user_id OR m.recipient_id = :user_id
            ORDER BY m.sent_at DESC
            LIMIT 5
        ');
        
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function updateLastActivity(int $userId): void {
        $stmt = $this->db->prepare('
            UPDATE users 
            SET last_activity = NOW() 
            WHERE id = :user_id
        ');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
}
?>