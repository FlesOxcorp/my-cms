<?php
function checkAuthentication() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        header('Location: login.php');
        exit;
    }
}

function regenerateSessionId() {
    session_regenerate_id(true);
}

function validateCSRFToken($token) {
    if (!CSRF::validateToken($token)) {
        die('Invalid CSRF token');
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

function getUserDialogs(PDO $pdo, $userId) {
    $stmt = $pdo->prepare('
        SELECT u.id, u.login, u.avatar_color, MAX(m.sent_at) AS last_message_time
        FROM users u
        JOIN messages m ON (m.sender_id = u.id OR m.recipient_id = u.id)
        WHERE (m.sender_id = ? OR m.recipient_id = ?)
        AND u.id != ?
        GROUP BY u.id
        ORDER BY last_message_time DESC
    ');
    $stmt->execute([$userId, $userId, $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPaginatedUsers(PDO $pdo, $limit, $offset) {
    $stmt = $pdo->prepare('SELECT id, login, avatar_color FROM users LIMIT :limit OFFSET :offset');
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalUsers(PDO $pdo) {
    $stmt = $pdo->query('SELECT COUNT(*) FROM users');
    return $stmt->fetchColumn();
}











function htmlspecialchars_array($array) {
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = htmlspecialchars_array($value);
        } else {
            $array[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
    }
    return $array;
}

function checkCSRFToken() {
    if (!isset($_POST['csrf_token']) || !CSRF::validateToken($_POST['csrf_token'])) {
        die('CSRF validation failed.');
    }
}

function secureSessionStart() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        session_start();
        session_regenerate_id(true);
    }
}

function handleException(Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo 'An error occurred. Please try again later.';
    exit;
}

set_exception_handler('handleException');
?>
