<?php
declare(strict_types=1);

require __DIR__ . '/../../app/core/bootstrap.php';

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000, // expira no passado
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroi a sessão
session_destroy();

// Redireciona para login
header('Location: /index.php?page=login');
exit;
