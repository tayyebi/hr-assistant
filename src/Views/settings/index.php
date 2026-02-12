<?php $layout = 'app'; ?>
<h1 class="page-title">Workspace Settings</h1>
<h2 class="section-title">Plugin Settings</h2>
<?php if (empty($settings)): ?>
<p class="text-muted">No plugin settings configured yet.</p>
<?php else: ?>
<table class="table table-compact">
<thead><tr><th>Plugin</th><th>Key</th><th>Value</th></tr></thead>
<tbody>
<?php foreach ($settings as $s): ?>
<tr>
    <td><?= htmlspecialchars($s['plugin_name']) ?></td>
    <td><code><?= htmlspecialchars($s['key']) ?></code></td>
    <td><?= htmlspecialchars($s['value'] ?? '') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
