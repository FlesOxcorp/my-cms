<?php
require 'config/database.php';
require 'core/Auth.php';
require 'core/helpers.php';

secureSessionStart();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$recipientId = filter_input(INPUT_GET, 'recipient_id', FILTER_VALIDATE_INT);

if (!$recipientId) {
    echo json_encode(['success' => false, 'error' => 'Invalid recipient']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        SELECT id 
        FROM messages 
        WHERE sender_id = ? AND recipient_id = ? AND is_read = 1
    ');
    $stmt->execute([$_SESSION['user_id'], $recipientId]);
    
    echo json_encode([
        'success' => true,
        'read_messages' => $stmt->fetchAll(PDO::FETCH_COLUMN)
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
?>