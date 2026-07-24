<?php
use MeuSegredo\Core\Security;
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
$catIcon = $categories[$secret['category']][0] ?? '📌';
$catLabel = $categories[$secret['category']][1] ?? 'Outros';
?>

<div class="secret-card" style="cursor: default; border-color: var(--border-color);">
    <div class="meta">
        <span class="category"><?= $catIcon ?> <?= $catLabel ?></span>
        <span class="pseudonym">por <?= htmlspecialchars($secret['pseudonym']) ?></span>
        <?php if ($secret['city'] || $secret['age']): ?>
            <span style="color: var(--text-muted);">&bull;</span>
            <span style="color: var(--text-muted);">
                <?= $secret['city'] ? htmlspecialchars($secret['city']) : '' ?>
                <?= ($secret['city'] && $secret['age']) ? ', ' : '' ?>
                <?= $secret['age'] ? (int)$secret['age'] . ' anos' : '' ?>
            </span>
        <?php endif; ?>
        <span class="time" style="margin-left: auto;"><?= date('d/m/Y H:i', strtotime($secret['created_at'])) ?></span>
    </div>
    <h2 style="font-size: 22px; margin-bottom: 12px; color: var(--text-primary);"><?= htmlspecialchars($secret['title']) ?></h2>
    <p style="color: var(--text-secondary); font-size: 15px; margin-bottom: 24px; line-height: 1.7; white-space: pre-wrap;"><?= htmlspecialchars($secret['content']) ?></p>

    <div class="stats" style="margin-bottom: 20px;">
        <span>👁️ <?= (int)$secret['views'] ?> visualizações</span>
        <button id="like-btn" style="background: none; border: none; cursor: pointer; color: #2ecc71; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">❤️ <span id="likes-count"><?= (int)$secret['likes'] ?></span> curtidas</button>
        <button id="dislike-btn" style="background: none; border: none; cursor: pointer; color: #e74c3c; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">💔 <span id="dislikes-count"><?= (int)$secret['dislikes'] ?></span> descurtidas</button>
        <button id="report-btn" style="background: none; border: none; cursor: pointer; color: var(--text-muted); font-weight: 600; margin-left: auto; display: inline-flex; align-items: center; gap: 5px;">⚠️ Denunciar</button>
    </div>
</div>

