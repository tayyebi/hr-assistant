<div class="page-header">
    <h1>Leave Types</h1>
    <a href="<?= $prefix ?>/leave" class="btn">Back</a>
</div>

<section>
    <h2>Configured Leave Types</h2>
    <?php if (empty($types)): ?>
        <p>No leave types configured yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Days per Year</th>
                    <th>Requires Approval</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($types as $type): ?>
                    <tr>
                        <td><?= htmlspecialchars($type['name']) ?></td>
                        <td><?= htmlspecialchars($type['default_days_per_year']) ?></td>
                        <td><?= $type['requires_approval'] ? 'Yes' : 'No' ?></td>
                        <td><?= $type['is_active'] ? 'Active' : 'Inactive' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section>
    <h2>Add Leave Type</h2>
    <form method="post">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Default Days per Year</label>
            <input type="number" name="default_days_per_year" value="20" required>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="requires_approval" value="1" checked>
                Requires Approval
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Add Leave Type</button>
    </form>
</section>
