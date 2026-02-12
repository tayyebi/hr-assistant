<h2><?= htmlspecialchars($instance['label']) ?></h2>
<p><small><?= htmlspecialchars($instance['base_url']) ?></small></p>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
<div>
    <h3>Spaces</h3>
    <table><thead><tr><th>Key</th><th>Name</th></tr></thead><tbody>
    <?php foreach ($spaces as $s): ?>
        <tr><td><strong><?= htmlspecialchars($s['key'] ?? '') ?></strong></td><td><?= htmlspecialchars($s['name'] ?? '') ?></td></tr>
    <?php endforeach; ?>
    <?php if (empty($spaces)): ?><tr><td colspan="2">No spaces found or API error</td></tr><?php endif; ?>
    </tbody></table>
</div>
<div>
    <h3>Space Grants</h3>
    <table><thead><tr><th>Employee</th><th>Space</th><th>Permission</th><th>Since</th></tr></thead><tbody>
    <?php foreach ($grants as $g): ?>
        <tr>
            <td><?= htmlspecialchars($g['first_name'] . ' ' . $g['last_name']) ?></td>
            <td><?= htmlspecialchars($g['space_key']) ?></td>
            <td><?= htmlspecialchars($g['permission_type']) ?></td>
            <td><small><?= $g['granted_at'] ?></small></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($grants)): ?><tr><td colspan="4">No grants</td></tr><?php endif; ?>
    </tbody></table>

    <h4>Grant Access</h4>
    <form method="post" action="<?= $prefix ?>/confluence/grant">
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
        <div class="form-group"><label>Confluence Account ID</label><input name="confluence_account_id" required></div>
        <div class="form-group"><label>Space Key</label><input name="space_key" placeholder="HR" required></div>
        <div class="form-group"><label>Space Name</label><input name="space_name"></div>
        <div class="form-group"><label>Permission</label>
            <select name="permission_type"><option value="read">Read</option><option value="write">Write</option><option value="admin">Admin</option></select>
        </div>
        <button type="submit" class="btn btn-primary">Grant</button>
    </form>
</div>
</div>
