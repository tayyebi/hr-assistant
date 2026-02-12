<h2><?= htmlspecialchars($instance['label']) ?></h2>
<p><small><?= htmlspecialchars($instance['base_url']) ?></small></p>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
<div>
    <h3>Nextcloud Users</h3>
    <table><thead><tr><th>User ID</th></tr></thead><tbody>
    <?php foreach ($ncUsers as $u): ?>
        <tr><td><?= htmlspecialchars(is_array($u) ? ($u['id'] ?? '') : $u) ?></td></tr>
    <?php endforeach; ?>
    <?php if (empty($ncUsers)): ?><tr><td>No users or API error</td></tr><?php endif; ?>
    </tbody></table>
</div>
<div>
    <h3>Linked Employees</h3>
    <table><thead><tr><th>Employee</th><th>NC User</th><th>Since</th><th></th><th></th></tr></thead><tbody>
    <?php foreach ($links as $l): ?>
        <tr>
            <td><?= htmlspecialchars($l['first_name'] . ' ' . $l['last_name']) ?></td>
            <td><?= htmlspecialchars($l['nc_user_id']) ?></td>
            <td><small><?= $l['linked_at'] ?></small></td>
            <td><a href="<?= $prefix ?>/nextcloud/files/<?= $l['id'] ?>" class="btn btn-sm">Files</a></td>
            <td><form method="post" action="<?= $prefix ?>/nextcloud/unlink/<?= $l['id'] ?>" style="margin:0;"><button type="submit" class="btn btn-sm btn-danger">Unlink</button></form></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($links)): ?><tr><td colspan="5">No links</td></tr><?php endif; ?>
    </tbody></table>

    <h4>Link Employee</h4>
    <form method="post" action="<?= $prefix ?>/nextcloud/link">
        <input type="hidden" name="instance_id" value="<?= $instance['id'] ?>">
        <div class="form-group">
            <label>Employee</label>
            <select name="employee_id" required>
                <option value="">Select…</option>
                <?php foreach ($employees as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Nextcloud User ID</label><input name="nc_user_id" required></div>
        <div class="form-group"><label>Display Name</label><input name="nc_display_name"></div>
        <button type="submit" class="btn btn-primary">Link</button>
    </form>
</div>
</div>
