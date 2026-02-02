<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/FornecedorRepository.php';
require_once __DIR__ . '/../src/Repositories/RequisicaoRepository.php';
require_once __DIR__ . '/../src/Repositories/OfertaRepository.php';
require_once __DIR__ . '/../src/Services/FornecedorService.php';

$pdo = Database::getConnection();

$fornecedorId = (string)($_SESSION['fornecedor_id'] ?? '');
$role = (string)($_SESSION['role'] ?? '');

if ($role !== 'fornecedor' || $fornecedorId === '') {
    echo '<div class="alert alert--error">Acesso negado.</div>';
    return;
}

$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$service = new FornecedorService(
    new FornecedorRepository($pdo),
    new RequisicaoRepository($pdo),
    new OfertaRepository($pdo)
);

$requisicoes = $service->listarRequisicoesAbertas($fornecedorId);
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Requisições abertas</h1>
    <div class="page-subtitle"><?= count($requisicoes) ?> disponíveis</div>
  </div>
</div>

<?php if ($success): ?>
  <div class="alert alert--success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table class="table">
  <thead>
    <tr>
      <th>Produto</th>
      <th class="col-num">Qtd</th>
      <th>Categoria</th>
      <th class="col-date">Criada em</th>
      <th class="col-actions">Ação</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($requisicoes)): ?>
      <tr>
        <td colspan="5" style="text-align:center; color:#666;">Nenhuma requisição aberta para você no momento</td>
      </tr>
    <?php else: ?>
      <?php foreach ($requisicoes as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['produto'] ?? '-') ?></td>
          <td class="col-num"><?= (int)($r['quantidade'] ?? 0) ?></td>
          <td><?= htmlspecialchars($r['categoria_nome'] ?? '-') ?></td>
          <td class="col-date">
            <?php
              $dt = $r['created_at'] ?? null;
              echo $dt ? date('d/m/Y H:i', strtotime((string)$dt)) : '-';
            ?>
          </td>
          <td class="col-actions">
            <a class="link" href="/index.php?page=oferta_nova&requisicao_id=<?= htmlspecialchars($r['id']) ?>">Fazer oferta</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
