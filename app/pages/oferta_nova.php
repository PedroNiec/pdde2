<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/RequisicaoRepository.php';

$pdo = Database::getConnection();

$fornecedorId = (string)($_SESSION['fornecedor_id'] ?? '');
$role = (string)($_SESSION['role'] ?? '');

if ($role !== 'fornecedor' || $fornecedorId === '') {
    echo '<div class="alert alert--error">Acesso negado.</div>';
    return;
}

$requisicaoId = (string)($_GET['requisicao_id'] ?? '');

$repo = new RequisicaoRepository($pdo);
$req = $repo->buscarDetalhe($requisicaoId);

$quantidade = (int)($req['quantidade'] ?? 0);

if (!$req || ($req['status'] ?? '') !== 'aberta') {
    echo '<div class="alert alert--error">Requisição inválida ou não está aberta.</div>';
    echo '<a class="btn-secondary" href="/index.php?page=fornecedor_requisicoes">Voltar</a>';
    return;
}

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

function badgeClass(string $status): string {
    return match ($status) {
        'aberta' => 'req-badge req-badge--open',
        'em_compra' => 'req-badge req-badge--progress',
        'concluida' => 'req-badge req-badge--done',
        'cancelada' => 'req-badge req-badge--cancel',
        default => 'req-badge'
    };
}

$status = (string)($req['status'] ?? '-');
$createdAt = $req['created_at'] ?? null;
$createdFmt = $createdAt ? date('d/m/Y H:i', strtotime((string)$createdAt)) : '-';

$produto = (string)($req['produto'] ?? '-');
$categoria = (string)($req['categoria_nome'] ?? '-');
$pdde = (string)($req['pdde_nome'] ?? '-');
$observacoes = trim((string)($req['observacoes'] ?? ''));
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Nova oferta</h1>

        <div class="page-subtitle" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-top:6px;">
            <span class="req-chip"><strong>Produto:</strong> <?= htmlspecialchars($produto) ?></span>
            <span class="req-chip"><strong>Qtd:</strong> <?= (int)$quantidade ?></span>
            <span class="<?= htmlspecialchars(badgeClass($status)) ?>"><?= htmlspecialchars($status) ?></span>
        </div>
    </div>
    <a href="/index.php?page=fornecedor_requisicoes" class="btn-secondary">Voltar</a>
</div>

<?php if ($error): ?>
    <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Card detalhado da requisição -->
<div class="card req-card" style="margin: 12px 0 18px;">
    <div class="req-card__title">
        <div style="font-weight: 700; font-size: 15px;">Detalhes da requisição</div>
        <div class="muted" style="font-size: 12px;">ID: <?= htmlspecialchars((string)($req['id'] ?? $requisicaoId)) ?> • Criada em: <?= htmlspecialchars($createdFmt) ?></div>
    </div>

    <div class="req-grid">
        <div class="req-kv">
            <div class="req-kv__label">Produto</div>
            <div class="req-kv__value"><?= htmlspecialchars($produto) ?></div>
        </div>

        <div class="req-kv">
            <div class="req-kv__label">Quantidade</div>
            <div class="req-kv__value"><?= (int)$quantidade ?></div>
        </div>

        <div class="req-kv">
            <div class="req-kv__label">Categoria</div>
            <div class="req-kv__value"><?= htmlspecialchars($categoria) ?></div>
        </div>

        <div class="req-kv">
            <div class="req-kv__label">PDDE</div>
            <div class="req-kv__value"><?= htmlspecialchars($pdde) ?></div>
        </div>

        <div class="req-kv">
            <div class="req-kv__label">Status</div>
            <div class="req-kv__value"><span class="<?= htmlspecialchars(badgeClass($status)) ?>"><?= htmlspecialchars($status) ?></span></div>
        </div>

        <div class="req-kv">
            <div class="req-kv__label">Criada em</div>
            <div class="req-kv__value"><?= htmlspecialchars($createdFmt) ?></div>
        </div>
    </div>

    <?php if ($observacoes !== ''): ?>
        <div class="req-obs">
            <div class="req-kv__label" style="margin-bottom:6px;">Observações</div>
            <div class="req-obs__box"><?= nl2br(htmlspecialchars($observacoes)) ?></div>
        </div>
    <?php endif; ?>
