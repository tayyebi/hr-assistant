<h2>Passbolt Settings</h2>

<?php if (!empty($instances)): ?>
<table><thead><tr><th>Label</th><th>URL</th><th>Status</th></tr></thead><tbody>
<?php foreach ($instances as $i): ?>
    <tr><td><?= htmlspecialchars($i['label']) ?></td><td><?= htmlspecialchars($i['base_url']) ?></td><td><?= $i['is_active'] ? 'Active' : 'Inactive' ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>

<h3>Add Instance</h3>
<form method="post" action="<?= $prefix ?>/passbolt/settings">
    <div class="form-group"><label>Label</label><input name="label" required></div>
    <div class="form-group"><label>Base URL</label><input name="base_url" placeholder="https://passbolt.example.com" required></div>
    <div class="form-group"><label>Admin API Key</label><input name="admin_api_key" required></div>
    <div class="form-group"><label>Server Key Fingerprint</label><input name="server_key_fingerprint" placeholder="optional"></div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
