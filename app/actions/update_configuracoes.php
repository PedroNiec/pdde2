<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/ConfiguracoesRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php?page=configuracoes');
    exit;
}

$data = [
    'id' => $_POST['id'] ?? null,
    'nome' => trim($_POST['nome'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'endereco' => trim($_POST['endereco'] ?? ''),
    'telefone' => trim($_POST['telefone'] ?? ''),
    'responsavel' => trim($_POST['responsavel'] ?? ''),
    'active' => (int) ($_POST['active'] ?? 0),
];

$pdo = Database::getConnection();
$configRepo = new ConfiguracoesRepository($pdo);

$configRepo->updateEscolaData($data);

$_SESSION['flash_success'] = 'Configurações atualizadas com sucesso.';
header('Location: /index.php?page=configuracoes&success=1');
exit;