</div>

<form method="POST" action="/index.php?action=oferta_store" class="form">
    <input type="hidden" name="requisicao_id" value="<?= htmlspecialchars($requisicaoId) ?>">

    <div class="form-row" style="align-items:flex-end; gap:14px; flex-wrap:wrap;">
        <div class="field" style="max-width: 260px;">
            <label for="valor_unitario">Valor unitário</label>
            <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    id="valor_unitario"
                    name="valor_unitario"
                    required
                    placeholder="Ex.: 12.50"
            >
        </div>

        <div class="field" style="max-width: 260px;">
            <label for="marca">Marca</label>
            <input
                    type="text"
                    id="marca"
                    name="marca"
                    required
                    placeholder="Ex.: Tilibra"
            >
        </div>

        <div class="field" style="max-width: 260px;">
            <label for="valor_total">Valor total</label>
            <input
                    type="text"
                    id="valor_total"
                    name="valor_total"
                    readonly
                    placeholder="0,00"
            >
            <div class="muted" style="font-size:12px; margin-top:6px;">
                Cálculo automático: valor unitário × <?= (int)$quantidade ?>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">Enviar oferta</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const quantidade = <?= (int)$quantidade ?>;
        const campoUnitario = document.getElementById('valor_unitario');
        const campoTotal = document.getElementById('valor_total');

        function atualizarTotal() {
            let valorUnitario = parseFloat(campoUnitario.value);

            if (isNaN(valorUnitario)) {
                campoTotal.value = '';
                return;
            }

            let total = valorUnitario * quantidade;
            campoTotal.value = total.toFixed(2);
        }

        campoUnitario.addEventListener('input', atualizarTotal);
    });
</script>

<style>
    /* Cabeçalho “chips” */
    .req-chip{
        display:inline-flex;
        gap:6px;
        align-items:center;
        padding:6px 10px;
        border-radius:999px;
        background:#f6f7fb;
        border:1px solid #e6e8f0;
        font-size:12px;
    }

    /* Card detalhado */
    .req-card__title{
        display:flex;
        justify-content:space-between;
        gap:10px;
        flex-wrap:wrap;
        margin-bottom:12px;
    }

    .req-grid{
        display:grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap:12px;
    }

    @media (max-width: 900px){
        .req-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 560px){
        .req-grid{ grid-template-columns: 1fr; }
    }

    .req-kv{
        padding:10px 12px;
        border:1px solid #eceef6;
        background:#fff;
        border-radius:10px;
    }

    .req-kv__label{
        font-size:12px;
        color:#667085;
        margin-bottom:6px;
    }

    .req-kv__value{
        font-size:14px;
        font-weight:650;
        color:#111827;
    }

    /* Observações */
    .req-obs{
        margin-top:12px;
    }
    .req-obs__box{
        border:1px dashed #e2e8f0;
        background:#fbfcff;
        border-radius:10px;
        padding:10px 12px;
        color:#1f2937;
        font-size:13px;
        line-height:1.35;
    }

    /* Badges de status (leve) */
    .req-badge{
        display:inline-flex;
        align-items:center;
        padding:6px 10px;
        border-radius:999px;
        font-size:12px;
        border:1px solid #e6e8f0;
        background:#f6f7fb;
        color:#111827;
        text-transform: capitalize;
    }
    .req-badge--open{
        background:#ecfdf3;
        border-color:#abefc6;
        color:#027a48;
    }
    .req-badge--progress{
        background:#eff8ff;
        border-color:#b2ddff;
        color:#175cd3;
    }
    .req-badge--done{
        background:#f0fdf4;
        border-color:#bbf7d0;
        color:#166534;
    }
    .req-badge--cancel{
        background:#fff1f2;
        border-color:#fecdd3;
        color:#9f1239;
    }
</style>
