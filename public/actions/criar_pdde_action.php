<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/src/Repositories/PddeRepository.php';
require_once __DIR__ . '/../../app/core/bootstrap.php';

$escolaId = (string)($_SESSION['escola_id'] ?? '');

$pdo = Database::getConnection();

$repo = new PddeRepository($pdo);

$data = [
    'escola_id' => $escolaId,
    'nome' => trim((string)($_POST['nome'] ?? '')),
    'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
    'saldo_atual' => $_POST['saldo_inicial'] ?? 0,
    'saldo_atual' => $_POST['saldo_inicial'] ?? 0,
];

$created = $repo->criar($data);

$_SESSION['flash_success'] = 'PDDE criado com sucesso.';
header('Location: /index.php?page=pdde');
exit;
