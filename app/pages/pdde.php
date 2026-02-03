<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/PddeRepository.php';

$pdo = Database::getConnection();

$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$escolaId = (string)($_SESSION['escola_id'] ?? '');

$repo = new PddeRepository($pdo);
$pddes = $escolaId ? $repo->listarPorEscola($escolaId) : [];
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

/* Card */
.ui .card{
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow:hidden;
}

/* Form */
.ui .form-card{
  padding: 16px;
  margin-bottom: 16px;
}

.ui .form{
  margin: 0;
}

.ui .form-row{
  display:flex;
  gap: 12px;
}

.ui .field{
  display:flex;
  flex-direction: column;
  gap: 6px;
  flex: 1;
}

.ui .field label{
  font-size: 13px;
  font-weight: 900;
  color: var(--text);
}

.ui .field input{
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid var(--border);
  font-size: 14px;
  outline: none;
  background: #fff;
}

.ui .field input:focus{
  border-color: rgba(17,24,39,.35);
  box-shadow: 0 0 0 3px rgba(17,24,39,.08);
}

.ui .form-actions{
  display:flex;
  justify-content:flex-end;
  margin-top: 12px;
}

/* Buttons */
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
  cursor: pointer;
}

.ui .btn:hover{
  background: var(--primaryHover);
  transform: translateY(-1px);
}

/* Table */
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
  width: 140px;
  white-space: nowrap;
  font-variant-numeric: tabular-nums;
}

.ui .col-date{
  white-space: nowrap;
  width: 170px;
  color: var(--muted);
  font-size: 13px;
}

.ui .col-actions{
  width: 90px;
  text-align: right;
}

/* Links */
.ui .link{
  color: var(--text);
  text-decoration: none;
  font-weight: 900;
}
.ui .link:hover{ text-decoration: underline; }

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

@media (max-width: 900px){
  .ui .form-row{ flex-direction: column; }
  .ui .col-date{ display:none; }
  .ui .col-actions{ text-align:left; }
  .ui .page-header{ align-items:flex-start; }
}
</style>

<div class="ui">

  <div class="page-header">
    <div>
      <h1 class="page-title">PDDES</h1>
      <div class="page-subtitle">
        <span class="muted">Crie e visualize os PDDES da escola</span>
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

  <div class="card form-card">
    <form method="POST" action="/actions/criar_pdde_action.php" class="form">
      <div class="form-row">
        <div class="field" style="flex: 2;">
          <label for="nome">Nome do PDDE</label>
          <input type="text" id="nome" name="nome" required placeholder="Ex.: PDDE Estadual">
        </div>

        <div class="field" style="max-width: 260px;">
          <label for="saldo_inicial">Saldo Inicial</label>
          <input type="number" id="saldo_inicial" name="saldo_inicial" required step="0.01" min="0" placeholder="0,00">
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn">Criar PDDE</button>
      </div>
    </form>
  </div>

  <?php if (empty($pddes)): ?>
    <div class="empty">
      <div class="empty__icon">📁</div>
      <div class="empty__title">Nenhum PDDE cadastrado</div>
      <div class="empty__text">Crie um PDDE para começar a organizar os saldos.</div>
    </div>
  <?php else: ?>
    <div class="card table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Nome</th>
            <th class="col-num">Saldo inicial</th>
            <th class="col-num">Disponível</th>
            <th class="col-num">Bloqueado</th>
            <th class="col-num">Gasto</th>
            <th class="col-date">Criado em</th>
            <th class="col-actions">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pddes as $p): ?>
            <tr>
              <td><?= htmlspecialchars((string)($p['nome'] ?? '-')) ?></td>

              <td class="col-num">
                <?php
                  $v = (float)($p['saldo_inicial'] ?? 0);
                  echo 'R$ ' . number_format($v, 2, ',', '.');
                ?>
              </td>

              <td class="col-num">
                <?php
                  $v = (float)($p['saldo_disponivel'] ?? 0);
                  echo 'R$ ' . number_format($v, 2, ',', '.');
                ?>
              </td>

              <td class="col-num">
                <?php
                  $v = (float)($p['saldo_bloqueado'] ?? 0);
                  echo 'R$ ' . number_format($v, 2, ',', '.');
                ?>
              </td>

              <td class="col-num">
                <?php
                  $v = (float)($p['saldo_gasto'] ?? 0);
                  echo 'R$ ' . number_format($v, 2, ',', '.');
                ?>
              </td>

              <td class="col-date">
                <?php
                  $dt = $p['created_at'] ?? null;
                  echo $dt ? date('d/m/Y H:i', strtotime((string)$dt)) : '-';
                ?>
              </td>

              <td class="col-actions">
                <a class="link" href="/index.php?page=pdde_editar&id=<?= htmlspecialchars((string)$p['id']) ?>">Editar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>
