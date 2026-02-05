<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/core/bootstrap.php';

require_once __DIR__ . '/../../app/src/Repositories/FornecedorRepository.php';
require_once __DIR__ . '/../../app/src/Repositories/RequisicaoRepository.php';
require_once __DIR__ . '/../../app/src/Repositories/OfertaRepository.php';
require_once __DIR__ . '/../../app/src/Services/FornecedorService.php';

$role = (string)($_SESSION['role'] ?? '');
$fornecedorId = (string)($_SESSION['fornecedor_id'] ?? '');

$requisicaoId = (string)($_POST['requisicao_id'] ?? '');
$valorUnitario = (float)($_POST['valor_unitario'] ?? 0);
$marca = (string)($_POST['marca'] ?? '');

try {
    if ($role !== 'fornecedor' || $fornecedorId === '') {
        throw new InvalidArgumentException('Acesso negado.');
    }

    $pdo = Database::getConnection();

    $service = new FornecedorService(
        new FornecedorRepository($pdo),
        new RequisicaoRepository($pdo),
        new OfertaRepository($pdo)
    );

    $service->criarOferta($fornecedorId, $requisicaoId, $valorUnitario, $marca);

    $_SESSION['flash_success'] = 'Oferta enviada com sucesso.';
    header('Location: /index.php?page=fornecedor_requisicoes');
    exit;

} catch (Throwable $e) {
    $_SESSION['flash_error'] = $e->getMessage();
    header('Location: /index.php?page=oferta_nova&requisicao_id=' . urlencode($requisicaoId));
    exit;
}
