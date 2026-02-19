<?php $layout = 'app'; ?>

<header class="page-header">
    <h1 class="page-title">Employees</h1>
</header>

<section>
    <form method="post" action="<?= $prefix ?>/employees" class="form-stack mb-4" aria-label="Add new employee">
        <div class="form-row">
            <div class="form-col">
                <label for="first_name" class="field-label">First Name *</label>
                <input 
                    type="text" 
                    id="first_name"
                    name="first_name" 
                    placeholder="John" 
                    class="field-input" 
                    required
                    aria-required="true"
                >
            </div>
            <div class="form-col">
                <label for="last_name" class="field-label">Last Name *</label>
                <input 
                    type="text" 
                    id="last_name"
                    name="last_name" 
                    placeholder="Doe" 
                    class="field-input" 
                    required
                    aria-required="true"
                >
            </div>
        </div>

        <div class="form-row">
            <div class="form-col">
                <label for="employee_code" class="field-label">Employee Code</label>
                <input 
                    type="text" 
                    id="employee_code"
                    name="employee_code" 
                    placeholder="EMP-001" 
                    class="field-input"
                >
            </div>
            <div class="form-col">
                <label for="position" class="field-label">Position</label>
                <input 
                    type="text" 
                    id="position"
                    name="position" 
                    placeholder="Software Engineer" 
                    class="field-input"
                >
            </div>
        </div>

        <div class="form-row">
            <div class="form-col">
                <label for="department" class="field-label">Department</label>
                <input 
                    type="text" 
                    id="department"
                    name="department" 
                    placeholder="Engineering" 
                    class="field-input"
                >
            </div>
            <div class="form-col">
                <label for="hire_date" class="field-label">Hire Date</label>
                <input 
                    type="date" 
                    id="hire_date"
                    name="hire_date" 
                    class="field-input"
                >
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            Add Employee
        </button>
    </form>
</section>

<section>
    <h2 class="section-title">Employee Directory</h2>
    
    <?php if (empty($employees)): ?>
    <p class="text-muted">No employees found. Create your first employee using the form above.</p>
    <?php else: ?>
    <div style="overflow-x: auto;">
        <table class="table" role="grid">
            <thead>
                <tr>
                    <th scope="col">Code</th>
                    <th scope="col">Name</th>
                    <th scope="col">Position</th>
                    <th scope="col">Department</th>
                    <th scope="col">Hire Date</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $e): ?>
                <tr>
                    <td><code><?= htmlspecialchars($e['employee_code'] ?? '—') ?></code></td>
                    <td>
                        <strong><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></strong>
                    </td>
                    <td><?= htmlspecialchars($e['position'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($e['department'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($e['hire_date'] ?? '—') ?></td>
                    <td>
                        <span class="badge" style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; <?= $e['is_active'] ? 'background: var(--success-light); color: var(--success);' : 'background: var(--border); color: var(--muted);' ?>">
                            <?= $e['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
