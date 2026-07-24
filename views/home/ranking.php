<div style="margin-bottom: 30px; text-align: center;">
    <h2 style="font-size: 28px; margin-bottom: 12px; color: var(--accent);">🏆 Ranking dos Segredos</h2>
    <p style="color: var(--text-secondary);">Os segredos mais curtidos e apoiados de toda a comunidade.</p>
</div>

<div class="feed">
    <?php if (empty($posts)): ?>
        <div style="background: var(--bg-card); padding: 40px; text-align: center; border-radius: var(--radius); border: 1px solid var(--border-color);">
            <p style="color: var(--text-secondary); font-size: 16px;">Nenhum segredo no ranking no momento.</p>
        </div>
    <?php else: ?>
        <?php $rank = 1; foreach ($posts as $post): ?>
            <div class="secret-card" onclick="window.location.href='/secrets/<?= $post['id'] ?>/revelado'">
                <div class="meta" style="align-items: center;">
                    <span style="font-size: 20px; font-weight: 800; color: #f1c40f; margin-right: 8px;">#<?= $rank++ ?></span>
                    <span class="category"><?= htmlspecialchars($post['category']) ?></span>
                    <span class="pseudonym">por <?= htmlspecialchars($post['pseudonym']) ?></span>
                    <span class="time" style="margin-left: auto;"><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></span>
                </div>
                <h3><?= htmlspecialchars($post['title']) ?></h3>
                <p class="excerpt">
                    <?= nl2br(htmlspecialchars(mb_strimwidth($post['content'], 0, 300, '...'))) ?>
                </p>
                <div class="stats">
                    <span>👁️ <?= (int)$post['views'] ?> visualizações</span>
                    <span style="color: #2ecc71; font-weight: 600;">❤️ <?= (int)$post['likes'] ?> curtidas</span>
                    <span>💔 <?= (int)$post['dislikes'] ?> descurtidas</span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
