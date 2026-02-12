<h2>Payroll Run: <?= $run['period_start'] ?> → <?= $run['period_end'] ?></h2>
<p>Status:
    <?php if ($run['status'] === 'completed'): ?><span style="color:green;">Completed</span>
    <?php else: ?><?= htmlspecialchars($run['status']) ?><?php endif; ?>
    <?php if ($run['completed_at']): ?> | Completed: <?= $run['completed_at'] ?><?php endif; ?>
</p>

<?php if (empty($payslips)): ?>
    <p style="color:#666;">No payslips generated. Ensure employees are assigned to salary structures.</p>
<?php else: ?>
<table>
<thead><tr><th>Employee</th><th>Base Salary</th><th>Earnings</th><th>Deductions</th><th>Net Pay</th><th>Detail</th></tr></thead>
<tbody>
<?php $totalNet = 0; foreach ($payslips as $ps): $totalNet += (float)$ps['net_pay']; ?>
    <tr>
        <td><?= htmlspecialchars($ps['first_name'] . ' ' . $ps['last_name']) ?></td>
        <td style="text-align:right;"><?= number_format((float)$ps['base_salary'], 2) ?></td>
        <td style="text-align:right;color:green;">+<?= number_format((float)$ps['total_earnings'], 2) ?></td>
        <td style="text-align:right;color:red;">-<?= number_format((float)$ps['total_deductions'], 2) ?></td>
        <td style="text-align:right;"><strong><?= number_format((float)$ps['net_pay'], 2) ?></strong></td>
        <td>
            <?php $bd = json_decode($ps['breakdown_json'] ?? '[]', true); ?>
            <?php foreach ($bd as $c): ?>
                <small><?= htmlspecialchars($c['name']) ?>: <?= $c['type'] === 'earning' ? '+' : '-' ?><?= number_format($c['amount'], 2) ?></small><br>
            <?php endforeach; ?>
        </td>
    </tr>
<?php endforeach; ?>
<tr style="font-weight:bold;border-top:2px solid #232f3e;">
    <td>Total</td><td></td><td></td><td></td><td style="text-align:right;"><?= number_format($totalNet, 2) ?></td><td></td>
</tr>
</tbody>
</table>
<?php endif; ?>
