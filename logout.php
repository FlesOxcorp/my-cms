<?php
session_start();
require 'config/database.php';
require 'core/Auth.php';

$auth = new Auth($pdo);

// Удаление ключей шифрования из сессии
unset($_SESSION['encryptionKey']);
unset($_SESSION['publicKey']);
unset($_SESSION['secretKey']);

// Завершение сессии
$auth->logout();

header('Location: login.php');
exit;
?>
