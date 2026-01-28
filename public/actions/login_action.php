<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/core/bootstrap.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['flash_error'] = 'Preencha email e senha.';
    header('Location: /index.php?page=login');
    exit;
}

$pdo = Database::getConnection();

$sql = <<<SQL
SELECT id, name, email, password_hash, role, active
FROM users
WHERE email = :email
LIMIT 1
SQL;

$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !$user['active'] || !password_verify($password, $user['password_hash'])) {
    $_SESSION['flash_error'] = 'Login inválido.';
    header('Location: /index.php?page=login');
    exit;
}

$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['role']    = $user['role'] ?? 'user';
$_SESSION['name']    = $user['name'];

header('Location: /index.php?page=home');
exit;
