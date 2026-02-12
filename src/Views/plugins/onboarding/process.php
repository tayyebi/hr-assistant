<h2>Onboarding – <?= htmlspecialchars($process['first_name'] . ' ' . $process['last_name']) ?></h2>
<p>Template: <?= htmlspecialchars($process['template_name']) ?> | Status:
    <?php if ($process['status'] === 'completed'): ?><span style="color:green;">Completed</span>
    <?php elseif ($process['status'] === 'cancelled'): ?><span style="color:#999;">Cancelled</span>
    <?php else: ?><span style="color:#e67e22;">In Progress</span><?php endif; ?>
</p>

<table>
<thead><tr><th>#</th><th>Task</th><th>Due</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($tasks as $i => $t): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td>
            <strong><?= htmlspecialchars($t['title']) ?></strong>
            <?php if ($t['description']): ?><br><small style="color:#666;"><?= htmlspecialchars($t['description']) ?></small><?php endif; ?>
            <?php if ($t['notes']): ?><br><small style="color:#999;">Note: <?= htmlspecialchars($t['notes']) ?></small><?php endif; ?>
        </td>
        <td><?= $t['due_date'] ?: '-' ?></td>
        <td>
            <?php if ($t['status'] === 'completed'): ?><span style="color:green;">✓ Done</span>
            <?php elseif ($t['status'] === 'skipped'): ?><span style="color:#999;">Skipped</span>
            <?php elseif ($t['status'] === 'in_progress'): ?><span style="color:#e67e22;">In Progress</span>
            <?php else: ?><span style="color:#666;">Pending</span><?php endif; ?>
        </td>
        <td>
            <?php if ($t['status'] !== 'completed' && $t['status'] !== 'skipped'): ?>
            <form method="post" action="<?= $prefix ?>/onboarding/task/<?= $t['id'] ?>/status" style="display:inline-flex;gap:4px;margin:0;">
                <input type="hidden" name="status" value="completed">
                <input name="notes" placeholder="Note" style="width:120px;padding:2px 4px;font-size:12px;">
                <button type="submit" class="btn btn-sm btn-primary">✓ Done</button>
            </form>
            <form method="post" action="<?= $prefix ?>/onboarding/task/<?= $t['id'] ?>/status" style="display:inline;margin:0;">
                <input type="hidden" name="status" value="skipped"><input type="hidden" name="notes" value="">
                <button type="submit" class="btn btn-sm">Skip</button>
            </form>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
