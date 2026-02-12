<h2>Leave Balances – <?= $year ?></h2>
<p>
    <a href="<?= $prefix ?>/leave/balances?year=<?= $year - 1 ?>" class="btn btn-sm">← <?= $year - 1 ?></a>
    <strong><?= $year ?></strong>
    <a href="<?= $prefix ?>/leave/balances?year=<?= $year + 1 ?>" class="btn btn-sm"><?= $year + 1 ?> →</a>
</p>

<?php if (empty($balances)): ?>
    <p style="color:#666;">No balance records for <?= $year ?>.</p>
<?php else: ?>
<table><thead><tr><th>Employee</th><th>Type</th><th>Total</th><th>Used</th><th>Remaining</th></tr></thead><tbody>
<?php foreach ($balances as $b): ?>
    <tr>
        <td><?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?></td>
        <td><?= htmlspecialchars($b['type_name']) ?></td>
        <td><?= $b['total_days'] ?></td>
        <td><?= $b['used_days'] ?></td>
        <td><strong><?= number_format((float)$b['total_days'] - (float)$b['used_days'], 1) ?></strong></td>
    </tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
