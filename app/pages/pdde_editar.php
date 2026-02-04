<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/PddeRepository.php';

$pdo = Database::getConnection();

$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$escolaId = (string)($_SESSION['escola_id'] ?? '');
$pddeId   = (string)($_GET['id'] ?? '');

$repo = new PddeRepository($pdo);
$pdde = ($escolaId && $pddeId) ? $repo->buscarPorIdDaEscola($pddeId, $escolaId) : null;

if (!$pdde) {
    echo '<div class="alert alert--error">PDDE não encontrado.</div>';
    echo '<a class="btn-secondary" href="/index.php?page=pdde">Voltar</a>';
    return;
}

$saldoAtual = (float)($pdde['saldo_disponivel'] ?? 0);
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Editar PDDE</h1>
    <div class="page-subtitle">Atualize nome e saldo atual</div>
  </div>

  <a href="/index.php?page=pdde" class="btn-secondary">Voltar</a>
</div>

<?php if ($success): ?>
  <div class="alert alert--success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="/index.php?action=pdde_update" class="form">
  <input type="hidden" name="id" value="<?= htmlspecialchars((string)$pdde['id']) ?>">

  <div class="form-row">
    <div class="field" style="flex: 2;">
      <label for="nome">Nome</label>
      <input type="text" id="nome" name="nome" required
             value="<?= htmlspecialchars((string)($pdde['nome'] ?? '')) ?>">
    </div>

    <div class="field" style="max-width: 260px;">
      <label for="saldo_atual">Saldo atual (disponível)</label>
      <input type="number" id="saldo_atual" name="saldo_atual" required
             step="0.01" min="0"
             value="<?= htmlspecialchars((string)$saldoAtual) ?>">
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn-primary">Salvar alterações</button>
  </div>
</form>
