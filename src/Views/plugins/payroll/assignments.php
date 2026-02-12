<h2>Payroll Assignments</h2>

<?php if (!empty($assignments)): ?>
<table><thead><tr><th>Employee</th><th>Structure</th><th>Custom Base</th><th>Effective From</th></tr></thead><tbody>
<?php foreach ($assignments as $a): ?>
    <tr>
        <td><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></td>
        <td><?= htmlspecialchars($a['structure_name']) ?></td>
        <td><?= $a['custom_base'] ? number_format((float)$a['custom_base'], 2) : '-' ?></td>
        <td><?= $a['effective_from'] ?></td>
    </tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>

<h3>Assign Employee</h3>
<form method="post" action="<?= $prefix ?>/payroll/assignments">
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
        <label>Salary Structure</label>
        <select name="structure_id" required>
            <option value="">Select…</option>
            <?php foreach ($structures as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= number_format((float)$s['base_amount'], 2) ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group"><label>Custom Base (optional)</label><input type="number" name="custom_base" step="0.01" placeholder="Override structure base"></div>
    <div class="form-group"><label>Effective From</label><input type="date" name="effective_from" value="<?= date('Y-m-d') ?>" required></div>
    <button type="submit" class="btn btn-primary">Assign</button>
</form>
