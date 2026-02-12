<h2><?= htmlspecialchars($announcement['title']) ?></h2>
<p style="color:#999;">
    Published: <?= $announcement['published_at'] ?? $announcement['created_at'] ?>
    <?php if ($announcement['author']): ?> by <?= htmlspecialchars($announcement['author']) ?><?php endif; ?>
    | Priority: <?= htmlspecialchars($announcement['priority']) ?>
</p>

<div style="border:1px solid #ddd;padding:16px;border-radius:4px;margin:16px 0;line-height:1.7;">
    <?= nl2br(htmlspecialchars($announcement['body'])) ?>
</div>

<a href="<?= $prefix ?>/announcements" class="btn btn-sm">← Back</a>
