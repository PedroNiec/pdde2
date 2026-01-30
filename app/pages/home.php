<?php
declare(strict_types=1);

require __DIR__ . '/../../app/core/bootstrap.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /index.php?page=login');
    exit;
}

$userName = $_SESSION['name'] ?? 'Usuário';
$userRole = $_SESSION['role'] ?? 'user';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <h2>Home</h2>

    <p>Bem-vindo, <?= htmlspecialchars($userName, ENT_QUOTES | ENT_HTML5) ?></p>
    <p>Perfil: <?= htmlspecialchars($userRole, ENT_QUOTES | ENT_HTML5) ?></p>

    <a href="/index.php?page=logout">Sair</a>

</body>
</html>
