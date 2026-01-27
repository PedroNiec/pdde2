<?php
$page = $_GET['page'] ?? 'login';

$routes = [
    'login' => __DIR__ . '/../app/pages/login.php',
    'home'  => __DIR__ . '/../app/pages/home.php',
    'users' => __DIR__ . '/../app/pages/users_list.php',
    'users_form' => __DIR__ . '/../app/pages/users_form.php',
];

require $routes[$page] ?? $routes['login'];
