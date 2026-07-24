<div style="margin-bottom: 30px;">
    <h2 style="font-size: 26px; margin-bottom: 8px;">Painel de Controle de Moderação</h2>
    <p style="color: var(--text-secondary);">Estatísticas e denúncias ativas que requerem sua ação.</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <div style="background: var(--bg-card); padding: 20px; border-radius: var(--radius); border: 1px solid var(--border-color); text-align: center;">
        <span style="font-size: 32px; font-weight: 800; color: var(--accent);"><?= (int)$totalSecrets ?></span>
        <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">Segredos Totais</p>
    </div>
    <div style="background: var(--bg-card); padding: 20px; border-radius: var(--radius); border: 1px solid var(--border-color); text-align: center;">
        <span style="font-size: 32px; font-weight: 800; color: #2ecc71;"><?= (int)$totalComments ?></span>
        <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">Comentários Totais</p>
    </div>
    <div style="background: var(--bg-card); padding: 20px; border-radius: var(--radius); border: 1px solid var(--border-color); text-align: center;">
        <span style="font-size: 32px; font-weight: 800; color: #f1c40f;"><?= (int)$totalReports ?></span>
        <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">Denúncias Pendentes</p>
    </div>
</div>

<div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius); padding: 25px;">
    <h3 style="font-size: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">Fila de Denúncias Pendentes</h3>

    <?php if (empty($reports)): ?>
        <p style="color: var(--text-muted); text-align: center; padding: 40px;">Excelente! Nenhuma denúncia pendente por enquanto.</p>
    <?php else: ?>
        <?php foreach ($reports as $rep): ?>
            <div style="background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius); padding: 20px; margin-bottom: 20px;">
                <div style="display: flex; gap: 10px; font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">
                    <span>Denúncia #<?= $rep['id'] ?></span>
                    <span>&bull;</span>
                    <span style="color: #e74c3c; font-weight: 600;">Motivo: <?= htmlspecialchars($rep['reason']) ?></span>
                    <span>&bull;</span>
                    <span>Enviado por IP: <?= htmlspecialchars($rep['ip']) ?></span>
                    <span>&bull;</span>
                    <span>Data: <?= date('d/m/Y H:i', strtotime($rep['created_at'])) ?></span>
                </div>

                <div style="background: var(--bg-card); padding: 12px; border-radius: 6px; margin-bottom: 16px; border-left: 3px solid var(--accent);">
                    <?php if ($rep['secret_id']): ?>
                        <strong style="font-size: 13px; color: var(--text-muted);">CONTEÚDO DENUNCIADO (SEGREDO #<?= $rep['secret_id'] ?>):</strong>
                        <h4 style="margin: 5px 0;"><?= htmlspecialchars($rep['secret_title'] ?? '') ?></h4>
                        <p style="font-size: 14px; margin: 0; line-height: 1.5; white-space: pre-wrap;"><?= htmlspecialchars($rep['secret_content'] ?? '') ?></p>
                    <?php else: ?>
                        <strong style="font-size: 13px; color: var(--text-muted);">CONTEÚDO DENUNCIADO (COMENTÁRIO #<?= $rep['comment_id'] ?>):</strong>
                        <p style="font-size: 14px; margin: 5px 0 0 0; line-height: 1.5; white-space: pre-wrap;"><?= htmlspecialchars($rep['comment_content'] ?? '') ?></p>
                    <?php endif; ?>
                </div>

                <?php if ($rep['details']): ?>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;"><strong>Mais detalhes da denúncia:</strong> <?= htmlspecialchars($rep['details']) ?></p>
                <?php endif; ?>

                <div style="display: flex; gap: 10px;">
                    <form action="/admin/reports/action" method="POST" style="display:inline;">
                        <input type="hidden" name="report_id" value="<?= $rep['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn-primary" style="background: #27ae60; font-size: 13px; padding: 8px 16px;">Manter Conteúdo (Rejeitar Denúncia)</button>
                    </form>
                    <form action="/admin/reports/action" method="POST" style="display:inline;">
                        <input type="hidden" name="report_id" value="<?= $rep['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn-primary" style="background: #c0392b; font-size: 13px; padding: 8px 16px;">Remover Conteúdo (Aprovar Denúncia)</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
