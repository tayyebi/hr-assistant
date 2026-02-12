<h2><?= htmlspecialchars($instance['label']) ?></h2>
<p><small><?= htmlspecialchars($instance['base_url']) ?></small></p>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
<div>
    <h3>Passbolt Users</h3>
    <table><thead><tr><th>Username</th><th>Name</th></tr></thead><tbody>
    <?php foreach ($pbUsers as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['username'] ?? '') ?></td>
            <td><?= htmlspecialchars(($u['profile']['first_name'] ?? '') . ' ' . ($u['profile']['last_name'] ?? '')) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($pbUsers)): ?><tr><td colspan="2">No users or API error</td></tr><?php endif; ?>
    </tbody></table>
</div>
<div>
    <h3>Linked Employees</h3>
    <table><thead><tr><th>Employee</th><th>PB Username</th><th>Since</th><th></th></tr></thead><tbody>
    <?php foreach ($links as $l): ?>
        <tr>
            <td><?= htmlspecialchars($l['first_name'] . ' ' . $l['last_name']) ?></td>
            <td><?= htmlspecialchars($l['username']) ?></td>
            <td><small><?= $l['linked_at'] ?></small></td>
            <td><form method="post" action="<?= $prefix ?>/passbolt/unlink/<?= $l['id'] ?>" style="margin:0;"><button type="submit" class="btn btn-sm btn-danger">Unlink</button></form></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($links)): ?><tr><td colspan="4">No links</td></tr><?php endif; ?>
    </tbody></table>

    <h4>Link Employee</h4>
    <form method="post" action="<?= $prefix ?>/passbolt/link">
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
        <div class="form-group"><label>Passbolt User ID</label><input name="passbolt_user_id" required></div>
        <div class="form-group"><label>Username / Email</label><input name="username"></div>
        <button type="submit" class="btn btn-primary">Link</button>
    </form>
</div>
</div>
