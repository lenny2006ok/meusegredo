<?php
$categories = [
    'relacionamentos' => ['❤️', 'Relacionamentos'],
    'trabalho' => ['💼', 'Trabalho'],
    'familia' => ['👨‍👩‍👧', 'Família'],
    'escola' => ['🎓', 'Escola'],
    'dinheiro' => ['💰', 'Dinheiro'],
    'sobrenatural' => ['👻', 'Sobrenatural'],
    'desabafos' => ['😢', 'Desabafos'],
    'segredos' => ['🤫', 'Segredos'],
    'engracados' => ['🤣', 'Engraçados'],
    'outros' => ['📌', 'Outros']
];
?>

<div style="margin-bottom: 30px;">
    <h2 style="font-size: 24px; margin-bottom: 8px;">Categorias</h2>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="/" style="text-decoration: none; padding: 8px 16px; border-radius: 50px; background: <?= !isset($selectedCategory) ? 'var(--accent)' : 'var(--bg-card)' ?>; color: #fff; font-size: 14px; font-weight: 600; border: 1px solid var(--border-color);">✨ Todos</a>
        <?php foreach ($categories as $key => $val): ?>
            <a href="/secrets/category/<?= $key ?>" style="text-decoration: none; padding: 8px 16px; border-radius: 50px; background: <?= (isset($selectedCategory) && $selectedCategory === $key) ? 'var(--accent)' : 'var(--bg-card)' ?>; color: #fff; font-size: 14px; font-weight: 600; border: 1px solid var(--border-color);">
                <?= $val[0] ?> <?= $val[1] ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="feed" id="secrets-feed">
    <?php if (empty($posts)): ?>
        <div style="background: var(--bg-card); padding: 40px; text-align: center; border-radius: var(--radius); border: 1px solid var(--border-color);">
            <p style="color: var(--text-secondary); font-size: 16px;">Nenhum segredo encontrado por aqui ainda... Que tal revelar o primeiro?</p>
            <a href="/secrets/create" class="btn-primary" style="margin-top: 16px;">Contar meu segredo</a>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <?php
            $catIcon = $categories[$post['category']][0] ?? '📌';
            $catLabel = $categories[$post['category']][1] ?? 'Outros';
            ?>
            <div class="secret-card" onclick="window.location.href='/secrets/<?= $post['id'] ?>/revelado'">
                <div class="meta">
                    <span class="category"><?= $catIcon ?> <?= $catLabel ?></span>
                    <span class="pseudonym">por <?= htmlspecialchars($post['pseudonym']) ?></span>
                    <?php if ($post['city'] || $post['age']): ?>
                        <span style="color: var(--text-muted);">&bull;</span>
                        <span style="color: var(--text-muted);">
                            <?= $post['city'] ? htmlspecialchars($post['city']) : '' ?>
                            <?= ($post['city'] && $post['age']) ? ', ' : '' ?>
                            <?= $post['age'] ? (int)$post['age'] . ' anos' : '' ?>
                        </span>
                    <?php endif; ?>
                    <span class="time" style="margin-left: auto;"><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></span>
                </div>
                <h3><?= htmlspecialchars($post['title']) ?></h3>
                <p class="excerpt">
                    <?= nl2br(htmlspecialchars(mb_strimwidth($post['content'], 0, 300, '...'))) ?>
                </p>
                <div class="stats">
                    <span>👁️ <?= (int)$post['views'] ?> visualizações</span>
                    <span>❤️ <?= (int)$post['likes'] ?> curtidas</span>
                    <span>💔 <?= (int)$post['dislikes'] ?> descurtidas</span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if (!empty($posts) && count($posts) >= 10): ?>
    <div style="text-align: center; margin-top: 30px;">
        <button id="load-more-btn" class="btn-primary" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary);">Carregar Mais Segredos</button>
    </div>

    <script>
        let page = 2;
        const category = <?= isset($selectedCategory) ? json_encode($selectedCategory) : 'null' ?>;

        document.getElementById('load-more-btn').addEventListener('click', function() {
            let url = `/api/feed/load?page=${page}`;
            if (category) {
                url += `&category=${category}`;
            }

            fetch(url)
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.posts.length > 0) {
                        const feed = document.getElementById('secrets-feed');
                        res.posts.forEach(post => {
                            const date = new Date(post.created_at);
                            const formattedDate = date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
                            const excerpt = post.content.length > 300 ? post.content.substring(0, 300) + '...' : post.content;

                            let metaExtra = '';
                            if (post.city || post.age) {
                                metaExtra = ` <span style="color: var(--text-muted);">&bull;</span> <span style="color: var(--text-muted);">${post.city || ''} ${post.city && post.age ? ', ' : ''} ${post.age ? post.age + ' anos' : ''}</span>`;
                            }

                            const card = document.createElement('div');
                            card.className = 'secret-card';
                            card.onclick = () => window.location.href = `/secrets/${post.id}/revelado`;
                            card.innerHTML = `
                                <div class="meta">
                                    <span class="category">📌 ${post.category}</span>
                                    <span class="pseudonym">por ${post.pseudonym}</span>
                                    ${metaExtra}
                                    <span class="time" style="margin-left: auto;">${formattedDate}</span>
                                </div>
                                <h3>${post.title}</h3>
                                <p class="excerpt">${excerpt.replace(/\n/g, '<br>')}</p>
                                <div class="stats">
                                    <span>👁️ ${post.views} visualizações</span>
                                    <span>❤️ ${post.likes} curtidas</span>
                                    <span>💔 ${post.dislikes} descurtidas</span>
                                </div>
                            `;
                            feed.appendChild(card);
                        });
                        page++;
                        if (res.posts.length < 10) {
                            document.getElementById('load-more-btn').style.display = 'none';
                        }
                    } else {
                        document.getElementById('load-more-btn').style.display = 'none';
                    }
                });
        });
    </script>
<?php endif; ?>
