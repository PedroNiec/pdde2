<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/OfertaRepository.php';
require_once __DIR__ . '/../src/Repositories/FornecedorRepository.php';
require_once __DIR__ . '/../src/Repositories/RequisicaoRepository.php';
require_once __DIR__ . '/../src/Services/FornecedorService.php';

$pdo = Database::getConnection();

$fornecedorId = (string)($_SESSION['fornecedor_id'] ?? '');
$role = (string)($_SESSION['role'] ?? '');

if ($role !== 'fornecedor' || $fornecedorId === '') {
    echo '<div class="alert alert--error">Acesso negado.</div>';
    return;
}

$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$service = new FornecedorService(
    new FornecedorRepository($pdo),
    new RequisicaoRepository($pdo),
    new OfertaRepository($pdo)
);

$ofertas = $service->ofertasPorFornecedor($fornecedorId);

/**
 * ===== Filtro padronizado (GET) =====
 * q: busca geral (produto/categoria/marca)
 */
$q = trim((string)($_GET['q'] ?? ''));
$qNorm = mb_strtolower($q);

if ($qNorm !== '') {
    $ofertas = array_values(array_filter($ofertas, function($r) use ($qNorm) {
        $produto = mb_strtolower((string)($r['produto'] ?? ''));
        $categoria = mb_strtolower((string)($r['categoria'] ?? ''));
        $marca = mb_strtolower((string)($r['marca'] ?? ''));
        return str_contains($produto, $qNorm)
            || str_contains($categoria, $qNorm)
            || str_contains($marca, $qNorm);
    }));
}
?>

<style>
/* ====== TEMP (somente esta tela) - Light Modern ====== */
.ui{
  --surface: #ffffff;
  --text: #0f172a;
  --muted: #64748b;
  --border: #e5e7eb;
  --shadow: 0 10px 30px rgba(2, 6, 23, .06);
  --radius: 14px;

  --primary: #111827;
  --primaryHover: #0b1220;

  --successBg: #d1fae5;
  --successBd: #a7f3d0;
  --successTx: #065f46;

  --dangerBg: #fee2e2;
  --dangerBd: #fecaca;
  --dangerTx: #991b1b;

  color: var(--text);
}

.ui .page-header{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:12px;
  margin: 6px 0 14px;
}

.ui .page-title{
  margin:0;
  font-size: 22px;
  font-weight: 900;
  letter-spacing: .2px;
}

.ui .page-subtitle{
  margin-top: 8px;
  font-size: 13px;
  color: var(--muted);
  display:flex;
  gap:10px;
  align-items:center;
}

/* Alerts */
.ui .alert{
  border-radius: 12px;
  padding: 12px 14px;
  border: 1px solid var(--border);
  margin: 10px 0 14px;
  font-size: 14px;
  background: var(--surface);
}

.ui .alert--success{
  background: var(--successBg);
  border-color: var(--successBd);
  color: var(--successTx);
}

.ui .alert--error{
  background: var(--dangerBg);
  border-color: var(--dangerBd);
  color: var(--dangerTx);
}

/* Card + Table */
.ui .card{
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow:hidden;
}

.ui .table-wrap{
  overflow-x:auto;
  border-radius: var(--radius);
}

.ui .table{
  width:100%;
  border-collapse: collapse;
}

.ui .table th,
.ui .table td{
  padding: 12px 14px;
  border-bottom: 1px solid var(--border);
  font-size: 14px;
  vertical-align: middle;
}

.ui .table th{
  background: #fbfcfe;
  color: #475569;
  font-weight: 900;
  font-size: 12px;
  letter-spacing: .06em;
  text-transform: uppercase;
}

