<?php $layout = 'admin'; ?>
<h1 class="page-title">Audit Log</h1>
<table class="table table-compact">
<thead><tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th><th>IP</th></tr></thead>
<tbody>
<?php foreach (($logs ?? []) as $l): ?>
<tr>
    <td class="text-muted text-sm"><?= $l['created_at'] ?></td>
    <td><?= htmlspecialchars($l['display_name'] ?? $l['email'] ?? '—') ?></td>
    <td><code><?= htmlspecialchars($l['action']) ?></code></td>
    <td><?= htmlspecialchars(($l['entity_type'] ?? '') . ($l['entity_id'] ? '#' . $l['entity_id'] : '')) ?></td>
    <td class="text-muted text-sm"><?= htmlspecialchars($l['ip_address'] ?? '') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
