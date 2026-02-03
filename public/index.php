<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/core/bootstrap.php';

$page = $_GET['page'] ?? 'login';

$allowedPages = [
  'login','home','logout',
  'requisicoes','requisicao_nova','requisicao_detalhe',
  'fornecedor_requisicoes','oferta_nova', 'pdde', 'pdde_editar',
  'ofertas_criadas'
];

if (!in_array($page, $allowedPages, true)) {
    http_response_code(404);
    exit('Página não encontrada');
}

$isLogged = !empty($_SESSION['user_id']);
$role = (string)($_SESSION['role'] ?? '');

// Gate simples: se não está logado, só deixa acessar login
if (!$isLogged && $page !== 'login') {
    header('Location: /index.php?page=login');
    exit;
}

// Se está logado e tentou ir pra login, manda pro "home" do role
if ($isLogged && $page === 'login') {
    if ($role === 'fornecedor') {
        header('Location: /index.php?page=fornecedor_requisicoes');
        exit;
    }
    header('Location: /index.php?page=home');
    exit;
}

// Gate por role (segurança): não deixa fornecedor abrir pages de escola e vice-versa
if ($isLogged) {
    $schoolPages = ['home','requisicoes','requisicao_nova','requisicao_detalhe','pdde'];
    $supplierPages = ['fornecedor_requisicoes','oferta_nova'];

    if ($role === 'escola' && in_array($page, $supplierPages, true)) {
        http_response_code(403);
        exit('Acesso negado');
    }

    if ($role === 'fornecedor' && in_array($page, $schoolPages, true)) {
        http_response_code(403);
        exit('Acesso negado');
    }
}

$pageFile = __DIR__ . "/../app/pages/{$page}.php";
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PddeControla</title>
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body>

<?php if ($page === 'login'): ?>

  <div class="auth-wrap">
    <?php require_once $pageFile; ?>
  </div>

<?php else: ?>

  <aside class="sidebar">
    <div class="sidebar__brand">PddeControla</div>

    <nav class="sidebar__nav">
      <?php if ($role === 'escola'): ?>
        <a class="sidebar__link <?= $page === 'home' ? 'is-active' : '' ?>"
           href="/index.php?page=home">Home</a>

        <a class="sidebar__link <?= $page === 'requisicoes' ? 'is-active' : '' ?>"
           href="/index.php?page=requisicoes">Requisições</a>
        <a class="sidebar__link <?= $page === 'pdde' ? 'is-active' : '' ?>"
            href="/index.php?page=pdde">PDDE</a>
      <?php endif; ?>

      <?php if ($role === 'fornecedor'): ?>
        <a class="sidebar__link <?= $page === 'fornecedor_requisicoes' ? 'is-active' : '' ?>"
           href="/index.php?page=fornecedor_requisicoes">Requisições</a>
        <a class="sidebar__link <?= $page === 'ofertas_criadas' ? 'is-active' : '' ?>"
           href="/index.php?page=ofertas_criadas">Ofertas Criadas</a>
      <?php endif; ?>
    </nav>

    <div class="sidebar__footer">
      <div class="sidebar__user">
        <?= htmlspecialchars($_SESSION['name'] ?? 'Usuário') ?>
      </div>

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
