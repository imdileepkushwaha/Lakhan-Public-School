<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ADMIN_SESSION_TIMEOUT', 600);

function admin_destroy_session(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function admin_session_is_valid(bool $update_activity = true): bool
{
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }

    $now = time();

    if (
        isset($_SESSION['admin_last_activity']) &&
        ($now - (int) $_SESSION['admin_last_activity']) > ADMIN_SESSION_TIMEOUT
    ) {
        return false;
    }

    if ($update_activity) {
        $_SESSION['admin_last_activity'] = $now;
    }

    return true;
}

function admin_require_login(): void
{
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }

    if (
        isset($_SESSION['admin_last_activity']) &&
        (time() - (int) $_SESSION['admin_last_activity']) > ADMIN_SESSION_TIMEOUT
    ) {
        admin_destroy_session();
        header('Location: login.php?expired=1');
        exit;
    }

    $_SESSION['admin_last_activity'] = time();
}
