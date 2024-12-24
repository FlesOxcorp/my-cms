<?php
require_once 'Crypto.php';

class Auth {
    protected PDO $db;

    public const PASSWORD_MIN_LENGTH = 8;
    public const LOGIN_MIN_LENGTH = 5;
    public const LOGIN_MAX_LENGTH = 32;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    private function initSession(): void {
        // Конфигурация сессии
        ini_set('session.gc_maxlifetime', 3600); // Время жизни сессии на сервере
        ini_set('session.cookie_lifetime', 0); // Время жизни cookie сессии - до закрытия браузера
        session_start();
    }

    public function validateLogin(string $login): array {
        $errors = [];
        
        if (strlen($login) < self::LOGIN_MIN_LENGTH || strlen($login) > self::LOGIN_MAX_LENGTH) {
            $errors[] = "Логин должен быть от " . self::LOGIN_MIN_LENGTH . " до " . self::LOGIN_MAX_LENGTH . " символов";
        }
        
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $login)) {
            $errors[] = "Логин может содержать только буквы, цифры, тире и подчеркивание";
        }
        
        return $errors;
    }

    public function validatePassword(string $password): array {
        $errors = [];
        
        if (strlen($password) < self::PASSWORD_MIN_LENGTH) {
            $errors[] = "Пароль должен быть не менее " . self::PASSWORD_MIN_LENGTH . " символов";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Пароль должен содержать хотя бы одну заглавную букву";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Пароль должен содержать хотя бы одну строчную букву";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Пароль должен содержать хотя бы одну цифру";
        }
        
        return $errors;
    }

    public function generateAvatarColor(): string {
        // Расширенная палитра цветов
        $colors = [
            '#FF5733', '#33FF57', '#5733FF', '#FF33A1', '#33FFF3', 
            '#F3FF33', '#FF8C33', '#33FF8C', '#8C33FF', '#FF338C',
            '#338CFF', '#8CFF33', '#3366FF', '#FF3366', '#66FF33'
        ];
        return $colors[array_rand($colors)];
    }

    private function isLoginTaken(string $login): bool {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE LOWER(login) = LOWER(?)');
        $stmt->execute([trim($login)]);
        return $stmt->fetchColumn() > 0;
    }

    public function register(string $username, string $login, string $password): array {
        $response = ['success' => false, 'errors' => []];
        
        // Валидация логина
        $loginErrors = $this->validateLogin($login);
        if (!empty($loginErrors)) {
            $response['errors'] = array_merge($response['errors'], $loginErrors);
        }
        
        // Валидация пароля
        $passwordErrors = $this->validatePassword($password);
        if (!empty($passwordErrors)) {
            $response['errors'] = array_merge($response['errors'], $passwordErrors);
        }
        
        // Проверка существования пользователя
        if ($this->isLoginTaken($login)) {
            $response['errors'][] = "Этот логин уже занят";
        }

        if (!empty($response['errors'])) {
            return $response;
        }

        try {
            // Хеширование пароля с использованием более безопасных настроек
            $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
            
            // Генерация цвета аватара
            $avatarColor = $this->generateAvatarColor();
            
            // Добавление пользователя в базу данных
            $stmt = $this->db->prepare(
                'INSERT INTO users (username, login, password, avatar_color, last_activity) 
                VALUES (?, ?, ?, ?, NOW())'
            );
            
            if ($stmt->execute([
                trim($username),
                trim($login),
                $hashedPassword,
                $avatarColor
            ])) {
                $response['success'] = true;
            }
        } catch (PDOException $e) {
            $response['errors'][] = "Ошибка при регистрации пользователя";
            error_log("Registration error: " . $e->getMessage());
        }

        return $response;
    }

    public function login(string $username, string $password): array {
        $response = ['success' => false, 'errors' => []];

        try {
            $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ?');
            $stmt->execute([trim($username)]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Обновление хеша пароля, если используется устаревший алгоритм
                if (password_needs_rehash($user['password'], PASSWORD_ARGON2ID)) {
                    $newHash = password_hash($password, PASSWORD_ARGON2ID);
                    $updateStmt = $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $updateStmt->execute([$newHash, $user['id']]);
                }

                // Обновление времени последней активности
                $this->updateLastActivity($user['id']);

                // Безопасная инициализация сессии
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['login'] = $user['login'];
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                $_SESSION['last_activity'] = time();

                $response['success'] = true;
            } else {
                $response['errors'][] = "Неверное имя пользователя или пароль";
            }
        } catch (PDOException $e) {
            $response['errors'][] = "Ошибка при входе в систему";
            error_log("Login error: " . $e->getMessage());
        }

        return $response;
    }

    public function updateLastActivity(int $userId): void {
        try {
            $stmt = $this->db->prepare('UPDATE users SET last_activity = NOW() WHERE id = ?');
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Error updating last activity: " . $e->getMessage());
        }
    }

    public function isAuthenticated(): bool {
        if (!isset($_SESSION['user_id']) || 
            !isset($_SESSION['user_agent']) || 
            $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            return false;
        }

        // Проверка времени последней активности (30 минут)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            $this->logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public function logout(): void {
        // Очистка всех данных сессии
        $_SESSION = array();

        // Уничтожение cookie сессии
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Уничтожение сессии
        session_destroy();
    }
}
?>