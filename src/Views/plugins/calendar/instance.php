<div class="page-header">
    <h1><?= htmlspecialchars($instance['label']) ?></h1>
    <a href="<?= $prefix ?>/calendar" class="btn">Back</a>
</div>

<section>
    <h2>Employee Calendars</h2>
    <?php if (empty($assignments)): ?>
        <p>No calendar assignments yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Calendar Path</th>
                    <th>Access</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $asn): ?>
                    <tr>
                        <td><?= htmlspecialchars($asn['first_name'] . ' ' . $asn['last_name']) ?></td>
                        <td><?= htmlspecialchars($asn['calendar_path']) ?></td>
                        <td><?= htmlspecialchars($asn['access_level']) ?></td>
                        <td><?= htmlspecialchars($asn['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section>
    <h2>Assign Calendar</h2>
    <form method="post" action="<?= $prefix ?>/calendar/assign">
        <input type="hidden" name="instance_id" value="<?= $instance['id'] ?>">
        <div class="form-group">
            <label>Employee</label>
            <select name="employee_id" required>
                <option value="">Select employee</option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Calendar Path</label>
            <input type="text" name="calendar_path" placeholder="/calendars/john.doe/" required>
        </div>
        <div class="form-group">
            <label>Access Level</label>
            <select name="access_level" required>
                <option value="read" selected>Read</option>
                <option value="write">Write</option>
                <option value="owner">Owner</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Assign Calendar</button>
    </form>
</section>
