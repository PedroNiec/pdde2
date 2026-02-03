<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/FornecedorRepository.php';
require_once __DIR__ . '/../src/Repositories/RequisicaoRepository.php';
require_once __DIR__ . '/../src/Repositories/OfertaRepository.php';
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

$requisicoes = $service->listarRequisicoesAbertas($fornecedorId);
?>

<style>
/* ====== TEMP (somente esta tela) ====== */
.ui{
  --bg: #f6f7fb;
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

.ui .badge{
  display:inline-flex;
  align-items:center;
  padding: 5px 10px;
  border-radius: 999px;
  border: 1px solid var(--border);
  background: var(--surface);
  font-weight: 800;
  font-size: 12px;
  color: #334155;
}

.ui .alert{
  border-radius: 12px;
  padding: 12px 14px;
  border: 1px solid var(--border);
  margin: 10px 0 14px;
  font-size: 14px;
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
  width: 90px;
  white-space: nowrap;
  font-variant-numeric: tabular-nums;
}

.ui .col-date{
  white-space: nowrap;
  width: 160px;
  color: var(--muted);
  font-size: 13px;
}

.ui .col-actions{
  width: 150px;
  text-align: right;
}

.ui .btn{
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

.ui .btn:hover{
  background: var(--primaryHover);
  transform: translateY(-1px);
}

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

@media (max-width: 780px){
  .ui .col-date{ display:none; }
  .ui .col-actions{ text-align:left; }
  .ui .page-header{ align-items:flex-start; }
}
</style>

<div class="ui">

  <div class="page-header">
    <div>
      <h1 class="page-title">Requisições abertas</h1>
      <div class="page-subtitle">
        <span class="badge"><?= count($requisicoes) ?> disponíveis</span>
        <span><?= date('d/m/Y H:i') ?></span>
      </div>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="alert alert--success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (empty($requisicoes)): ?>
    <div class="empty">
      <div class="empty__icon">📭</div>
      <div class="empty__title">Nenhuma requisição aberta</div>
      <div class="empty__text">Quando surgir uma nova requisição para você, ela vai aparecer aqui.</div>
    </div>
  <?php else: ?>
    <div class="card table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Produto</th>
            <th class="col-num">Qtd</th>
            <th>Categoria</th>
            <th class="col-date">Criada em</th>
            <th class="col-actions">Ação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($requisicoes as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['produto'] ?? '-') ?></td>
              <td class="col-num"><?= (int)($r['quantidade'] ?? 0) ?></td>
              <td><?= htmlspecialchars($r['categoria_nome'] ?? '-') ?></td>
              <td class="col-date">
                <?php
                  $dt = $r['created_at'] ?? null;
                  echo $dt ? date('d/m/Y H:i', strtotime((string)$dt)) : '-';
                ?>
              </td>
              <td class="col-actions">
                <a class="btn" href="/index.php?page=oferta_nova&requisicao_id=<?= htmlspecialchars((string)$r['id']) ?>">
                  Fazer oferta
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>
