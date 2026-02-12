<h2>Onboarding</h2>
<p><a href="<?= $prefix ?>/onboarding/templates" class="btn btn-sm">Templates</a></p>

<?php if (empty($processes)): ?>
    <p style="color:#666;">No onboarding processes yet.</p>
<?php else: ?>
    <table><thead><tr><th>Employee</th><th>Template</th><th>Started</th><th>Status</th><th></th></tr></thead><tbody>
    <?php foreach ($processes as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></td>
            <td><?= htmlspecialchars($p['template_name']) ?></td>
            <td><small><?= $p['started_at'] ?></small></td>
            <td>
                <?php if ($p['status'] === 'completed'): ?><span style="color:green;">Completed</span>
                <?php elseif ($p['status'] === 'cancelled'): ?><span style="color:#999;">Cancelled</span>
                <?php else: ?><span style="color:#e67e22;">In Progress</span><?php endif; ?>
            </td>
            <td><a href="<?= $prefix ?>/onboarding/process/<?= $p['id'] ?>" class="btn btn-sm">View</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody></table>
<?php endif; ?>
