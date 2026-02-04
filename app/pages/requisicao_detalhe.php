<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/RequisicaoRepository.php';
require_once __DIR__ . '/../src/Repositories/PddeRepository.php';
require_once __DIR__ . '/../src/Repositories/CategoriaRepository.php';
require_once __DIR__ . '/../src/Repositories/OfertaRepository.php';
require_once __DIR__ . '/../src/Services/RequisicaoService.php';

$pdo = Database::getConnection();

$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);


$repo = new RequisicaoRepository($pdo);
$pddeRepo = new PddeRepository($pdo);
$catRepo = new CategoriaRepository($pdo);
$ofertaRepo = new OfertaRepository($pdo);

$service = new RequisicaoService($repo, $pddeRepo, $catRepo, $ofertaRepo);

$escolaId = (string)($_SESSION['escola_id'] ?? '');
$requisicaoId = (string)($_GET['id'] ?? '');

try {
    $req = $service->buscarDetalheParaEscola($requisicaoId, $escolaId);
    $ofertas = $service->listarOfertasDaRequisicaoParaEscola($requisicaoId, $escolaId);
} catch (Throwable $e) {
    echo '<div class="alert alert--error">' . htmlspecialchars($e->getMessage()) . '</div>';
    echo '<a class="btn-secondary" href="/index.php?page=requisicoes">Voltar</a>';
    return;
}
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Detalhes da requisição</h1>
    <div class="page-subtitle">ID: <?= htmlspecialchars($req['id']) ?></div>
  </div>
  <a href="/index.php?page=requisicoes" class="btn-secondary">Voltar</a>
</div>

<?php if ($success): ?>
  <div class="alert alert--success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>



<div class="card">
  <div class="kv-grid">
    <div class="kv">
      <div class="kv__label">Produto</div>
      <div class="kv__value"><?= htmlspecialchars($req['produto'] ?? '-') ?></div>
    </div>

    <div class="kv">
      <div class="kv__label">Quantidade</div>
      <div class="kv__value"><?= (int)($req['quantidade'] ?? 0) ?></div>
    </div>

    <div class="kv">
      <div class="kv__label">PDDE</div>
      <div class="kv__value"><?= htmlspecialchars($req['pdde_nome'] ?? '-') ?></div>
    </div>

    <div class="kv">
      <div class="kv__label">Categoria</div>
      <div class="kv__value"><?= htmlspecialchars($req['categoria_nome'] ?? '-') ?></div>
    </div>

    <div class="kv">
      <div class="kv__label">Status</div>
      <div class="kv__value"><?= htmlspecialchars($req['status'] ?? '-') ?></div>
    </div>

    <div class="kv">
      <div class="kv__label">Criada em</div>
      <div class="kv__value">
        <?php
          $dt = $req['created_at'] ?? null;
          echo $dt ? date('d/m/Y H:i', strtotime((string)$dt)) : '-';
        ?>
      </div>
    </div>
  </div>
</div>

<?php
$isAberta = (($req['status'] ?? '') === 'aberta');
$temOfertaSelecionada = !empty($req['oferta_selecionada_id']);
?>

<?php if ($isAberta && $temOfertaSelecionada): ?>
  <div class="action-bar">
    <form method="POST" action="/index.php?action=requisicao_store" style="margin:0;">
      <input type="hidden" name="requisicao_id" value="<?= htmlspecialchars($req['id']) ?>">
      <button type="submit" class="btn-primary">
        Iniciar compra
      </button>
    </form>
  </div>
<?php endif; ?>

<?php
$isEmCompra = (($req['status'] ?? '') === 'em_compra');
?>

<?php if ($isEmCompra): ?>
  <div class="action-bar" style="margin: 12px 0 16px;">
    <form method="POST" action="/index.php?action=requisicao_concluir_compra" style="margin:0;">
      <input type="hidden" name="requisicao_id" value="<?= htmlspecialchars($req['id']) ?>">
      <button type="submit" class="btn-primary">Concluir compra</button>
    </form>
  </div>
<?php endif; ?>



<h2 class="section-title">Ofertas</h2>

<table class="table">
  <thead>
    <tr>
      <th>Fornecedor</th>
      <th class="col-num">Valor unitário</th>
      <th class="col-val">Valor total</th>
      <th class="col-date">Enviada em</th>
      <th class="col-actions">Ação</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($ofertas)): ?>
      <tr>
        <td colspan="3" style="text-align:center; color:#666;">
          Nenhuma oferta recebida ainda
        </td>
      </tr>
    <?php else: ?>
      <?php foreach ($ofertas as $o): ?>
        <tr>
          <td><?= htmlspecialchars($o['fornecedor_nome'] ?? '-') ?></td>
          <td class="col-num">
            <?php
              $v = (string)($o['valor_unitario'] ?? '0');
              echo 'R$ ' . number_format((float)$v, 2, ',', '.');
            ?>
          </td>
          <td class="col-val">
            <?php
              $v = (string)($o['valor_total'] ?? '0');
              echo 'R$ ' . number_format((float)$v, 2, ',', '.');
            ?>
          </td>
          <td class="col-date">
            <?php
              $odt = $o['created_at'] ?? null;
              echo $odt ? date('d/m/Y H:i', strtotime((string)$odt)) : '-';
            ?>
          </td>
          <td class="col-actions">
  <?php
    $selecionadaId = (string)($req['oferta_selecionada_id'] ?? '');
    $thisId = (string)($o['id'] ?? '');
    $isSelected = $selecionadaId !== '' && $selecionadaId === $thisId;
    $isAberta = (($req['status'] ?? '') === 'aberta');
  ?>

  <?php if ($isSelected): ?>
    <span class="badge badge--done">Selecionada</span>

  <?php elseif ($isAberta): ?>
    <form method="POST" action="/index.php?action=oferta_selecionar" style="margin:0;">
      <input type="hidden" name="requisicao_id" value="<?= htmlspecialchars($req['id']) ?>">
      <input type="hidden" name="oferta_id" value="<?= htmlspecialchars($thisId) ?>">
      <button type="submit" class="btn-small">Selecionar</button>
    </form>

  <?php else: ?>
    <span class="muted">-</span>
  <?php endif; ?>
</td>

        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