.ui .table tbody tr:nth-child(even){ background:#fcfcfd; }
.ui .table tbody tr:hover td{ background:#f6f7f9; }
.ui .table tr:last-child td{ border-bottom: none; }

.ui .col-num{
  text-align:right;
  width: 120px;
  white-space: nowrap;
  font-variant-numeric: tabular-nums;
}

.ui .col-date{
  white-space: nowrap;
  width: 170px;
  color: var(--muted);
  font-size: 13px;
}

/* Price pill */
.ui .price{
  display:inline-flex;
  align-items:center;
  justify-content:flex-end;
  padding: 6px 10px;
  border-radius: 999px;
  border: 1px solid var(--border);
  background: #fbfcfe;
  font-weight: 900;
  font-size: 13px;
  white-space: nowrap;
}

/* Empty state */
.ui .empty{
  border: 1px dashed rgba(15,23,42,.18);
  border-radius: var(--radius);
  padding: 26px;
  background: #ffffff;
  text-align:center;
  box-shadow: var(--shadow);
}
.ui .empty__icon{ font-size: 26px; }
.ui .empty__title{ margin-top: 10px; font-weight: 900; }
.ui .empty__text{ margin-top: 6px; color: var(--muted); font-size: 13px; }

/* ===== Filter bar (padrão) ===== */
.ui .filter-bar{
  display:flex;
  gap:10px;
  align-items:center;
  justify-content:space-between;
  flex-wrap: wrap;
  padding: 12px;
  margin: 10px 0 14px;
}

.ui .filter-left{
  display:flex;
  gap:10px;
  align-items:center;
  flex-wrap: wrap;
  flex: 1;
}

.ui .filter-field{
  display:flex;
  flex-direction: column;
  gap: 6px;
}

.ui .filter-label{
  font-size: 12px;
  font-weight: 900;
  color: var(--muted);
}

.ui .filter-input{
  height: 40px;
  padding: 0 12px;
  border-radius: 12px;
  border: 1px solid var(--border);
  background: #fff;
  outline: none;
  min-width: 280px;
}

.ui .filter-input:focus{
  border-color: rgba(17,24,39,.35);
  box-shadow: 0 0 0 3px rgba(17,24,39,.08);
}

.ui .col-status{
  text-align: center;
  width: 140px;
  white-space: nowrap;
}

.ui .status-badge{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: .02em;
  border: 1px solid transparent;
}

/* variações */
.ui .status--aberta{
  background: #f8fafc;
  color: #6991c9;
  border-color: #e2e8f0;
}


.ui .status--concluida{
  background: #ecfdf5;
  color: #047857;
  border-color: #bbf7d0;
}

.ui .btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:8px;
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
  cursor: pointer;
}
.ui .btn:hover{ background: var(--primaryHover); transform: translateY(-1px); }

.ui .btn-ghost{
  background: #fff;
  color: var(--text);
  border: 1px solid var(--border);
}
.ui .btn-ghost:hover{
  background: #fbfcfe;
}

.ui .filter-meta{
  font-size: 13px;
  color: var(--muted);
  white-space: nowrap;
}

@media (max-width: 900px){
  .ui .page-header{ align-items:flex-start; }
  .ui .filter-input{ min-width: 220px; width: 100%; }
}
</style>

<div class="ui">

  <div class="page-header">
    <div>
      <h1 class="page-title">Ofertas</h1>
      <div class="page-subtitle">
        <span><?= count($ofertas) ?> registr<?= count($ofertas) === 1 ? 'o' : 'os' ?></span>
        <span class="muted">•</span>
        <span class="muted"><?= date('d/m/Y H:i') ?></span>
      </div>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="alert alert--success"><?= htmlspecialchars((string)$success) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert--error"><?= htmlspecialchars((string)$error) ?></div>
  <?php endif; ?>

  <!-- FILTER BAR PADRÃO -->
  <div class="card filter-bar">
    <form method="GET" style="width:100%; display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
      <input type="hidden" name="page" value="ofertas_criadas">

      <div class="filter-left">
        <div class="filter-field">
          <div class="filter-label">Buscar</div>
          <input
            class="filter-input"
            type="text"
            name="q"
            value="<?= htmlspecialchars($q) ?>"
            placeholder="Produto, categoria ou marca..."
          >
        </div>

        <button class="btn" type="submit">Filtrar</button>
        <a class="btn btn-ghost" href="/index.php?page=ofertas_criadas">Limpar</a>
      </div>

      <div class="filter-meta">
        <?= $q !== '' ? 'Filtro ativo: “' . htmlspecialchars($q) . '”' : 'Sem filtros' ?>
      </div>
    </form>
  </div>

  <?php if (empty($ofertas)): ?>
    <div class="empty">
      <div class="empty__icon">🏷️</div>
      <div class="empty__title">Nenhuma oferta encontrada</div>
      <div class="empty__text">
        <?= $q !== '' ? 'Tente ajustar sua busca.' : 'Quando você enviar uma oferta, ela vai aparecer aqui.' ?>
      </div>
    </div>
  <?php else: ?>
    <div class="card table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Produto</th>
            <th>Categoria</th>
            <th>Marca</th>
            <th class="col-num">Qtd</th>
            <th class="col-num">Valor unitário</th>
            <th class="col-num">Valor total</th>
            <th class="col-status">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ofertas as $r): ?>
            <?php $valorTotal = (float)($r['quantidade'] ?? 0) * (float)($r['valor_unitario'] ?? 0); ?>
            <tr>
              <td><?= htmlspecialchars((string)($r['produto'] ?? '-')) ?></td>
              <td><?= htmlspecialchars((string)($r['categoria'] ?? '-')) ?></td>
              <td><?= htmlspecialchars((string)($r['marca'] ?? '-')) ?></td>

              <td class="col-num"><?= (int)($r['quantidade'] ?? 0) ?></td>

              <td class="col-num">
                <span class="price">
                  <?php
                    $v = $r['valor_unitario'] ?? null;
                    echo $v !== null ? 'R$ ' . number_format((float)$v, 2, ',', '.') : '-';
                  ?>
                </span>
              </td>

              <td class="col-num">
                <span class="price">
                  <?= 'R$ ' . number_format((float)$valorTotal, 2, ',', '.') ?>
                </span>
              </td>

              <?php
                $status = (string)($r['status'] ?? '');
                $map = [
                  'PERDIDA' => ['label' => 'PERDIDA', 'class' => 'status-badge status--aberta'],
                  'GANHA' => ['label' => 'GANHA', 'class' => 'status-badge status--concluida'],
                ];
                $info = $map[$status] ?? ['label' => $status ?: '-', 'class' => 'status-badge'];
              ?>
              <td class="col-status">
                <span class="<?= $info['class'] ?>">
                  <?= htmlspecialchars($info['label']) ?>
                </span>
              </td>


            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>
