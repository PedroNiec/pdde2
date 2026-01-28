<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/core/bootstrap.php';

$page = $_GET['page'] ?? 'login';

$allowedPages = ['login', 'home', 'logout', 'requisicoes'];
if (!in_array($page, $allowedPages, true)) {
    http_response_code(404);
    exit('Página não encontrada');
}

// gate simples: se não está logado, só deixa acessar login
$isLogged = !empty($_SESSION['user_id']);
if (!$isLogged && $page !== 'login') {
    header('Location: /index.php?page=login');
    exit;
}

// se está logado e tentou ir pra login, manda pra home
if ($isLogged && $page === 'login') {
    header('Location: /index.php?page=home');
    exit;
}

$pageFile = __DIR__ . "/../app/pages/{$page}.php";
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PDDE</title>
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body>

<?php if ($page === 'login'): ?>

  <div class="auth-wrap">
    <?php require_once $pageFile; ?>
  </div>

<?php else: ?>

  <aside class="sidebar">
    <div class="sidebar__brand">PDDE</div>

    <nav class="sidebar__nav">
      <a class="sidebar__link <?= $page === 'home' ? 'is-active' : '' ?>" href="/index.php?page=home">Home</a>
      <a class="sidebar__link <?= $page === 'requisicoes' ? 'is-active' : '' ?>" href="/index.php?page=requisicoes">Requisições</a>
</a>

    </nav>

    <div class="sidebar__footer">
      <div class="sidebar__user">
        <?= htmlspecialchars($_SESSION['name'] ?? 'Usuário') ?>
      </div>

      <!-- Mantive como page=logout pra bater com sua whitelist -->
      <a class="sidebar__link" href="/index.php?page=logout">Sair</a>
    </div>
  </aside>

  <main class="main">
    <?php require_once $pageFile; ?>
  </main>

<?php endif; ?>

<script src="/assets/app.js"></script>
</body>
</html>
