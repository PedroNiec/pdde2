<?php 

declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/ConfiguracoesRepository.php';


$pdo = Database::getConnection();


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
</head>
<body>
<div class="ui">
    <header class="page-header">
        <h1 class="page-title
">Configurações</h1>
    </header>

    <form method="POST" action="/index.php?page=configuracoes">
        <div class="form-group">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($dados['nome']) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($dados['email']) ?>" required>
        </div>

        <button type="submit" class="btn">Salvar</button>
    </form>

</div>
</body>
</html>
<?php


