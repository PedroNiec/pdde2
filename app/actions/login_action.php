<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/core/bootstrap.php';

$email = trim($_POST['email'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    $_SESSION['flash_error'] = 'Preencha email e senha.';
    header('Location: /index.php?page=login');
    exit;
}

$pdo = Database::getConnection();

$sql = <<<SQL
SELECT id, name, email, password_hash, role, active, escola_id, fornecedor_id
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
$_SESSION['role']    = trim((string)($user['role'] ?? 'user'));
$_SESSION['name']    = (string)($user['name'] ?? '');
$_SESSION['escola_id'] = $user['escola_id'] ?? null;
$_SESSION['fornecedor_id'] = $user['fornecedor_id'] ?? null;


/* Gate por role + vínculo */
if ($_SESSION['role'] === 'fornecedor') {
    if (empty($_SESSION['fornecedor_id'])) {
        $_SESSION['flash_error'] = 'Usuário fornecedor sem fornecedor_id vinculado.';
        header('Location: /index.php?page=login');
        exit;
    }
    header('Location: /index.php?page=fornecedor_requisicoes');
    exit;
}

if ($_SESSION['role'] === 'escola') {
    if (empty($_SESSION['escola_id'])) {
        $_SESSION['flash_error'] = 'Usuário escola sem escola_id vinculado.';
        header('Location: /index.php?page=login');
        exit;
    }
    header('Location: /index.php?page=requisicoes');
    exit;
}

header('Location: /index.php?page=home');
exit;
