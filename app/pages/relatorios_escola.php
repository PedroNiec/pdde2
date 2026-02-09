<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/RelatoriosRepository.php';
require_once __DIR__ . '/../src/Services/RelatoriosService.php';
require_once __DIR__ . '/../src/Repositories/PddeRepository.php';

$pdo = Database::getConnection();

$relRepo = new RelatoriosRepository($pdo);
$relService = new RelatoriosService($relRepo);
$pddeRepo = new PddeRepository($pdo);


$escolaId = (string)($_SESSION['escola_id'] ?? '');

$pddes = $escolaId ? $pddeRepo->listarPorEscola($escolaId) : [];

$escola_id = $_SESSION['escola_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mes'])) {
    $mes = trim($_POST['mes']);
    $relService->relatorioMensalPorEscola($mes, $escola_id);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pdde_id'])) {
    $pddeId = trim($_POST['pdde_id']);
    $relService->relatorioPorPdde($pddeId);
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 30px;
            background: #f5f6f8;
        }

        .form-container {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 20px;
            max-width: 520px;
        }

        .form-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }

        .form-row {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .field {
            display: flex;
            flex-direction: column;
        }

        .field label {
            font-size: 13px;
            color: #555;
            margin-bottom: 4px;
        }

        .field input[type="month"] {
            padding: 7px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            min-width: 200px;
        }

        .field input[type="month"]:focus {
            outline: none;
            border-color: #4a90e2;
        }

        .form-actions {
            display: flex;
            align-items: flex-end;
        }

        .btn-primary {
            padding: 8px 14px;
            border: 1px solid #2f6fd6;
            background: #3b82f6;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #2f6fd6;
        }

        @media (max-width: 480px) {
            .form-row {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-primary {
                width: 100%;
            }
        }

        .field select {
            padding: 7px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            min-width: 200px;
            background: #fff;
        }

        .field select:focus {
            outline: none;
            border-color: #4a90e2;
        }

    </style>
</head>

<body>

<div class="form-container">
    <div class="form-title">Relatório mensal</div>

    <form method="POST" class="form">
        <div class="form-row">
            <div class="field">
                <label for="mes">Mês/Ano</label>
                <input type="month" id="mes" name="mes" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Gerar relatório
                </button>
            </div>
        </div>
    </form>
</div>

<br><br>

<div class="form-container">
    <div class="form-title">Relatório por PDDE</div>

<form method="POST" class="form">
    <div class="form-row">
        <div class="field">
            <label for="pdde_id">PDDE</label>
            <select id="pdde_id" name="pdde_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($pddes as $p): ?>
                    <option value="<?= htmlspecialchars($p['id']) ?>">
                        <?= htmlspecialchars($p['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                Gerar relatório por PDDE
            </button>
        </div>
    </div>
</form>

</div>

</body>
</html>

