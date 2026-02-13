<?php
declare(strict_types=1);

require __DIR__ . '/../../app/core/bootstrap.php';
require_once __DIR__ . '/../src/Repositories/PddeRepository.php';
require_once __DIR__ . '/../src/Repositories/RequisicaoRepository.php';
require_once __DIR__ . '/../src/Repositories/CategoriaRepository.php';
require_once __DIR__ . '/../src/Repositories/OfertaRepository.php';
require_once __DIR__ . '/../src/Services/RequisicaoService.php';

$pdo = Database::getConnection();
$pddeRepo = new PddeRepository($pdo);
$reqRepo = new RequisicaoRepository($pdo);
$catRepo = new CategoriaRepository($pdo);
$ofertaRepo = new OfertaRepository($pdo);
$movRepo = new MovimentacoesRepository($pdo);
$movService = new MovimentacoesService($movRepo);

$reqService = new RequisicaoService($reqRepo, $pddeRepo, $catRepo, $ofertaRepo);

if (empty($_SESSION['user_id'])) {
    header('Location: /index.php?page=login');
    exit;
}

// gate (home da escola)
$userRole = (string)($_SESSION['role'] ?? '');
if ($userRole !== 'escola') {
    header('Location: /index.php?page=fornecedor_requisicoes');
    exit;
}

$userName = (string)($_SESSION['name'] ?? 'Usuário');

$escolaId = (string)($_SESSION['escola_id'] ?? '');

$pddes = $pddeRepo->listarPorEscola($escolaId);
$requisicoes = $reqService->listarPorEscola($escolaId);
$movimentacoes = $movService->totalPorEscola($escolaId);

// ===== Card 1: Totais PDDE =====
$totInicial = 0.0;
$totDisponivel = 0.0;
$totBloqueado = 0.0;
$totGasto = 0.0;

foreach ($pddes as $p) {
    $totInicial += (float)($p['saldo_inicial'] ?? 0);
    $totDisponivel += (float)($p['saldo_disponivel'] ?? 0);

    // se vier negativo, evita visual estranho no resumo
    $totBloqueado += abs((float)($p['saldo_bloqueado'] ?? 0));

    $totGasto += (float)($p['saldo_gasto'] ?? 0);
}

function brl(float $v): string {
    return 'R$ ' . number_format($v, 2, ',', '.');
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Escola</title>

    <style>
        :root{
            --surface:#fff;
            --text:#0f172a;
            --muted:#64748b;
            --border:#e5e7eb;
            --shadow: 0 10px 30px rgba(2, 6, 23, .06);
            --radius: 14px;

            --primary:#111827;
            --primaryHover:#0b1220;

            --successBg:#ecfdf5;
            --successBd:#bbf7d0;
            --successTx:#047857;

            --infoBg:#eff6ff;
            --infoBd:#bfdbfe;
            --infoTx:#1d4ed8;

            --warnBg:#fff7ed;
            --warnBd:#fed7aa;
            --warnTx:#9a3412;

            --neutralBg:#f8fafc;
            --neutralBd:#e2e8f0;
            --neutralTx:#334155;
        }

        *{ box-sizing: border-box; }
        body{
            margin:0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--text);
            background: #f6f7fb;
        }

        .page{
            max-width: 1100px;
            margin: 0 auto;
            padding: 18px;
        }

        .topbar{
            display:flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin: 6px 0 16px;
        }

        .title{
            margin:0;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: .2px;
        }

        .subtitle{
            margin-top: 8px;
            color: var(--muted);
            font-size: 13px;
            display:flex;
            gap:10px;
            align-items:center;
            flex-wrap: wrap;
        }

        .chip{
            display:inline-flex;
            align-items:center;
            padding: 5px 10px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #fff;
            font-weight: 900;
            font-size: 12px;
            color: #334155;
        }

        .btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(17,24,39,.12);
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            font-weight: 900;
            font-size: 13px;
            transition: transform .12s ease, background .12s ease;
            white-space: nowrap;
        }
        .btn:hover{ background: var(--primaryHover); transform: translateY(-1px); }

        .card{
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .card__head{
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            display:flex;
            align-items:flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .card__title{
            margin:0;
            font-size: 14px;
            font-weight: 900;
        }

        .card__desc{
            margin-top: 6px;
            font-size: 13px;
            color: var(--muted);
        }

        .kpis{
            display:grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            padding: 14px 16px 16px;
        }

        .kpi{
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px 12px 10px;
            background: #fff;
        }

        .kpi__label{
            font-size: 12px;
            font-weight: 900;
            color: var(--muted);
            display:flex;
            align-items:center;
            gap: 8px;
        }

        .kpi__value{
            margin-top: 8px;
            font-size: 18px;
            font-weight: 1000;
            letter-spacing: .2px;
        }

        .kpi--success{ background: var(--successBg); border-color: var(--successBd); }
        .kpi--success .kpi__value{ color: var(--successTx); }

        .kpi--info{ background: var(--infoBg); border-color: var(--infoBd); }
        .kpi--info .kpi__value{ color: var(--infoTx); }

        .kpi--warn{ background: var(--warnBg); border-color: var(--warnBd); }
        .kpi--warn .kpi__value{ color: var(--warnTx); }

        .kpi--neutral{ background: var(--neutralBg); border-color: var(--neutralBd); }
        .kpi--neutral .kpi__value{ color: var(--neutralTx); }

        @media (max-width: 900px){
            .kpis{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>
</head>

<body>
<div class="page">

    <div class="topbar">
        <div>
            <h1 class="title">Home</h1>
            <div class="subtitle">
                <span class="chip">Olá, <?= htmlspecialchars($userName, ENT_QUOTES | ENT_HTML5) ?></span>
                <span>•</span>
                <span><?= date('d/m/Y H:i') ?></span>
            </div>
        </div>

        <a class="btn" href="/index.php?page=logout">Sair</a>
    </div>

    <!-- CARD 1: PDDE (Visão geral) -->
    <div class="card">
        <div class="card__head">
            <div>
                <h2 class="card__title">PDDE — Visão geral</h2>
                <div class="card__desc">
                    <?= count($pddes) ?> PDDE<?= count($pddes) === 1 ? '' : 's' ?> cadastrados
                </div>
            </div>

            <a class="btn" href="/index.php?page=pdde">Ver PDDES</a>
        </div>

        <div class="kpis">
            <div class="kpi kpi--success">
                <div class="kpi__label">Disponível</div>
                <div class="kpi__value"><?= brl($totDisponivel) ?></div>
            </div>

            <div class="kpi kpi--info">
                <div class="kpi__label">Bloqueado</div>
                <div class="kpi__value"><?= brl($totBloqueado) ?></div>
            </div>

            <div class="kpi kpi--warn">
                <div class="kpi__label">Gasto</div>
                <div class="kpi__value"><?= brl($totGasto) ?></div>
            </div>

            <div class="kpi kpi--neutral">
                <div class="kpi__label">Saldo inicial</div>
                <div class="kpi__value"><?= brl($totInicial) ?></div>
            </div>
        </div>
    </div>

</div>
</body>
</html>
