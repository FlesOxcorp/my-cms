<?php
session_start();
require 'config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        SELECT 
            login, 
            avatar_color,
            last_activity
        FROM users
        WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        AND id != ?
        ORDER BY last_activity DESC
        LIMIT 5
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['users' => $users]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>