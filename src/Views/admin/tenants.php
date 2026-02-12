<?php $layout = 'admin'; ?>
<h1 class="page-title">Tenants</h1>
<form method="post" action="/admin/tenants" class="inline-form">
    <input type="text" name="name" placeholder="Name" class="field-input field-sm" required>
    <input type="text" name="slug" placeholder="slug" class="field-input field-sm" required>
    <input type="text" name="domain" placeholder="domain (optional)" class="field-input field-sm">
    <button type="submit" class="btn btn-primary btn-sm">Create</button>
</form>
<table class="table">
<thead><tr><th>ID</th><th>Name</th><th>Slug</th><th>Domain</th><th>Active</th><th>Created</th></tr></thead>
<tbody>
<?php foreach (($tenants ?? []) as $t): ?>
<tr>
    <td><?= $t['id'] ?></td>
    <td><?= htmlspecialchars($t['name']) ?></td>
    <td><code><?= htmlspecialchars($t['slug']) ?></code></td>
    <td><?= htmlspecialchars($t['domain'] ?? '—') ?></td>
    <td><?= $t['is_active'] ? 'Yes' : 'No' ?></td>
    <td><?= $t['created_at'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
