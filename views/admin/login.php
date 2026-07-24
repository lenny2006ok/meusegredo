<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - MeuSegredo.Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background: var(--bg-primary);
        }
        .login-box {
            width: 100%;
            max-width: 400px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            padding: 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
    </style>
</head>
<body class="dark-theme">
    <div class="login-box">
        <h2 style="font-size: 24px; text-align: center; margin-bottom: 8px; color: var(--accent);">MeuSegredo<span>.Admin</span></h2>
        <p style="text-align: center; font-size: 13px; color: var(--text-secondary); margin-bottom: 24px;">Autenticação de moderador</p>

        <?php if (isset($flash_message)): ?>
            <div style="background: #c0392b; color: #fff; padding: 10px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; font-weight: 600; text-align: center;">
                <?= htmlspecialchars($flash_message) ?>
            </div>
        <?php endif; ?>

        <form action="/admin/login" method="POST">
            <div class="form-group">
                <label for="username">Usuário</label>
                <input type="text" id="username" name="username" required placeholder="admin" autofocus>
            </div>
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; margin-top: 10px; font-size: 14px;">Entrar no Painel</button>
        </form>
        <p style="text-align: center; margin-top: 20px; font-size: 13px;"><a href="/" style="color: var(--text-muted); text-decoration: none;">&larr; Voltar ao site público</a></p>
    </div>
</body>
</html>
