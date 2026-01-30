<?php
declare(strict_types=1);

require_once   __DIR__ . '/../src/Repositories/RequisicaoRepository.php';
require_once   __DIR__ . '/../src/Services/RequisicaoService.php';

$pdo = Database::getConnection();

$repo = new RequisicaoRepository($pdo);
$service = new RequisicaoService($repo);

$escolaId = (string)($_SESSION['escola_id'] ?? '');
$requisicoes = $escolaId ? $service->listarPorEscola($escolaId) : [];
?>

<h1>Requisições</h1>

<div style="margin-bottom: 16px;">
    <a href="/index.php?page=requisicao_nova" class="btn-primary">
        Nova requisição
    </a>
</div>

<table class="table">
    <thead>
    <tr>
        <th>Produto</th>
        <th>Quantidade</th>
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
                <td><?= htmlspecialchars($r['status'] ?? '') ?></td>
                <td>
                    <?php
                    $dt = $r['created_at'] ?? null;
                    echo $dt ? date('d/m/Y H:i', strtotime((string)$dt)) : '';
                    ?>
                </td>
                <td>
                    <!-- ações futuras: Ver / Editar / Selecionar oferta -->
                    -
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
