<?php
declare(strict_types=1);


unset($_SESSION['flash_error']);

?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>

  <style>
    :root{
      --bg: #f4f6f8;
      --card: #ffffff;
      --text: #0f172a;
      --muted: #64748b;
      --border: #e5e7eb;
      --primary: #111827;
      --danger: #dc2626;
      --radius: 14px;
      --shadow: 0 20px 40px rgba(0,0,0,.08);
      --focus: 0 0 0 3px rgba(17,24,39,.12);
    }

    *{ box-sizing: border-box; }
    html, body{ height: 100%; }

    body{
      margin: 0;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: var(--bg);
      color: var(--text);
      display: grid;
      place-items: center;
      padding: 24px;
    }

    .card{
      width: 100%;
      max-width: 420px;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 28px;
    }

    h1{
      font-size: 20px;
      margin: 0 0 6px;
      font-weight: 700;
    }

    .subtitle{
      margin: 0 0 22px;
      font-size: 14px;
      color: var(--muted);
    }

    .error{
      border: 1px solid #fecaca;
      background: #fee2e2;
      color: var(--danger);
      padding: 10px 12px;
      border-radius: 10px;
      margin-bottom: 16px;
      font-size: 13px;
    }

    .field{
      margin-bottom: 14px;
    }

    label{
      display: block;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 6px;
    }

    input{
      width: 100%;
      padding: 12px;
      border-radius: 10px;
      border: 1px solid var(--border);
      font-size: 14px;
      outline: none;
      transition: border-color .15s ease, box-shadow .15s ease;
    }

    input:focus{
      border-color: var(--primary);
      box-shadow: var(--focus);
    }

    button{
      width: 100%;
      margin-top: 6px;
      padding: 12px;
      border-radius: 10px;
      border: none;
      cursor: pointer;
      font-weight: 600;
      font-size: 14px;
      background: var(--primary);
      color: #fff;
      transition: filter .15s ease;
    }

    button:hover{
      filter: brightness(1.1);
    }

    .footer{
      margin-top: 16px;
      text-align: center;
      font-size: 12px;
      color: var(--muted);
    }
  </style>
</head>
<body>

  <div class="card">
    <h1>Entrar</h1>
    <p class="subtitle">Use seu email e senha para acessar</p>

<form method="POST" action="/index.php?action=login_action">
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus>
      </div>

      <div class="field">
        <label for="password">Senha</label>
        <input type="password" id="password" name="password" required>
      </div>

      <button type="submit">Entrar</button>
    </form>

    <div class="footer">
        <a href="/index.php?page=cadastro_fornecedor">Sou fornecedor e quero me cadastrar</a>
    </div>
  </div>

</body>
</html>
