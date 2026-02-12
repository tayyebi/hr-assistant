<h2>GitLab Settings</h2>

<h3>Instances</h3>
<?php if (!empty($instances)): ?>
<table><thead><tr><th>Label</th><th>URL</th><th>Status</th></tr></thead><tbody>
<?php foreach ($instances as $i): ?>
    <tr><td><?= htmlspecialchars($i['label']) ?></td><td><?= htmlspecialchars($i['base_url']) ?></td><td><?= $i['is_active'] ? 'Active' : 'Inactive' ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>

<h3>Add Instance</h3>
<form method="post" action="<?= $prefix ?>/gitlab/settings">
    <div class="form-group"><label>Label</label><input name="label" required></div>
    <div class="form-group"><label>Base URL</label><input name="base_url" placeholder="https://gitlab.example.com" required></div>
    <div class="form-group"><label>Access Token</label><input name="access_token" required></div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
