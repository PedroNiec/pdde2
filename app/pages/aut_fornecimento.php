<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/AutorizacoesRepository.php';

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

$repository = new AutorizacoesRepository($pdo);

$autorizacoes = $repository->listarAutorizacoesPorOfertaVencedora($fornecedorId);


?>


<div class="ui">

  <div class="page-header">
    <div>
      <h1 class="page-title">Autorizações de fornecimento</h1>
      <div class="page-subtitle">
        <span><?= count($autorizacoes) ?> registr<?= count($autorizacoes) === 1 ? 'o' : 'os' ?></span>
        <span class="muted">•</span>
        <span class="muted"><?= date('d/m/Y H:i') ?></span>
      </div>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="alert alert--success"><?= htmlspecialchars((string)$success) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert--error"><?= htmlspecialchars((string)$error) ?></div>
  <?php endif; ?>

  <?php if (empty($autorizacoes)): ?>
    <div class="empty">
      <div class="empty__icon">🏷️</div>
      <div class="empty__title">Nenhuma autorização disponível</div>
    </div>
  <?php else: ?>
    <div class="card table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Escola</th>
            <th>Produto</th>
            <th class="col-num">Qtd</th>
            <th class="col-num">Valor total</th>
            <th class="col-date">Data de criação</th>
            <th class="col-date">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($autorizacoes as $aut): ?>
            <?php $escola = (string)($aut['nome']); ?>
            <tr>
              <td><?= htmlspecialchars((string)$escola) ?></td>
              <td><?= htmlspecialchars((string)($aut['produto'] ?? '-')) ?></td>
              <td class="col-num"><?= (int)($aut['quantidade'] ?? 0) ?></td>

              <td class="col-num">
                <span class="price">
                  <?php
                    echo 'R$ ' . number_format((float)$aut['valor_total'], 2, ',', '.');
                  ?>
                </span>
              </td>

              <td class="col-date">
                <?php
                  $dt = $aut['data_criacao'] ?? null;
                  echo $dt ? date('d/m/Y H:i', strtotime((string)$dt)) : '-';
                ?>
              </td>

                <td class="col-actions">
                    <a class="btn" href="<?= $aut['public_url']?>" target="_blank" rel="noopener noreferrer">
                        Download
                    </a>
                </td>

            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>
