<h2>Onboarding Templates</h2>

<?php if (!empty($templates)): ?>
<table><thead><tr><th>Name</th><th>Description</th><th>Active</th></tr></thead><tbody>
<?php foreach ($templates as $t): ?>
    <tr><td><?= htmlspecialchars($t['name']) ?></td><td><?= htmlspecialchars($t['description'] ?? '') ?></td><td><?= $t['is_active'] ? 'Yes' : 'No' ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>

<h3>Create Template</h3>
<form method="post" action="<?= $prefix ?>/onboarding/templates">
    <div class="form-group"><label>Name</label><input name="name" required></div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="2"></textarea></div>
    <fieldset style="border:1px solid #ddd;padding:12px;margin:12px 0;">
        <legend>Tasks</legend>
        <div id="tasks-list">
            <?php for ($i = 0; $i < 5; $i++): ?>
            <div style="display:flex;gap:8px;margin-bottom:6px;">
                <input name="task_title[]" placeholder="Task title" style="flex:1;">
                <input name="task_due_days[]" placeholder="Due in days" type="number" style="width:100px;" value="0">
            </div>
            <?php endfor; ?>
        </div>
    </fieldset>
    <button type="submit" class="btn btn-primary">Create Template</button>
</form>

<?php if (!empty($employees) && !empty($templates)): ?>
<h3>Start Onboarding</h3>
<form method="post" action="<?= $prefix ?>/onboarding/start">
    <div class="form-group">
        <label>Employee</label>
        <select name="employee_id" required>
            <option value="">Select…</option>
            <?php foreach ($employees as $e): ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Template</label>
        <select name="template_id" required>
            <option value="">Select…</option>
            <?php foreach ($templates as $t): ?>
                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Start Onboarding</button>
</form>
<?php endif; ?>
