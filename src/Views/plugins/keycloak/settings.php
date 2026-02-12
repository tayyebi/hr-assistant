<h2>Keycloak Settings</h2>

<?php if (!empty($instances)): ?>
<table><thead><tr><th>Label</th><th>URL</th><th>Realm</th><th>Status</th></tr></thead><tbody>
<?php foreach ($instances as $i): ?>
    <tr><td><?= htmlspecialchars($i['label']) ?></td><td><?= htmlspecialchars($i['base_url']) ?></td><td><?= htmlspecialchars($i['realm']) ?></td><td><?= $i['is_active'] ? 'Active' : 'Inactive' ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>

<h3>Add Instance</h3>
<form method="post" action="<?= $prefix ?>/keycloak/settings">
    <div class="form-group"><label>Label</label><input name="label" required></div>
    <div class="form-group"><label>Base URL</label><input name="base_url" placeholder="https://auth.example.com" required></div>
    <div class="form-group"><label>Realm</label><input name="realm" value="master" required></div>
    <div class="form-group"><label>Client ID</label><input name="client_id" required></div>
    <div class="form-group"><label>Client Secret</label><input name="client_secret" required></div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
