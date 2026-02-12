<h2><?= htmlspecialchars($instance['label']) ?></h2>
<p><small><?= htmlspecialchars($instance['base_url']) ?></small></p>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
<div>
    <h3>Projects</h3>
    <table><thead><tr><th>Key</th><th>Name</th></tr></thead><tbody>
    <?php foreach ($projects as $p): ?>
        <tr><td><strong><?= htmlspecialchars($p['key'] ?? '') ?></strong></td><td><?= htmlspecialchars($p['name'] ?? '') ?></td></tr>
    <?php endforeach; ?>
    <?php if (empty($projects)): ?><tr><td colspan="2">No projects found or API error</td></tr><?php endif; ?>
    </tbody></table>
</div>
<div>
    <h3>Access Grants</h3>
    <table><thead><tr><th>Employee</th><th>Project</th><th>Role</th><th>Since</th></tr></thead><tbody>
    <?php foreach ($grants as $g): ?>
        <tr>
            <td><?= htmlspecialchars($g['first_name'] . ' ' . $g['last_name']) ?></td>
            <td><?= htmlspecialchars($g['project_key']) ?></td>
            <td><?= htmlspecialchars($g['role_name']) ?></td>
            <td><small><?= $g['granted_at'] ?></small></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($grants)): ?><tr><td colspan="4">No grants</td></tr><?php endif; ?>
    </tbody></table>

    <h4>Grant Access</h4>
    <form method="post" action="<?= $prefix ?>/jira/grant">
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
        <div class="form-group"><label>Jira Account ID</label><input name="jira_account_id" required></div>
        <div class="form-group"><label>Project Key</label><input name="project_key" placeholder="PROJ" required></div>
        <div class="form-group"><label>Project Name</label><input name="project_name"></div>
        <div class="form-group"><label>Role</label><input name="role_name" value="Member"></div>
        <button type="submit" class="btn btn-primary">Grant</button>
    </form>
</div>
</div>
