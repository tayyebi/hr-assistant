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
<form method="GET" action="<?php echo View::workspaceUrl('/employees/'); ?>" style="margin-bottom: var(--spacing-lg);">
    <search>
        <?php Icon::render('search', 20, 20); ?>
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
                            <button data-variant="ghost" data-size="icon"
                                    data-employee-id="<?php echo htmlspecialchars($emp['id']); ?>"
                                    onclick="manageAssets(this)">
                                <?php Icon::render('layers', 16, 16); ?>
                            </button>
                            <button data-variant="ghost" data-size="icon" 
                                    data-employee-id="<?php echo htmlspecialchars($emp['id']); ?>"
                                    data-full-name="<?php echo htmlspecialchars($emp['full_name']); ?>"
                                    data-position="<?php echo htmlspecialchars($emp['position']); ?>"
                                    data-email="<?php echo htmlspecialchars($emp['email']); ?>"
                                    data-telegram="<?php echo htmlspecialchars($emp['telegram_chat_id'] ?? ''); ?>"
                                    data-birthday="<?php echo htmlspecialchars($emp['birthday'] ?? ''); ?>"
                                    data-hired="<?php echo htmlspecialchars($emp['hired_date'] ?? ''); ?>"
                                    onclick="editEmployee(this)">
                                <?php Icon::render('edit', 16, 16); ?>
                            </button>
                            <form method="POST" action="<?php echo View::workspaceUrl('/employees/delete/'); ?>" style="display: inline;">
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

        <form method="POST" action="<?php echo View::workspaceUrl('/employees/'); ?>" data-employee-form>
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
function editEmployee(button) {
    const form = document.querySelector('[data-employee-form]');
    const dialog = document.querySelector('dialog');
    
    form.action = '/employees/update';
    form.querySelector('[name="id"]').value = button.dataset.employeeId;
    form.querySelector('[name="full_name"]').value = button.dataset.fullName;
    form.querySelector('[name="position"]').value = button.dataset.position;
    form.querySelector('[name="email"]').value = button.dataset.email;
    form.querySelector('[name="telegram_chat_id"]').value = button.dataset.telegram || '';
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

<!-- Manage Assets Modal -->
<dialog id="assets-dialog">
    <article>
        <header>
            <h3>Manage Assets for <span id="assets-employee-name"></span></h3>
            <button type="button" data-variant="ghost" data-size="icon" onclick="this.closest('dialog').close()">
                <?php Icon::render('close', 24, 24); ?>
            </button>
        </header>

        <section>
            <div id="provider-instances-container">Loading available provider instances...</div>
        </section>

        <footer>
            <button type="button" data-variant="secondary" onclick="this.closest('dialog').close()">Close</button>
        </footer>
    </article>
</dialog>

<script>
async function manageAssets(button) {
    const employeeId = button.dataset.employeeId;
    const dialog = document.getElementById('assets-dialog');
    document.getElementById('assets-employee-name').textContent = button.closest('tr').querySelector('strong').textContent;

    // Fetch provider instances
    const res = await fetch('/api/provider-instances');
    const data = await res.json();
    const container = document.getElementById('provider-instances-container');
    container.innerHTML = '';

    if (!data.success || !data.instances) {
        container.textContent = 'No provider instances available.';
    } else {
        // Group by type
        const grouped = {};
        data.instances.forEach(i => {
            if (!grouped[i.type]) grouped[i.type] = [];
            grouped[i.type].push(i);
        });

        for (const [type, instances] of Object.entries(grouped)) {
            const section = document.createElement('section');
            section.innerHTML = `<h4>${type}</h4>`;
            instances.forEach(inst => {
                const div = document.createElement('div');
                div.style.marginBottom = '8px';
                div.innerHTML = `
                    <strong>${inst.name}</strong> <small style="color: var(--text-muted);">(${inst.provider})</small>
                    <div style="margin-top: 6px; display:flex; gap:8px;">
                        <input type="text" placeholder="Identifier (email/username)" data-inst-id="${inst.id}" class="asset-identifier-input">
                        <select data-inst-type="${type}" data-inst-provider="${inst.provider}" class="asset-type-select">
                            <option value="">Select asset type</option>
                            <option value="email">email</option>
                            <option value="git">git</option>
                            <option value="messenger">messenger</option>
                            <option value="iam">iam</option>
                        </select>
                        <button data-inst-id="${inst.id}" onclick="assignAsset('${employeeId}', this)">Assign</button>
                    </div>
                `;
                section.appendChild(div);
            });
            container.appendChild(section);
        }
    }

    dialog.showModal();
}

async function assignAsset(employeeId, button) {
    const instId = button.dataset.instId;
    const wrap = button.closest('div');
    const identifier = wrap.querySelector('.asset-identifier-input').value;
    const select = wrap.querySelector('.asset-type-select');
    const assetType = select.value || 'git';

    const resp = await fetch('/assets/assign', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `employee_id=${encodeURIComponent(employeeId)}&provider_instance_id=${encodeURIComponent(instId)}&asset_identifier=${encodeURIComponent(identifier)}&asset_type=${encodeURIComponent(assetType)}`
    });
    const data = await resp.json();
    if (data.success) {
        alert('Asset assigned');
        location.reload();
    } else {
        alert('Failed: ' + (data.error || 'unknown'));
    }
}
</script>
