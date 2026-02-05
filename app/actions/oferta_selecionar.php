<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/core/bootstrap.php';

require_once __DIR__ . '/../../app/src/Repositories/RequisicaoRepository.php';
require_once __DIR__ . '/../../app/src/Repositories/PddeRepository.php';
require_once __DIR__ . '/../../app/src/Repositories/CategoriaRepository.php';
require_once __DIR__ . '/../../app/src/Repositories/OfertaRepository.php';
require_once __DIR__ . '/../../app/src/Services/RequisicaoService.php';

$escolaId = (string)($_SESSION['escola_id'] ?? '');
$requisicaoId = (string)($_POST['requisicao_id'] ?? '');
$ofertaId = (string)($_POST['oferta_id'] ?? '');

try {
    if ($escolaId === '' || $requisicaoId === '' || $ofertaId === '') {
        throw new InvalidArgumentException('Dados inválidos.');
    }

    $pdo = Database::getConnection();

    $repo = new RequisicaoRepository($pdo);
    $pddeRepo = new PddeRepository($pdo);
    $catRepo = new CategoriaRepository($pdo);
    $ofertaRepo = new OfertaRepository($pdo);

    $service = new RequisicaoService($repo, $pddeRepo, $catRepo, $ofertaRepo);

    $service->selecionarOfertaParaEscola($requisicaoId, $ofertaId, $escolaId);

    $_SESSION['flash_success'] = 'Oferta selecionada.';
    header('Location: /index.php?page=requisicao_detalhe&id=' . urlencode($requisicaoId));
    exit;

} catch (Throwable $e) {
    $_SESSION['flash_error'] = $e->getMessage();
    header('Location: /index.php?page=requisicao_detalhe&id=' . urlencode($requisicaoId));
    exit;
}
