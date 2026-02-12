<h2>Documents Settings</h2>

<?php if (!empty($instances)): ?>
<table><thead><tr><th>Label</th><th>URL</th><th>Status</th></tr></thead><tbody>
<?php foreach ($instances as $i): ?>
    <tr><td><?= htmlspecialchars($i['label']) ?></td><td><?= htmlspecialchars($i['base_url']) ?></td><td><?= $i['is_active'] ? 'Active' : 'Inactive' ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>

<h3>Add Nextcloud Instance</h3>
<form method="post" action="<?= $prefix ?>/nextcloud/settings">
    <div class="form-group"><label>Label</label><input name="label" required></div>
    <div class="form-group"><label>Base URL</label><input name="base_url" placeholder="https://cloud.example.com" required></div>
    <div class="form-group"><label>Admin User</label><input name="admin_user" required></div>
    <div class="form-group"><label>Admin Password</label><input type="password" name="admin_password" required></div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
