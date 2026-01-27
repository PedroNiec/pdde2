<?php
require __DIR__ . '/../core/bootstrap.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['flash_error'] = 'Preencha email e senha';
    header('Location: /index.php?page=login');
    exit;
}

// busca usuário no banco
$sql = 'SELECT id, name, email, password_hash, role, active
        FROM users
        WHERE email = :email
        LIMIT 1';

$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// valida usuário
if (
    !$user ||
    !$user['active'] ||
    !password_verify($password, $user['password_hash'])
) {
    $_SESSION['flash_error'] = 'Login inválido';
    header('Location: /index.php?page=login');
    exit;
}

// login OK ? cria sessão
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'] ?? 'user';
$_SESSION['name']    = $user['name'];

// redirect pós-login
header('Location: /index.php?page=home');
exit;
