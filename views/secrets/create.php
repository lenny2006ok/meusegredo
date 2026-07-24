<?php
use MeuSegredo\Core\Security;
$config = require __DIR__ . '/../../config/env.php';
$siteKey = $config['TURNSTILE_SITE_KEY'] ?? '';
?>

<div style="max-width: 600px; margin: 0 auto; background: var(--bg-card); padding: 30px; border-radius: var(--radius); border: 1px solid var(--border-color);">
    <h2 style="font-size: 26px; margin-bottom: 12px; text-align: center; color: var(--accent);">🤫 Revelar Segredo</h2>
    <p style="text-align: center; color: var(--text-secondary); margin-bottom: 24px; font-size: 14px;">Seu segredo será publicado de forma 100% anônima e segura. Sinta-se livre para desabafar.</p>

    <form action="/secrets/store" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Security::generateCSRF()) ?>">

        <div class="form-group">
            <label for="title">Título do Segredo *</label>
            <input type="text" id="title" name="title" required placeholder="Ex: Guardei um segredo por 5 anos..." maxlength="150">
        </div>

        <div class="form-group">
            <label for="category">Categoria *</label>
            <select id="category" name="category" required>
                <option value="relacionamentos">❤️ Relacionamentos</option>
                <option value="trabalho">💼 Trabalho</option>
                <option value="familia">👨‍👩‍👧 Família</option>
                <option value="escola">🎓 Escola</option>
                <option value="dinheiro">💰 Dinheiro</option>
                <option value="sobrenatural">👻 Sobrenatural</option>
                <option value="desabafos">😢 Desabafos</option>
                <option value="segredos" selected>🤫 Segredos</option>
                <option value="engracados">🤣 Engraçados</option>
                <option value="outros">📌 Outros</option>
            </select>
        </div>

        <div class="form-group">
            <label for="content">Segredo *</label>
            <textarea id="content" name="content" required placeholder="Escreva aqui tudo o que você gostaria de desabafar anonimamente..." maxlength="5000"></textarea>
        </div>

        <div class="form-group">
            <label for="pseudonym">Pseudônimo (Opcional)</label>
            <input type="text" id="pseudonym" name="pseudonym" placeholder="Ex: Lobo Solitário (Deixe em branco para gerar aleatório)" maxlength="50">
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label for="city">Cidade (Opcional)</label>
                <input type="text" id="city" name="city" placeholder="Ex: São Paulo" maxlength="100">
            </div>
            <div class="form-group" style="width: 120px;">
                <label for="age">Idade (Opcional)</label>
                <input type="number" id="age" name="age" placeholder="Ex: 25" min="1" max="120">
            </div>
        </div>

        <div style="margin: 20px 0; text-align: center; display: flex; justify-content: center;">
            <?php if (!empty($siteKey)): ?>
                <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars($siteKey) ?>"></div>
            <?php else: ?>
                <div class="cf-turnstile" data-sitekey="1x00000000000000000000AA"></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 14px; font-size: 16px;">Publicar Anonimamente</button>
    </form>
</div>
