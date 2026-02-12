<h2>Payroll</h2>
<p>
    <a href="<?= $prefix ?>/payroll/structures" class="btn btn-sm">Structures</a>
    <a href="<?= $prefix ?>/payroll/assignments" class="btn btn-sm">Assignments</a>
</p>

<h3>Payroll Runs</h3>
<?php if (empty($runs)): ?>
    <p style="color:#666;">No payroll runs yet.</p>
<?php else: ?>
<table><thead><tr><th>Period</th><th>Status</th><th>Created</th><th></th></tr></thead><tbody>
<?php foreach ($runs as $r): ?>
    <tr>
        <td><?= $r['period_start'] ?> → <?= $r['period_end'] ?></td>
        <td>
            <?php if ($r['status'] === 'completed'): ?><span style="color:green;">Completed</span>
            <?php elseif ($r['status'] === 'processing'): ?><span style="color:#e67e22;">Processing</span>
            <?php elseif ($r['status'] === 'cancelled'): ?><span style="color:#999;">Cancelled</span>
            <?php else: ?><span style="color:#666;">Draft</span><?php endif; ?>
        </td>
        <td><small><?= $r['created_at'] ?></small></td>
        <td><a href="<?= $prefix ?>/payroll/run/<?= $r['id'] ?>" class="btn btn-sm">View</a></td>
    </tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>

<h3>New Payroll Run</h3>
<form method="post" action="<?= $prefix ?>/payroll/run">
    <div style="display:flex;gap:12px;align-items:end;">
        <div class="form-group"><label>Period Start</label><input type="date" name="period_start" required></div>
        <div class="form-group"><label>Period End</label><input type="date" name="period_end" required></div>
        <button type="submit" class="btn btn-primary">Run Payroll</button>
    </div>
</form>
