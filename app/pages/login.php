<?php
// se já estiver logado, manda pra home
if (!empty($_SESSION['user_id'])) {
    header('Location: /index.php?page=home');
    exit;
}

// mensagem de erro (flash)
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>

<h2>Login</h2>

<?php if ($error): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="POST" action="/app/actions/login_action.php">
    <div>
        <label>Email</label><br>
        <input type="email" name="email" required>
    </div>

    <div>
        <label>Senha</label><br>
        <input type="password" name="password" required>
    </div>

    <button type="submit">Entrar</button>
</form>
