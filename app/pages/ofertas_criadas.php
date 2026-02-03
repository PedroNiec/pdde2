<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/OfertaRepository.php';
require_once __DIR__ . '/../src/Repositories/FornecedorRepository.php';
require_once __DIR__ . '/../src/Repositories/RequisicaoRepository.php';
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

$ofertas = $service->ofertasPorFornecedor($fornecedorId);

?>

<div class="page-header">
  <div>
    <h1 class="page-title">Ofertas</h1>
        <div class="page-subtitle"><?= count($ofertas) ?> registr<?= count($ofertas) === 1 ? 'o' : 'os' ?></div>

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
      <th>Categoria</th>
      <th class="col-num">Qtd</th>
      <th class="col-num">Valor unitário</th>
      <th class="col-num">Valor total</th>
      <th>Criado em</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($ofertas)): ?>
      <tr>
        <td colspan="5" style="text-align:center; color:#666;">Nenhuma oferta criada por você no momento</td>
      </tr>
    <?php else: ?>
      <?php foreach ($ofertas as $r): 
        
        $valorTotal = $r['quantidade'] * $r['valor_unitario'];

        ?>
        <tr>
          <td><?= htmlspecialchars($r['produto'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['categoria'] ?? '-') ?></td>
          <td class="col-num"><?= (int)($r['quantidade'] ?? 0) ?></td>
          <td class="col-num">
            <span class="price">
                <?php
                $v = $r['valor_unitario'] ?? null;
                echo $v !== null ? 'R$ ' . number_format((float)$v, 2, ',', '.') : '-';
                ?>
            </span>
            </td>
            <td class="col-num">
            <span class="price">
                <?php
                $v = $valorTotal ?? null;
                echo $v !== null ? 'R$ ' . number_format((float)$v, 2, ',', '.') : '-';
                ?>
            </span>
            </td>


          <td class="col-date">
            <?php
              $dt = $r['created_at'] ?? null;
              echo $dt ? date('d/m/Y H:i', strtotime((string)$dt)) : '-';
            ?>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
