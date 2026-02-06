<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/RequisicaoRepository.php';

$pdo = Database::getConnection();

$fornecedorId = (string)($_SESSION['fornecedor_id'] ?? '');
$role = (string)($_SESSION['role'] ?? '');

if ($role !== 'fornecedor' || $fornecedorId === '') {
    echo '<div class="alert alert--error">Acesso negado.</div>';
    return;
}

$requisicaoId = (string)($_GET['requisicao_id'] ?? '');

$repo = new RequisicaoRepository($pdo);
$req = $repo->buscarDetalhe($requisicaoId);

$quantidade = $req['quantidade'];

if (!$req || ($req['status'] ?? '') !== 'aberta') {
    echo '<div class="alert alert--error">Requisição inválida ou não está aberta.</div>';
    echo '<a class="btn-secondary" href="/index.php?page=fornecedor_requisicoes">Voltar</a>';
    return;
}

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Nova oferta</h1>
    <div class="page-subtitle"><?= htmlspecialchars($req['produto'] ?? '-') ?> (Qtd: <?= (int)($req['quantidade'] ?? 0) ?>)</div>
  </div>
  <a href="/index.php?page=fornecedor_requisicoes" class="btn-secondary">Voltar</a>
</div>

<?php if ($error): ?>
  <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="/index.php?action=oferta_store" class="form">
  <input type="hidden" name="requisicao_id" value="<?= htmlspecialchars($requisicaoId) ?>">

  <div class="form-row">
    <div class="field" style="max-width: 260px;">
      <label for="valor_unitario">Valor unitário</label>
      <input
        type="number"
        step="0.01"
        min="0.01"
        id="valor_unitario"
        name="valor_unitario"
        required
        placeholder="Ex.: 12.50"
      >
    </div>
      <div class="field" style="max-width: 260px;">
          <label for="marca">Marca</label>
          <input
                  type="text"
                  id="marca"
                  name="marca"
                  required
                  placeholder="Ex.: Tilibra"
          >
      </div>

      <br>
      <div class="field" style="max-width: 260px;">
          <label for="valor_total">Valor total</label>
          <input
                  type="text"
                  id="valor_total"
                  name="valor_total"
                  readonly
                  placeholder="0,00"
          >
      </div>

  </div>

  <div class="form-actions">
    <button type="submit" class="btn-primary">Enviar oferta</button>
  </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const quantidade = <?= (int)$quantidade ?>;
        const campoUnitario = document.getElementById('valor_unitario');
        const campoTotal = document.getElementById('valor_total');

        function atualizarTotal() {
            let valorUnitario = parseFloat(campoUnitario.value);

            if (isNaN(valorUnitario)) {
                campoTotal.value = '';
                return;
            }

            let total = valorUnitario * quantidade;

            campoTotal.value = total.toFixed(2);
        }

        campoUnitario.addEventListener('input', atualizarTotal);
    });
</script>

