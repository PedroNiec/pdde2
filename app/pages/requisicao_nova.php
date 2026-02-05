<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/PddeRepository.php';
require_once __DIR__ . '/../src/Repositories/CategoriaRepository.php';

$pdo = Database::getConnection();

$escolaId = (string)($_SESSION['escola_id'] ?? '');
$pddeRepo = new PddeRepository($pdo);
$categoriaRepo = new CategoriaRepository($pdo);

$pddes = $escolaId ? $pddeRepo->listarPorEscola($escolaId) : [];
$categorias = $categoriaRepo->listarTodas();

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Nova requisição</h1>
    <div class="page-subtitle">Crie uma requisição para a escola</div>
  </div>

  <a href="/index.php?page=requisicoes" class="btn-secondary">Voltar</a>
</div>

<?php if ($error): ?>
  <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="/index.php?action=requisicao_store" class="form">
  <div class="form-row">
    <div class="field">
      <label for="pdde_id">PDDE</label>
      <select id="pdde_id" name="pdde_id" required>
        <option value="">Selecione...</option>
        <?php foreach ($pddes as $p): ?>
          <option value="<?= htmlspecialchars($p['id']) ?>">
            <?= htmlspecialchars($p['nome']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label for="categoria_id">Categoria</label>
      <select id="categoria_id" name="categoria_id">
        <option value="">(Opcional)</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= htmlspecialchars($c['id']) ?>">
            <?= htmlspecialchars($c['nome']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="form-row">
    <div class="field" style="flex: 2;">
      <label for="produto">Produto</label>
      <input type="text" id="produto" name="produto" required placeholder="Ex.: Papel A4, Caneta azul...">
    </div>

    <div class="field" style="flex: 1;">
      <label for="quantidade">Quantidade</label>
      <input type="number" id="quantidade" name="quantidade" min="1" required>
    </div>
  </div>

    <div class="field">
        <label for="obs">Observações</label>
        <input type="text" id="obs" name="obs" min="1" required>
    </div>

  <div class="form-actions">
    <button type="submit" class="btn-primary">Criar requisição</button>
  </div>
</form>
