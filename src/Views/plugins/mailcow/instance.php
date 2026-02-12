<h2><?= htmlspecialchars($instance['label']) ?></h2>
<p><small><?= htmlspecialchars($instance['base_url']) ?></small></p>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
<div>
    <h3>Mailboxes</h3>
    <table><thead><tr><th>Username</th><th>Domain</th><th>Quota</th><th>Active</th></tr></thead><tbody>
    <?php foreach ($mailboxes as $m): ?>
        <tr>
            <td><?= htmlspecialchars($m['username'] ?? '') ?></td>
            <td><?= htmlspecialchars($m['domain'] ?? '') ?></td>
            <td><?= isset($m['quota']) ? number_format((int)$m['quota'] / 1048576) . ' MB' : '-' ?></td>
            <td><?= ($m['active'] ?? 0) ? 'Yes' : 'No' ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($mailboxes)): ?><tr><td colspan="4">No mailboxes or API error</td></tr><?php endif; ?>
    </tbody></table>
</div>
<div>
    <h3>Tracked Mailboxes</h3>
    <table><thead><tr><th>Employee</th><th>Local Part</th><th>Domain</th></tr></thead><tbody>
    <?php foreach ($tracked as $t): ?>
        <tr>
            <td><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></td>
            <td><?= htmlspecialchars($t['local_part']) ?></td>
            <td><?= htmlspecialchars($t['domain']) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($tracked)): ?><tr><td colspan="3">None</td></tr><?php endif; ?>
    </tbody></table>

    <h4>Create Mailbox</h4>
    <form method="post" action="<?= $prefix ?>/mailcow/create-mailbox">
        <input type="hidden" name="instance_id" value="<?= $instance['id'] ?>">
        <div class="form-group">
            <label>Employee</label>
            <select name="employee_id" required>
                <option value="">Select…</option>
                <?php foreach ($employees as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Local Part</label><input name="local_part" placeholder="john.doe" required></div>
        <div class="form-group"><label>Domain</label><input name="domain" placeholder="example.com" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
</div>
