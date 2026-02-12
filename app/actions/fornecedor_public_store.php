<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido');
}

$nome        = trim((string)($_POST['nome'] ?? ''));
$cnpjCpf     = trim((string)($_POST['cnpj_cpf'] ?? ''));
$endereco    = trim((string)($_POST['endereco'] ?? ''));
$telefone    = trim((string)($_POST['telefone'] ?? ''));
$responsavel = trim((string)($_POST['responsavel'] ?? ''));
$emailRaw    = trim((string)($_POST['email'] ?? ''));
$email       = mb_strtolower($emailRaw);
$senha       = (string)($_POST['senha'] ?? '');
$categorias  = $_POST['categorias[]'] ?? [];



if ($nome === '' || $email === '' || $senha === '') {
    http_response_code(422);
    exit('Preencha nome, e-mail e senha.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    exit('E-mail inválido.');
}
if (strlen($senha) < 6) {
    http_response_code(422);
    exit('Senha deve ter pelo menos 6 caracteres.');
}
if (!is_array($categorias) || count($categorias) === 0) {
    http_response_code(422);
    exit('Selecione ao menos uma categoria.');
}

$categorias = array_values(array_unique(array_filter(array_map(
    fn($v) => filter_var($v, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]),
    $categorias
))));

if (count($categorias) === 0) {
    http_response_code(422);
    exit('Categorias inválidas.');
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

$pdo = Database::getConnection();

try {
    $pdo->beginTransaction();

    $st = $pdo->prepare("
    INSERT INTO fornecedores (nome, cnpj_cpf, endereco, telefone, responsavel)
    VALUES (:nome, :cnpj_cpf, :endereco, :telefone, :responsavel)
    RETURNING id
  ");

    $st->execute([
        ':nome' => $nome,
        ':cnpj_cpf' => ($cnpjCpf !== '' ? $cnpjCpf : null),
        ':endereco' => ($endereco !== '' ? $endereco : null),
        ':telefone' => ($telefone !== '' ? $telefone : null),
        ':responsavel' => ($responsavel !== '' ? $responsavel : null),
    ]);

    $fornecedorId = (int)$st->fetchColumn();

    $stLink = $pdo->prepare("
    INSERT INTO fornecedor_categorias (fornecedor_id, categoria_id)
    VALUES (:fornecedor_id, :categoria_id)
  ");
    foreach ($categorias as $catId) {
        $stLink->execute([
            ':fornecedor_id' => $fornecedorId,
            ':categoria_id' => (int)$catId,
        ]);
    }

    $stUser = $pdo->prepare("
    INSERT INTO users (name, email, password_hash, role, active)
    VALUES (:nome, :email, :password_hash, :fornecedor_id, 'fornecedor', TRUE)
  ");

    $stUser->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':password_hash' => $senhaHash,
        ':fornecedor_id' => $fornecedorId,
    ]);


    $pdo->commit();

    // pode mandar pra login ou mostrar msg de sucesso
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
