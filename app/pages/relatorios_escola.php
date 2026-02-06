<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/RelatoriosRepository.php';
require_once __DIR__ . '/../src/Services/RelatoriosService.php';

$pdo = Database::getConnection();

$relRepo = new RelatoriosRepository($pdo);
$relService = new RelatoriosService($relRepo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mes'])) {
    $mes = trim($_POST['mes']);
    $relService->relatorioMensal($mes);
    exit;
}

// Só define header HTML quando for renderizar HTML (GET)
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório</title>
</head>
<body>

<form method="POST" class="form">
    <div class="form-row" style="align-items:flex-end; gap:14px; flex-wrap:wrap;">
        <div class="field" style="max-width: 260px;">
            <label for="mes">Mês</label>
            <input type="month" id="mes" name="mes" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-primary">Gerar relatório</button>
        </div>
    </div>
</form>

</body>
</html>
