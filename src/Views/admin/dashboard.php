<?php $layout = 'admin'; ?>
<h1 class="page-title">System Dashboard</h1>
<div class="card-grid">
    <div class="card">
        <div class="card-label">Tenants</div>
        <div class="card-value"><?= count($tenants ?? []) ?></div>
    </div>
</div>
<h2 class="section-title">Workspaces</h2>
<table class="table">
<thead><tr><th>Name</th><th>Slug</th><th>Domain</th><th>Active</th></tr></thead>
<tbody>
<?php foreach (($tenants ?? []) as $t): ?>
<tr>
    <td><?= htmlspecialchars($t['name']) ?></td>
    <td><code><?= htmlspecialchars($t['slug']) ?></code></td>
    <td><?= htmlspecialchars($t['domain'] ?? '—') ?></td>
    <td><?= $t['is_active'] ? 'Yes' : 'No' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
