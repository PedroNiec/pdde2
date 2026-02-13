<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Repositories/CategoriaRepository.php';
$pdo = Database::getConnection();
$categoriaRepository = new CategoriaRepository($pdo);

$cats = $categoriaRepository->listarTodas();

$flashError = $_GET['err'] ?? '';
$flashOk    = $_GET['ok'] ?? '';
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <style>
        /* Cadastro público (cadastro_fornecedor) */
        .public-wrap{
            min-height: 100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding: 24px;
        }

        .public-card{
            width: min(920px, 100%);
            background: #fff;
            border: 1px solid rgba(0,0,0,.08);
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 10px 30px rgba(0,0,0,.06);
        }

        .public-header{
            margin-bottom: 14px;
        }

        .public-title{
            margin: 0 0 6px 0;
            font-size: 22px;
        }

        .public-subtitle{
            margin: 0;
            opacity: .75;
            font-size: 14px;
        }

        .alert{
            border-radius: 10px;
            padding: 12px 14px;
            margin: 14px 0;
            font-size: 14px;
            border: 1px solid rgba(0,0,0,.08);
        }
        .alert--error{ background: rgba(220,53,69,.08); }
        .alert--success{ background: rgba(25,135,84,.10); }

        .form-grid{
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-top: 10px;
        }

        .field label{
            display:block;
            font-size: 13px;
            margin-bottom: 6px;
            opacity: .85;
        }

        .field input, .field select{
            width:100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,.12);
            outline: none;
        }

        .field input:focus{
            border-color: rgba(46,204,113,.9);
            box-shadow: 0 0 0 4px rgba(46,204,113,.18);
        }

        .field--full{
            grid-column: 1 / -1;
        }

        .hint{
            display:block;
            margin-top: 8px;
            font-size: 12px;
            opacity: .75;
        }

        .cats-box{
            border: 1px solid rgba(0,0,0,.12);
            border-radius: 12px;
            padding: 12px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0,1fr));
            gap: 10px;
            max-height: 220px;
            overflow: auto;
        }

        .cat-item{
            display:flex;
            align-items:center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            background: rgba(0,0,0,.02);
            border: 1px solid rgba(0,0,0,.06);
            cursor: pointer;
        }

        .cat-item input{
            width: 16px;
            height: 16px;
        }

        .actions{
            display:flex;
            align-items:center;
            gap: 12px;
            margin-top: 6px;
        }

        .btn-primary{
            border: 0;
            border-radius: 12px;
            padding: 10px 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-link{
            text-decoration: none;
            font-size: 14px;
            opacity: .85;
        }

        @media (max-width: 760px){
            .form-grid{ grid-template-columns: 1fr; }
            .cats-box{ grid-template-columns: 1fr; }
        }

    </style>
</head>

<div class="public-wrap">
    <div class="public-card">
        <div class="public-header">
            <h1 class="public-title">Cadastro de Fornecedor</h1>
            <p class="public-subtitle">Preencha seus dados para solicitar acesso ao sistema.</p>
        </div>

        <?php if ($flashError): ?>
            <div class="alert alert--error"><?= htmlspecialchars((string)$flashError) ?></div>
        <?php endif; ?>

        <?php if ($flashOk): ?>
            <div class="alert alert--success"><?= htmlspecialchars((string)$flashOk) ?></div>
        <?php endif; ?>

        <form method="post" action="/index.php?action=fornecedor_public_store" class="form-grid" autocomplete="on">
            <div class="field">
                <label>Nome *</label>
                <input type="text" name="nome" required maxlength="200" placeholder="Ex.: João da Silva ME">
            </div>

            <div class="field">
                <label>CNPJ/CPF</label>
                <input type="text" name="cnpj_cpf" maxlength="30" placeholder="00.000.000/0000-00 ou 000.000.000-00">
            </div>

            <div class="field field--full">
                <label>Endereço</label>
                <input type="text" name="endereco" maxlength="255" placeholder="Rua, número, bairro, cidade/UF">
            </div>

            <div class="field">
                <label>Telefone</label>
                <input type="text" name="telefone" maxlength="30" placeholder="(00) 00000-0000">
            </div>

            <div class="field">
                <label>Responsável</label>
                <input type="text" name="responsavel" maxlength="200" placeholder="Nome do responsável">
            </div>

            <div class="field">
                <label>E-mail *</label>
                <input type="email" name="email" required maxlength="200" autocomplete="email" placeholder="seuemail@dominio.com">
            </div>

            <div class="field">
                <label>Senha *</label>
                <input type="password" name="senha" required minlength="6" autocomplete="new-password" placeholder="mínimo 6 caracteres">
            </div>

            <div class="field field--full">
                <label>Categorias *</label>

                <div class="cats-box">
                    <?php foreach ($cats as $c): ?>
                        <label class="cat-item">
                            <input type="checkbox" name="categorias[]" value="<?= htmlspecialchars((string)$c['id']) ?>">
                            <span><?= htmlspecialchars((string)$c['nome']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <small class="hint">Marque pelo menos uma categoria.</small>
            </div>


            <div class="actions field--full">
                <button type="submit" class="btn-primary">Cadastrar</button>
                <a class="btn-link" href="/index.php?page=login">Já tenho conta</a>
            </div>
        </form>
    </div>
</div>
</html>