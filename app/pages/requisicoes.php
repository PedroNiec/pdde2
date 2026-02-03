<?php
declare(strict_types=1);

require_once   __DIR__ . '/../src/Repositories/RequisicaoRepository.php';
require_once   __DIR__ . '/../src/Services/RequisicaoService.php';
require_once   __DIR__ . '/../src/Repositories/PddeRepository.php';
require_once   __DIR__ . '/../src/Repositories/CategoriaRepository.php';
require_once  __DIR__ . '/../src/Repositories/OfertaRepository.php';

$pdo = Database::getConnection();

$repo = new RequisicaoRepository($pdo);
$pdde = new PddeRepository($pdo);
$caterogia = new CategoriaRepository($pdo);
$oferta = new OfertaRepository($pdo);

$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$service = new RequisicaoService($repo, $pdde, $caterogia, $oferta);


$escolaId = (string)($_SESSION['escola_id'] ?? '');
$requisicoes = $escolaId ? $service->listarPorEscola($escolaId) : [];
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Requisições</h1>
    <div class="page-subtitle">
      <?= count($requisicoes) ?> registr<?= count($requisicoes) === 1 ? 'o' : 'os' ?>
    </div>
  </div>

  <a href="/index.php?page=requisicao_nova" class="btn-primary">
    Nova requisição
  </a>
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
        <th class="col-num">Quantidade</th>
        <th>PDDE</th>
        <th>Status</th>
        <th>Criada em</th>
        <th>Ações</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($requisicoes)): ?>
        <tr>
            <td colspan="6" style="text-align:center; color:#666;">
                Nenhuma requisição cadastrada
            </td>
        </tr>
    <?php else: ?>
        <?php foreach ($requisicoes as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['produto'] ?? '') ?></td>
                <td><?= (int)($r['quantidade'] ?? 0) ?></td>
                <td><?= htmlspecialchars($r['pdde_nome'] ?? '') ?></td>
                <td>
                    <?php
                        $status = (string)($r['status'] ?? '');
                        $map = [
                        'aberta' => ['label' => 'Aberta', 'class' => 'badge badge--open'],
                        'em_compra' => ['label' => 'Em compra', 'class' => 'badge badge--buy'],
                        'concluida' => ['label' => 'Concluída', 'class' => 'badge badge--done'],
                        ];
                        $info = $map[$status] ?? ['label' => $status ?: '-', 'class' => 'badge'];
                    ?>
                    <span class="<?= $info['class'] ?>"><?= htmlspecialchars($info['label']) ?></span>
                    </td>
                <td class="col-date">
                    <?php
                        $dt = $r['created_at'] ?? null;
                        echo $dt ? date('d/m/Y H:i', strtotime((string)$dt)) : '-';
                    ?>
                </td>
               <td class="col-actions">
  <a class="link" href="/index.php?page=requisicao_detalhe&id=<?= htmlspecialchars($r['id']) ?>">Ver</a>
</td>

            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
