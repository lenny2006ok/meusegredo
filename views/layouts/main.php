<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'MeuSegredo.Shop') ?></title>
    <meta name="description" content="Compartilhe seus segredos anonimamente">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/style.css">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body class="dark-theme">
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

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
