<?php $layout = 'minimal'; ?>
<div class="login-card">
    <h1 class="login-title">Select Workspace</h1>
<?php if (empty($tenants)): ?>
    <p class="text-muted">No workspaces available.</p>
<?php else: ?>
    <ul class="workspace-list">
<?php foreach ($tenants as $t): ?>
        <li><a href="/w/<?= htmlspecialchars($t['slug']) ?>/dashboard" class="workspace-link"><?= htmlspecialchars($t['name']) ?></a></li>
<?php endforeach; ?>
    </ul>
<?php endif; ?>
</div>
