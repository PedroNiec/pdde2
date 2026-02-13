<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/FornecedorRepository.php';
require_once __DIR__ . '/../src/Services/FornecedorService.php';
require_once __DIR__ . '/../src/Validators/FornecedorValidator.php';
require_once __DIR__ . '/../src/Repositories/RequisicaoRepository.php';
require_once __DIR__ . '/../src/Repositories/OfertaRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido');
}

$dados = $_POST;

$validator = new \Validators\FornecedorValidator();
$validator->dataValidator($dados);

$nome        = trim((string)($dados['nome'] ?? ''));
$cnpjCpf     = trim((string)($dados['cnpj_cpf'] ?? ''));
$endereco    = trim((string)($dados['endereco'] ?? ''));
$telefone    = trim((string)($dados['telefone'] ?? ''));
$responsavel = trim((string)($dados['responsavel'] ?? ''));
$emailRaw    = trim((string)($dados['email'] ?? ''));
$email       = mb_strtolower($emailRaw);
$senha       = (string)($dados['senha'] ?? '');
$categorias  = $dados['categorias'] ?? [];


$uuidRegex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

$categorias = array_values(array_unique(array_filter(array_map(
    fn($v) => trim((string)$v),
    $categorias
), fn($v) => $v !== '' && preg_match($uuidRegex, $v))));

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

$data = [
    'nome' => $nome,
    'cnpjCpf' => $cnpjCpf,
    'endereco' => $endereco,
    'telefone' => $telefone,
    'responsavel' => $responsavel,
    'email' => $email,
    'senha' => $senhaHash,
    'categorias' => $categorias
];

$pdo = Database::getConnection();


$ofertaRespository = new OfertaRepository($pdo);
$reqRespository = new RequisicaoRepository($pdo);
$fornecedorRespository = new FornecedorRepository($pdo);

$fornecedorService = new FornecedorService($fornecedorRespository, $reqRespository, $ofertaRespository);

try {
    $fornecedorService->criarFornecedor($data);

    header('Location: /index.php?page=login');
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();

    // Se bater no UNIQUE do email (lower(email))
    if (($e->getCode() ?? '') === '23505') {
        http_response_code(409);
        exit('Já existe fornecedor com esse e-mail.');
    }

    http_response_code(500);
    exit('Erro ao cadastrar fornecedor.');
}
