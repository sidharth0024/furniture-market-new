<?php
/**
 * logout.php — Destroy the admin session and redirect to the login page.
 */
declare(strict_types=1);
session_start();
session_unset();
session_destroy();

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']);
}

header('Location: index.php');
exit;
