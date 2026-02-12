<h2>Salary Structures</h2>

<?php if (!empty($structures)): ?>
<table><thead><tr><th>Name</th><th>Base Amount</th><th>Currency</th><th>Frequency</th><th></th></tr></thead><tbody>
<?php foreach ($structures as $s): ?>
    <tr>
        <td><?= htmlspecialchars($s['name']) ?></td>
        <td><?= number_format((float)$s['base_amount'], 2) ?></td>
        <td><?= htmlspecialchars($s['currency']) ?></td>
        <td><?= htmlspecialchars($s['pay_frequency']) ?></td>
        <td><a href="<?= $prefix ?>/payroll/structure/<?= $s['id'] ?>" class="btn btn-sm">Components</a></td>
    </tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>

<h3>Add Structure</h3>
<form method="post" action="<?= $prefix ?>/payroll/structures">
    <div class="form-group"><label>Name</label><input name="name" required></div>
    <div class="form-group"><label>Base Amount</label><input type="number" name="base_amount" step="0.01" value="0" required></div>
    <div class="form-group"><label>Currency</label><input name="currency" value="USD" maxlength="3"></div>
    <div class="form-group"><label>Frequency</label>
        <select name="pay_frequency"><option value="monthly">Monthly</option><option value="biweekly">Biweekly</option><option value="weekly">Weekly</option></select>
    </div>
    <button type="submit" class="btn btn-primary">Create</button>
</form>
