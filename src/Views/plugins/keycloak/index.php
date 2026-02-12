<h2>Keycloak</h2>
<?php if (empty($instances)): ?>
    <p style="color:#666;">No Keycloak instances configured. <a href="<?= $prefix ?>/keycloak/settings">Add one</a>.</p>
<?php else: ?>
    <table><thead><tr><th>Label</th><th>URL</th><th>Realm</th><th>Status</th><th></th></tr></thead><tbody>
    <?php foreach ($instances as $i): ?>
        <tr>
            <td><?= htmlspecialchars($i['label']) ?></td>
            <td><small><?= htmlspecialchars($i['base_url']) ?></small></td>
            <td><?= htmlspecialchars($i['realm']) ?></td>
            <td><?= $i['is_active'] ? '<span style="color:green;">Active</span>' : 'Inactive' ?></td>
            <td><a href="<?= $prefix ?>/keycloak/instance/<?= $i['id'] ?>" class="btn btn-sm">Manage</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody></table>
<?php endif; ?>
