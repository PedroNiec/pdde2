<?php 

declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/ConfiguracoesRepository.php';


$pdo = Database::getConnection();

$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$configuracoesRepo = new ConfiguracoesRepository($pdo);
$dados = $configuracoesRepo->getUserData((int)$_SESSION['user_id']);

if (!$dados) {
    echo '<div class="alert alert--error">Usuário não encontrado.</div>';
    return;
}
?>

<!DOCTYPE html>
<html>
    <head>
    <title>Configurações</title>
    <link rel="stylesheet" href="/assets/app.css">
    <style>
.card {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    max-width: 900px;
    margin: 0 auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

/* Grid */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-group.full {
    grid-column: span 2;
}

/* Labels */
.form-group label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 6px;
    color: #555;
}

/* Inputs */
.form-group input,
.form-group select {
    width: 100%;
    padding: 12px 14px;
    border-radius: 8px;
    border: 1px solid #dcdcdc;
    font-size: 0.95rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
}

/* Botão */
.form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 30px;
}

.btn-primary {
    background: #4f46e5;
    color: #fff;
    border: none;
    padding: 12px 22px;
    font-size: 0.95rem;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
}

.btn-primary:hover {
    background: #4338ca;
    transform: translateY(-1px);
}

/* Responsivo */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-group.full {
        grid-column: span 1;
    }
}

    </style>
</head>
<body>
<div class="ui">
    <header class="page-header">
        <h1 class="page-title">Configurações</h1>
    </header>

      <?php if ($success): ?>
            <div class="alert alert--success"><?= htmlspecialchars((string)$success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert--error"><?= htmlspecialchars((string)$error) ?></div>
        <?php endif; ?>

    <form method="POST" action="/index.php?action=update_configuracoes" class="card form-config">

    <input type="hidden" name="id" value="<?= htmlspecialchars($dados['id']) ?>">

    <div class="form-grid">

    <?php if ($_SESSION['fornecedor_id'] !== null): ?>
             <div class="form-group">
            <label for="nome">Razão social</label>
            <input type="text" id="nome" name="nome"
                   value="<?= htmlspecialchars($dados['nome']) ?>" required>
        </div>
        <?php endif; ?>

        <?php if ($_SESSION['escola_id'] !== null): ?>
             <div class="form-group">
            <label for="nome">Nome da escola</label>
            <input type="text" id="nome" name="nome"
                   value="<?= htmlspecialchars($dados['nome']) ?>" required>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($dados['email']) ?>" required>
        </div>

        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" name="telefone"
                   value="<?= htmlspecialchars($dados['telefone']) ?>">
        </div>

        <div class="form-group">
            <label for="responsavel">Responsável</label>
            <input type="text" id="responsavel" name="responsavel"
                   value="<?= htmlspecialchars($dados['responsavel']) ?>">
        </div>

        <?php if ($_SESSION['fornecedor_id'] !== null): ?>
             <div class="form-group">
            <label for="cnpj">CNPJ</label>
            <input type="text" id="cnpj" name="cnpj"
                   value="<?= htmlspecialchars($dados['cnpj_cpf']) ?>">
        </div>
        <?php endif; ?>

        <div class="form-group full">
            <label for="endereco">Endereço</label>
            <input type="text" id="endereco" name="endereco"
                   value="<?= htmlspecialchars($dados['endereco']) ?>">
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            💾 Salvar alterações
        </button>
    </div>
</form>

</div>
</body>
</html>
<?php


