<header>
    <div>
        <h2>Employees</h2>
        <p>Manage your organization's workforce.</p>
    </div>
    <button onclick="document.querySelector('dialog').showModal()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Add Employee
    </button>
</header>

<?php if (!empty($message)): ?>
    <output data-type="success"><?php echo htmlspecialchars($message); ?></output>
<?php endif; ?>

<!-- Search -->
<form method="GET" action="/employees" style="margin-bottom: var(--spacing-lg);">
    <search>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="search" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
    </search>
</form>

<!-- Employee Table -->
<div data-table>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Role</th>
                <th>Telegram ID</th>
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
                                    <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted);">
                                        <?php echo htmlspecialchars($emp['email']); ?>
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($emp['position']); ?></td>
                        <td>
                            <?php if (!empty($emp['telegram_chat_id'])): ?>
                                <mark data-status="active">#<?php echo htmlspecialchars($emp['telegram_chat_id']); ?></mark>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-style: italic; font-size: 0.75rem;">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($emp['hired_date']); ?></td>
                        <td style="text-align: right;">
                            <button data-variant="ghost" data-size="icon" onclick="editEmployee('<?php echo htmlspecialchars(json_encode($emp), ENT_QUOTES); ?>')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                            <form method="POST" action="/employees/delete" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($emp['id']); ?>">
                                <button type="submit" data-variant="ghost" data-size="icon" onclick="return confirm('Are you sure you want to remove this employee?')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-danger);">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
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
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </header>

        <form method="POST" action="/employees" data-employee-form>
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
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div>
                    <label>Telegram Chat ID</label>
                    <input type="text" name="telegram_chat_id" placeholder="e.g. 12345678">
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
function editEmployee(jsonStr) {
    const emp = JSON.parse(jsonStr);
    const form = document.querySelector('[data-employee-form]');
    const dialog = document.querySelector('dialog');
    
    form.action = '/employees/update';
    form.querySelector('[name="id"]').value = emp.id;
    form.querySelector('[name="full_name"]').value = emp.full_name;
    form.querySelector('[name="position"]').value = emp.position;
    form.querySelector('[name="email"]').value = emp.email;
    form.querySelector('[name="telegram_chat_id"]').value = emp.telegram_chat_id || '';
    form.querySelector('[name="birthday"]').value = emp.birthday;
    form.querySelector('[name="hired_date"]').value = emp.hired_date;
    
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
