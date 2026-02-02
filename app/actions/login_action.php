<?php
require __DIR__ . '/../core/bootstrap.php';

echo 'CHEGANDO AQUI LOGIN ACTION';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['flash_error'] = 'Preencha email e senha';
    header('Location: /index.php?page=login');
    exit;
}

// busca usu�rio no banco
$sql = 'SELECT id, name, email, password_hash, role, active
        FROM users
        WHERE email = :email
        LIMIT 1';

$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// valida usu�rio
if (
    !$user ||
    !$user['active'] ||
    !password_verify($password, $user['password_hash'])
) {
    $_SESSION['flash_error'] = 'Login inv�lido';
    header('Location: /index.php?page=login');
    exit;
}

// login OK ? cria sess�o
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'] ?? 'user';
$_SESSION['name']    = $user['name'];

// redirect p�s-login
header('Location: /index.php?page=home');
exit;
