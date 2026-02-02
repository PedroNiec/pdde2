<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/core/bootstrap.php';

require_once __DIR__ . '/../../app/src/Repositories/RequisicaoRepository.php';
require_once __DIR__ . '/../../app/src/Repositories/PddeRepository.php';
require_once __DIR__ . '/../../app/src/Repositories/OfertaRepository.php';
require_once __DIR__ . '/../../app/src/Repositories/CategoriaRepository.php';
require_once __DIR__ . '/../../app/src/Services/RequisicaoService.php';

$escolaId = (string)($_SESSION['escola_id'] ?? '');
$requisicaoId = (string)($_POST['requisicao_id'] ?? '');

try {
    if ($escolaId === '' || $requisicaoId === '') {
        throw new InvalidArgumentException('Dados inválidos.');
    }

    $pdo = Database::getConnection();

    $reqRepo = new RequisicaoRepository($pdo);
    $pddeRepo = new PddeRepository($pdo);
    $catRepo = new CategoriaRepository($pdo);
    $ofertaRepo = new OfertaRepository($pdo);

    $service = new RequisicaoService($reqRepo, $pddeRepo, $catRepo, $ofertaRepo);

    $service->concluirCompraParaEscola($requisicaoId, $escolaId);

    $_SESSION['flash_success'] = 'Compra concluída com sucesso.';
    header('Location: /index.php?page=requisicao_detalhe&id=' . urlencode($requisicaoId));
    exit;

} catch (Throwable $e) {
    $_SESSION['flash_error'] = $e->getMessage();
    header('Location: /index.php?page=requisicao_detalhe&id=' . urlencode($requisicaoId));
    exit;
}
