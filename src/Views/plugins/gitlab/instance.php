<h2><?= htmlspecialchars($instance['label']) ?></h2>
<p><small><?= htmlspecialchars($instance['base_url']) ?></small></p>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
<div>
    <h3>Projects</h3>
    <table><thead><tr><th>Name</th><th>Path</th></tr></thead><tbody>
    <?php foreach ($projects as $p): ?>
        <tr><td><?= htmlspecialchars($p['name'] ?? $p['path_with_namespace'] ?? '') ?></td><td><small><?= htmlspecialchars($p['path_with_namespace'] ?? '') ?></small></td></tr>
    <?php endforeach; ?>
    <?php if (empty($projects)): ?><tr><td colspan="2">No projects found or API error</td></tr><?php endif; ?>
    </tbody></table>
</div>
<div>
    <h3>Access Grants</h3>
    <table><thead><tr><th>Employee</th><th>Project</th><th>Level</th><th>Since</th></tr></thead><tbody>
    <?php foreach ($grants as $g): ?>
        <tr>
            <td><?= htmlspecialchars($g['first_name'] . ' ' . $g['last_name']) ?></td>
            <td><?= htmlspecialchars($g['project_path']) ?></td>
            <td><?= (int)$g['access_level'] ?></td>
            <td><small><?= $g['granted_at'] ?></small></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($grants)): ?><tr><td colspan="4">No grants</td></tr><?php endif; ?>
    </tbody></table>

    <h4>Grant Access</h4>
    <form method="post" action="<?= $prefix ?>/gitlab/grant">
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
        <div class="form-group"><label>GitLab User ID</label><input name="gitlab_user_id" required></div>
        <div class="form-group"><label>Project Path</label><input name="project_path" placeholder="group/project" required></div>
        <div class="form-group"><label>Access Level</label>
            <select name="access_level"><option value="30">Developer (30)</option><option value="40">Maintainer (40)</option><option value="20">Reporter (20)</option><option value="10">Guest (10)</option></select>
        </div>
        <button type="submit" class="btn btn-primary">Grant</button>
    </form>
</div>
</div>
