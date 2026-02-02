<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/core/bootstrap.php';
require_once __DIR__ . '/../../app/src/Repositories/PddeRepository.php';

$escolaId = (string)($_SESSION['escola_id'] ?? '');
$pddeId   = (string)($_POST['id'] ?? '');
$nome     = trim((string)($_POST['nome'] ?? ''));
$saldoAtual = (float)($_POST['saldo_atual'] ?? 0);

try {
    if ($escolaId === '' || $pddeId === '') {
        throw new InvalidArgumentException('Dados inválidos.');
    }
    if ($nome === '') {
        throw new InvalidArgumentException('Informe o nome do PDDE.');
    }
    if ($saldoAtual < 0) {
        throw new InvalidArgumentException('Saldo atual não pode ser negativo.');
    }

    $pdo = Database::getConnection();
    $repo = new PddeRepository($pdo);

    // garante que pertence à escola (e você pode usar pra validar antes se quiser)
    $pdde = $repo->buscarPorIdDaEscola($pddeId, $escolaId);
    if (!$pdde) {
        throw new RuntimeException('PDDE não encontrado.');
    }

    $repo->atualizarNomeESaldoAtual($pddeId, $escolaId, $nome, $saldoAtual);

    $_SESSION['flash_success'] = 'PDDE atualizado com sucesso.';
    header('Location: /index.php?page=pdde');
    exit;

} catch (Throwable $e) {
    $_SESSION['flash_error'] = $e->getMessage();
    header('Location: /index.php?page=pdde_editar&id=' . urlencode($pddeId));
    exit;
}
