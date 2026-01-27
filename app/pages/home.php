<?php
if (empty($_SESSION['user_id'])) {
    header('Location: /index.php?page=login');
    exit;
}
?>

<h2>Home</h2>

<p>Bem-vindo, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Usuário'); ?></p>

<p>Perfil: <?php echo htmlspecialchars($_SESSION['role']); ?></p>

<a href="/app/actions/logout.php">Sair</a>
