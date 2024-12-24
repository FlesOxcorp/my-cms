<?php
require 'config/database.php';
require 'core/Auth.php';
require 'core/CSRF.php';
require 'core/Template.php';
require 'core/helpers.php';

secureSessionStart();

try {
    if (!isset($_SESSION['user_id'])) {
        $auth = new Auth($pdo);
        $template = new Template(__DIR__ . '/templates');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkCSRFToken();

            $username = sanitizeInput($_POST['username']);
            $password = $_POST['password'];

            if (empty($username) || empty($password)) {
                $template->assign('error', 'Please fill in all fields');
            } else {
                if ($auth->login($username, $password)) {
                    regenerateSessionId();
                    CSRF::destroyToken();
                    header('Location: index.php');
                    exit;
                } else {
                    $template->assign('error', 'Invalid username or password');
                }
            }
        }

        $template->assign('csrf_token', CSRF::generateToken());
        $template->show('login');
    } else {
        header('Location: index.php');
    }
} catch (Throwable $e) {
    handleException($e);
}
?>
