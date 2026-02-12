<div class="page-header">
    <h1>Calendar Settings</h1>
    <a href="<?= $prefix ?>/calendar" class="btn">Back</a>
</div>

<section>
    <h2>Configured Instances</h2>
    <?php if (empty($instances)): ?>
        <p>No instances configured yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Label</th>
                    <th>Base URL</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($instances as $inst): ?>
                    <tr>
                        <td><?= htmlspecialchars($inst['label']) ?></td>
                        <td><?= htmlspecialchars($inst['base_url']) ?></td>
                        <td><?= $inst['is_active'] ? 'Active' : 'Inactive' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section>
    <h2>Add Instance</h2>
    <form method="post">
        <div class="form-group">
            <label>Label</label>
            <input type="text" name="label" required>
        </div>
        <div class="form-group">
            <label>Base URL</label>
            <input type="text" name="base_url" placeholder="https://caldav.example.com" required>
        </div>
        <div class="form-group">
            <label>Admin Username</label>
            <input type="text" name="admin_username" required>
        </div>
        <div class="form-group">
            <label>Admin Password</label>
            <input type="password" name="admin_password" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Instance</button>
    </form>
</section>
