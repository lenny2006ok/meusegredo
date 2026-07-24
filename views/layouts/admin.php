<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'MeuSegredo Admin') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body class="dark-theme">
    <nav class="navbar">
        <div class="container">
            <a href="/admin/dashboard" class="logo">MeuSegredo<span>.Admin</span></a>
            <div class="nav-links">
                <span style="color: #b0b0b0;">Olá, <strong><?= htmlspecialchars($_SESSION['admin_user'] ?? 'Moderador') ?></strong></span>
                <a href="/">Ir ao Site</a>
                <form action="/admin/logout" method="POST" style="display:inline;">
                    <button type="submit" style="background:none; border:none; color:#e74c3c; cursor:pointer; font-weight:600; font-size:14px; font-family:inherit;">Sair</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container" style="min-height: 70vh; padding-top: 30px; padding-bottom: 50px;">
        <?php if (isset($flash_message)): ?>
            <div class="alert alert-<?= htmlspecialchars($flash_type ?? 'info') ?>" style="background: <?= ($flash_type ?? 'success') === 'error' ? '#c0392b' : '#27ae60' ?>; color: #fff; padding: 12px 20px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
                <?= htmlspecialchars($flash_message) ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