<div class="comment-section">
    <h3 style="font-size: 18px; margin-bottom: 16px;">Comentários (<?= count($comments) ?>)</h3>

    <form id="comment-form" action="/api/comment/store" method="POST" style="margin-bottom: 30px; background: var(--bg-card); padding: 20px; border-radius: var(--radius); border: 1px solid var(--border-color);">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Security::generateCSRF()) ?>">
        <input type="hidden" name="secret_id" value="<?= htmlspecialchars($secret['id']) ?>">
        <input type="hidden" id="parent_id" name="parent_id" value="">

        <div class="form-group">
            <label for="comment-text">Deixe um comentário sob pseudônimo</label>
            <textarea id="comment-text" name="content" required placeholder="Seja respeitoso ao comentar..." maxlength="2000" style="min-height: 80px;"></textarea>
        </div>

        <div style="display: flex; gap: 15px; align-items: flex-end; justify-content: space-between;">
            <div class="form-group" style="margin-bottom: 0; flex: 1; max-width: 300px;">
                <label for="comment-pseudo">Pseudônimo (Opcional)</label>
                <input type="text" id="comment-pseudo" name="pseudonym" placeholder="Ex: Gato Curioso" maxlength="50">
            </div>
            <button type="submit" class="btn-primary" style="padding: 10px 24px;">Enviar Comentário</button>
        </div>
    </form>

    <div id="comments-list">
        <?php if (empty($comments)): ?>
            <p id="no-comments" style="color: var(--text-muted); text-align: center; padding: 20px;">Nenhum comentário por aqui ainda. Seja o primeiro a comentar!</p>
        <?php else: ?>
            <?php
            $commentMap = [];
            foreach ($comments as $comment) {
                $comment['children'] = [];
                $commentMap[$comment['id']] = $comment;
            }
            $roots = [];
            foreach ($commentMap as $id => &$comment) {
                if ($comment['parent_id']) {
                    if (isset($commentMap[$comment['parent_id']])) {
                        $commentMap[$comment['parent_id']]['children'][] = &$comment;
                    } else {
                        $roots[] = &$comment;
                    }
                } else {
                    $roots[] = &$comment;
                }
            }

            if (!function_exists('renderCommentTree')) {
                function renderCommentTree($commentNodes, $depth = 0) {
                    foreach ($commentNodes as $node) {
                        ?>
                        <div class="comment" id="comment-<?= $node['id'] ?>" style="margin-left: <?= $depth * 25 ?>px; border-left: <?= $depth > 0 ? '2px solid var(--border-color)' : 'none' ?>; padding-left: <?= $depth > 0 ? '15px' : '0' ?>; background: var(--bg-card); border-radius: var(--radius); padding: 15px; margin-bottom: 12px; border: 1px solid var(--border-color);">
                            <div class="meta" style="display: flex; font-size: 13px; margin-bottom: 6px; color: var(--text-secondary);">
                                <span class="pseudonym" style="font-weight: 600; color: var(--accent);"><?= htmlspecialchars($node['pseudonym']) ?></span>
                                <span class="time" style="margin-left: auto; color: var(--text-muted);"><?= date('d/m/Y H:i', strtotime($node['created_at'])) ?></span>
                            </div>
                            <p class="content" style="color: var(--text-primary); font-size: 14px; margin-bottom: 10px; line-height: 1.5; white-space: pre-wrap;"><?= htmlspecialchars($node['content']) ?></p>
                            <div class="actions" style="display: flex; gap: 15px; font-size: 12px; color: var(--text-muted);">
                                <button onclick="replyToComment(<?= $node['id'] ?>, '<?= htmlspecialchars(addslashes($node['pseudonym'])) ?>')" style="background: none; border: none; color: var(--accent); cursor: pointer; font-weight: 600;">↩️ Responder</button>
                                <button onclick="likeComment(<?= $node['id'] ?>)" style="background: none; border: none; color: var(--text-muted); cursor: pointer;">❤️ <span id="comm-likes-<?= $node['id'] ?>"><?= (int)$node['likes'] ?></span></button>
                                <button onclick="reportComment(<?= $node['id'] ?>)" style="background: none; border: none; color: var(--text-muted); cursor: pointer; margin-left: auto;">⚠️ Denunciar</button>
                            </div>
                        </div>
                        <?php
                        if (!empty($node['children'])) {
                            renderCommentTree($node['children'], $depth + 1);
                        }
                    }
                }
            }

            renderCommentTree($roots);
            ?>
        <?php endif; ?>
    </div>
</div>

<script>
    function replyToComment(id, pseudonym) {
        document.getElementById('parent_id').value = id;
        document.getElementById('comment-text').placeholder = `Respondendo para ${pseudonym}...`;
        document.getElementById('comment-text').focus();
    }

    document.getElementById('like-btn').addEventListener('click', function() {
        const formData = new FormData();
        formData.append('id', <?= $secret['id'] ?>);
        fetch('/api/secret/like', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                document.getElementById('likes-count').innerText = res.likes;
            } else {
                alert(res.message);
            }
        });
    });

    document.getElementById('dislike-btn').addEventListener('click', function() {
        const formData = new FormData();
        formData.append('id', <?= $secret['id'] ?>);
        fetch('/api/secret/dislike', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                document.getElementById('dislikes-count').innerText = res.dislikes;
            } else {
                alert(res.message);
            }
        });
    });

    function likeComment(id) {
        const formData = new FormData();
        formData.append('id', id);
        fetch('/api/comment/like', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                document.getElementById('comm-likes-' + id).innerText = res.likes;
            } else {
                alert(res.message);
            }
        });
    }

    document.getElementById('report-btn').addEventListener('click', function() {
        const reason = prompt('Por que deseja denunciar este segredo? (Ex: conteúdo abusivo, spam, discurso de ódio)');
        if (!reason) return;

        const formData = new FormData();
        formData.append('secret_id', <?= $secret['id'] ?>);
        formData.append('reason', reason);

        fetch('/api/secret/report', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            alert(res.message);
        });
    });

    function reportComment(id) {
        const reason = prompt('Por que deseja denunciar este comentário? (Ex: abusivo, ofensivo)');
        if (!reason) return;

        const formData = new FormData();
        formData.append('comment_id', id);
        formData.append('reason', reason);

        fetch('/api/secret/report', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            alert(res.message);
        });
    }
</script>
