<header>
    <div>
        <h2>Employees</h2>
        <p>Manage your organization's workforce.</p>
    </div>
    <button onclick="document.querySelector('dialog').showModal()">
        <?php Icon::render('plus', 18, 18); ?>
        Add Employee
    </button>
</header>

<?php if (!empty($message)): ?>
    <output data-type="success"><?php echo htmlspecialchars($message); ?></output>
<?php endif; ?>

<!-- Search -->
<form method="GET" action="<?php echo \App\Core\UrlHelper::workspace('/employees/'); ?>" style="margin-bottom: var(--spacing-lg);">
    <search>
        <?php Icon::render('search', 20, 20); ?>
        <input type="search" name="search" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
    </search>
</form>

<!-- Employee Table -->
<div data-table>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Role</th>
                <th>Accounts</th>
                <th>Hired Date</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($employees)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: var(--spacing-xl); color: var(--text-muted);">
                        No employees found matching your search.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: var(--spacing-md);">
                                <figure data-avatar>
                                    <?php echo strtoupper(substr($emp['full_name'], 0, 1)); ?>
                                </figure>
                                <div>
                                    <strong><?php echo htmlspecialchars($emp['full_name']); ?></strong>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($emp['position']); ?></td>
                        <td>
                            <?php 
                            $accountCount = is_array($emp['accounts']) ? count($emp['accounts']) : 0;
                            if ($accountCount > 0): ?>
                                <mark data-status="active"><?php echo $accountCount; ?> linked</mark>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-style: italic; font-size: 0.75rem;">No accounts</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($emp['hired_date']); ?></td>
                        <td style="text-align: right;">
                            <button data-variant="ghost" data-size="icon" 
                                    data-employee-id="<?php echo htmlspecialchars($emp['id']); ?>"
                                    data-full-name="<?php echo htmlspecialchars($emp['full_name']); ?>"
                                    data-position="<?php echo htmlspecialchars($emp['position']); ?>"
                                    data-birthday="<?php echo htmlspecialchars($emp['birthday'] ?? ''); ?>"
                                    data-hired="<?php echo htmlspecialchars($emp['hired_date'] ?? ''); ?>"
                                    onclick="editEmployee(this)">
                                <?php Icon::render('edit', 16, 16); ?>
                            </button>
                            <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/employees/delete/'); ?>" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($emp['id']); ?>">
                                <button type="submit" data-variant="ghost" data-size="icon" onclick="return confirm('Are you sure you want to remove this employee?')">
                                    <?php Icon::render('trash', 16, 16, 'color: var(--color-danger);'); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Employee Modal -->
<dialog>
    <article>
        <header>
            <h3>Employee Profile</h3>
            <button type="button" data-variant="ghost" data-size="icon" onclick="this.closest('dialog').close()">
                <?php Icon::render('close', 24, 24); ?>
            </button>
        </header>

        <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/employees/'); ?>" data-employee-form>
            <input type="hidden" name="id">
            
            <section data-grid="2">
                <div>
                    <label>Full Name</label>
                    <input type="text" name="full_name" required>
                </div>
                <div>
                    <label>Position</label>
                    <input type="text" name="position" required>
                </div>
                <div>
                    <label>Birthday</label>
                    <input type="date" name="birthday" required>
                </div>
                <div>
                    <label>Hired Date</label>
                    <input type="date" name="hired_date" required value="<?php echo date('Y-m-d'); ?>">
                </div>
            </section>

            <footer>
                <button type="button" data-variant="secondary" onclick="this.closest('dialog').close()">Cancel</button>
                <button type="submit">Save</button>
            </footer>
        </form>
    </article>
</dialog>

<script>
function editEmployee(button) {
    const form = document.querySelector('[data-employee-form]');
    const dialog = document.querySelector('dialog');
    
    form.action = '/employees/update';
    form.querySelector('[name="id"]').value = button.dataset.employeeId;
    form.querySelector('[name="full_name"]').value = button.dataset.fullName;
    form.querySelector('[name="position"]').value = button.dataset.position;
    form.querySelector('[name="birthday"]').value = button.dataset.birthday;
    form.querySelector('[name="hired_date"]').value = button.dataset.hired;
    
    dialog.showModal();
}

// Reset form when dialog closes
document.querySelector('dialog').addEventListener('close', function() {
    const form = document.querySelector('[data-employee-form]');
    form.action = '/employees';
    form.reset();
    form.querySelector('[name="hired_date"]').value = '<?php echo date('Y-m-d'); ?>';
});
</script>


