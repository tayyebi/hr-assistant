<h2>Leave Management</h2>

<?php if ($isHR): ?>
<p><a href="<?= $prefix ?>/leave/balances" class="btn btn-sm">Balances</a> <a href="<?= $prefix ?>/leave/settings" class="btn btn-sm">Settings</a></p>
<?php endif; ?>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
<div>
    <h3><?= $isHR ? 'All Requests' : 'My Requests' ?></h3>
    <table><thead><tr>
        <?php if ($isHR): ?><th>Employee</th><?php endif; ?>
        <th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th>
        <?php if ($isHR): ?><th>Actions</th><?php endif; ?>
    </tr></thead><tbody>
    <?php foreach ($requests as $r): ?>
        <tr>
            <?php if ($isHR): ?><td><?= htmlspecialchars(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?></td><?php endif; ?>
            <td><span style="border-left:3px solid <?= htmlspecialchars($r['color'] ?? '#3498db') ?>;padding-left:6px;"><?= htmlspecialchars($r['type_name']) ?></span></td>
            <td><?= $r['start_date'] ?></td>
            <td><?= $r['end_date'] ?></td>
            <td><?= $r['days'] ?></td>
            <td>
                <?php if ($r['status'] === 'approved'): ?><span style="color:green;">Approved</span>
                <?php elseif ($r['status'] === 'rejected'): ?><span style="color:red;">Rejected</span>
                <?php elseif ($r['status'] === 'cancelled'): ?><span style="color:#999;">Cancelled</span>
                <?php else: ?><span style="color:#e67e22;">Pending</span><?php endif; ?>
            </td>
            <?php if ($isHR && $r['status'] === 'pending'): ?>
            <td>
                <form method="post" action="<?= $prefix ?>/leave/review/<?= $r['id'] ?>" style="display:inline-flex;gap:4px;margin:0;">
                    <input name="review_note" placeholder="Note" style="width:80px;padding:2px 4px;font-size:12px;">
                    <button name="action" value="approve" class="btn btn-sm btn-primary">✓</button>
                    <button name="action" value="reject" class="btn btn-sm btn-danger">✗</button>
                </form>
            </td>
            <?php elseif ($isHR): ?><td></td><?php endif; ?>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($requests)): ?><tr><td colspan="<?= $isHR ? 7 : 5 ?>">No requests</td></tr><?php endif; ?>
    </tbody></table>
</div>
<div>
    <h3>Request Leave</h3>
    <form method="post" action="<?= $prefix ?>/leave/request">
        <div class="form-group">
            <label>Type</label>
            <select name="leave_type_id" required>
                <option value="">Select…</option>
                <?php foreach ($leaveTypes as $lt): ?>
                    <option value="<?= $lt['id'] ?>"><?= htmlspecialchars($lt['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Start Date</label><input type="date" name="start_date" required></div>
        <div class="form-group"><label>End Date</label><input type="date" name="end_date" required></div>
        <div class="form-group"><label>Days</label><input type="number" name="days" step="0.5" min="0.5" value="1" required></div>
        <div class="form-group"><label>Reason</label><textarea name="reason" rows="2"></textarea></div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
</div>
