<h2>Leave Settings</h2>

<h3>Leave Types</h3>
<?php if (!empty($types)): ?>
<table><thead><tr><th>Name</th><th>Default Days/Year</th><th>Paid</th><th>Approval</th><th>Active</th></tr></thead><tbody>
<?php foreach ($types as $t): ?>
    <tr>
        <td><span style="border-left:3px solid <?= htmlspecialchars($t['color']) ?>;padding-left:6px;"><?= htmlspecialchars($t['name']) ?></span></td>
        <td><?= $t['default_days_per_year'] ?></td>
        <td><?= $t['is_paid'] ? 'Yes' : 'No' ?></td>
        <td><?= $t['requires_approval'] ? 'Yes' : 'No' ?></td>
        <td><?= $t['is_active'] ? 'Yes' : 'No' ?></td>
    </tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>

<h3>Add Leave Type</h3>
<form method="post" action="<?= $prefix ?>/leave/settings">
    <div class="form-group"><label>Name</label><input name="name" required></div>
    <div class="form-group"><label>Color</label><input type="color" name="color" value="#3498db"></div>
    <div class="form-group"><label>Default Days / Year</label><input type="number" name="default_days_per_year" step="0.5" value="0"></div>
    <div class="form-group"><label><input type="checkbox" name="is_paid" checked> Paid</label></div>
    <div class="form-group"><label><input type="checkbox" name="requires_approval" checked> Requires Approval</label></div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
