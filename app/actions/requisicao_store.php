<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/core/bootstrap.php';

require_once __DIR__ . '/../../app/src/Repositories/RequisicaoRepository.php';
require_once __DIR__ . '/../../app/src/Repositories/PddeRepository.php';
require_once __DIR__ . '/../../app/src/Repositories/CategoriaRepository.php';
require_once __DIR__ . '/../../app/src/Services/RequisicaoService.php';
require_once __DIR__ . '/../../app/src/Repositories/OfertaRepository.php';

$escolaId = (string)($_SESSION['escola_id'] ?? '');

$pddeId = trim((string)($_POST['pdde_id'] ?? ''));
$categoriaId = (string)($_POST['categoria_id'] ?? '');
$produto = (string)($_POST['produto'] ?? '');
$quantidade = (int)($_POST['quantidade'] ?? 0);


try {
    if ($escolaId === '') {
        throw new InvalidArgumentException('Sessão inválida. Faça login novamente.');
    }

    $pdo = Database::getConnection();

    $repo = new RequisicaoRepository($pdo);
    $pddeRepo = new PddeRepository($pdo);
    $catRepo = new CategoriaRepository($pdo);
    $ofertaRepo = new OfertaRepository($pdo);

    $service = new RequisicaoService($repo, $pddeRepo, $catRepo, $ofertaRepo);

    $id = $service->criar($escolaId, $pddeId, $categoriaId, $produto, $quantidade);

    $_SESSION['flash_success'] = 'Requisição criada com sucesso.';
    header('Location: /index.php?page=requisicoes');
    exit;

} catch (Throwable $e) {
    $_SESSION['flash_error'] = $e->getMessage();
    header('Location: /index.php?page=requisicao_nova');
    exit;
}
