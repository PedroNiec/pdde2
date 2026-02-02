<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/PddeRepository.php';

$pdo = Database::getConnection();

$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$escolaId = (string)($_SESSION['escola_id'] ?? '');

$repo = new PddeRepository($pdo);
$pddes = $escolaId ? $repo->listarPorEscola($escolaId) : [];
?>

<div class="page-header">
  <div>
    <h1 class="page-title">PDDES</h1>
    <div class="page-subtitle">Crie e visualize os PDDES da escola</div>
  </div>
</div>

<?php if ($success): ?>
  <div class="alert alert--success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="/actions/criar_pdde_action.php" class="form" style="margin-bottom: 18px;">
  <div class="form-row">
    <div class="field" style="flex: 2;">
      <label for="nome">Nome do PDDE</label>
      <input type="text" id="nome" name="nome" required placeholder="Ex.: PDDE Estadual">
    </div>

    <div class="field" style="max-width: 260px;">
      <label for="saldo_inicial">Saldo Inicial</label>
      <input type="number" id="saldo_inicial" name="saldo_inicial" required step="0.01" min="0" placeholder="0,00">
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn-primary">Criar PDDE</button>
  </div>
</form>

<table class="table">
  <thead>
    <tr>
      <th>Nome</th>
      <th class="col-num">Saldo inicial</th>
      <th class="col-num">Disponível</th>
      <th class="col-num">Bloqueado</th>
      <th class="col-num">Gasto</th>
      <th class="col-date">Criado em</th>
      <th class="col-actions">Ações</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($pddes)): ?>
      <tr>
        <td colspan="6" style="text-align:center; color:#666;">Nenhum PDDE cadastrado</td>
      </tr>
    <?php else: ?>
      <?php foreach ($pddes as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['nome'] ?? '-') ?></td>

          <td class="col-num">
            <?php
              $v = (float)($p['saldo_inicial'] ?? 0);
              echo 'R$ ' . number_format($v, 2, ',', '.');
            ?>
          </td>

          <td class="col-num">
            <?php
              $v = (float)($p['saldo_disponivel'] ?? 0);
              echo 'R$ ' . number_format($v, 2, ',', '.');
            ?>
          </td>

          <td class="col-num">
            <?php
              $v = (float)($p['saldo_bloqueado'] ?? 0);
              echo 'R$ ' . number_format($v, 2, ',', '.');
            ?>
          </td>

          <td class="col-num">
            <?php
              $v = (float)($p['saldo_gasto'] ?? 0);
              echo 'R$ ' . number_format($v, 2, ',', '.');
            ?>
          </td>

          <td class="col-date">
            <?php
              $dt = $p['created_at'] ?? null;
              echo $dt ? date('d/m/Y H:i', strtotime((string)$dt)) : '-';
            ?>
          </td>

          <td class="col-actions">
            <a class="link" href="/index.php?page=pdde_editar&id=<?= htmlspecialchars($p['id']) ?>">Editar</a>
          </td>

        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
