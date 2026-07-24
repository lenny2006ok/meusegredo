<div style="max-width: 600px; margin: 40px auto; background: var(--bg-card); padding: 40px; border-radius: var(--radius); border: 1px solid var(--border-color); text-align: center;">
    <div style="font-size: 50px; margin-bottom: 20px;">🎉</div>
    <h2 style="font-size: 26px; margin-bottom: 12px; color: var(--accent);">Segredo Publicado!</h2>
    <p style="color: var(--text-secondary); margin-bottom: 30px; font-size: 15px; line-height: 1.6;">
        Seu segredo foi registrado com sucesso de forma totalmente anônima. Ele já está disponível no feed público para receber o apoio da comunidade.
    </p>

    <div style="display: flex; gap: 15px; justify-content: center;">
        <a href="/secrets/<?= (int)$id ?>/revelado" class="btn-primary">Visualizar meu Segredo</a>
        <a href="/" class="btn-primary" style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">Voltar ao Feed</a>
    </div>
</div>
