<?php $layout = 'app'; ?>
<div class="page-header">
    <h1 class="page-title">Employees</h1>
</div>
<form method="post" action="<?= $prefix ?>/employees" class="inline-form">
    <input type="text" name="first_name" placeholder="First name" class="field-input field-sm" required>
    <input type="text" name="last_name" placeholder="Last name" class="field-input field-sm" required>
    <input type="text" name="employee_code" placeholder="Code" class="field-input field-sm">
    <input type="text" name="position" placeholder="Position" class="field-input field-sm">
    <input type="text" name="department" placeholder="Department" class="field-input field-sm">
    <input type="date" name="hire_date" class="field-input field-sm">
    <button type="submit" class="btn btn-primary btn-sm">Add</button>
</form>
<table class="table">
<thead><tr><th>Code</th><th>Name</th><th>Position</th><th>Department</th><th>Hire Date</th><th>Active</th></tr></thead>
<tbody>
<?php foreach (($employees ?? []) as $e): ?>
<tr>
    <td><code><?= htmlspecialchars($e['employee_code'] ?? '') ?></code></td>
    <td><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></td>
    <td><?= htmlspecialchars($e['position'] ?? '') ?></td>
    <td><?= htmlspecialchars($e['department'] ?? '') ?></td>
    <td><?= $e['hire_date'] ?? '—' ?></td>
    <td><?= $e['is_active'] ? 'Yes' : 'No' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
