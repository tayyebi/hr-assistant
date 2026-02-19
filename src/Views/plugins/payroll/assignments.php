<section>
    <h1 class="page-title">Payroll Assignments</h1>
    
    <?php if (!empty($assignments)): ?>
    <div style="overflow-x: auto; margin-bottom: 32px;">
        <table class="table" role="grid">
            <thead>
                <tr>
                    <th scope="col">Employee</th>
                    <th scope="col">Salary Structure</th>
                    <th scope="col">Custom Base</th>
                    <th scope="col">Effective From</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $a): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></strong></td>
                    <td><?= htmlspecialchars($a['structure_name']) ?></td>
                    <td><?= $a['custom_base'] ? number_format((float)$a['custom_base'], 2) : '—' ?></td>
                    <td><?= htmlspecialchars($a['effective_from']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p class="text-muted" style="margin-bottom: 32px;">No assignments configured yet.</p>
    <?php endif; ?>
</section>

<section>
    <h2 class="section-title">Assign Employee to Structure</h2>
    <form method="post" action="<?= $prefix ?>/payroll/assignments" class="form-stack" aria-label="Assign employee">
        <div class="form-group">
            <label for="employee_id" class="field-label">Employee *</label>
            <select id="employee_id" name="employee_id" class="field-input" required aria-required="true">
                <option value="">Select an employee…</option>
                <?php foreach ($employees as $e): ?>
                <option value="<?= htmlspecialchars((string)$e['id']) ?>"><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="structure_id" class="field-label">Salary Structure *</label>
            <select id="structure_id" name="structure_id" class="field-input" required aria-required="true">
                <option value="">Select a structure…</option>
                <?php foreach ($structures as $s): ?>
                <option value="<?= htmlspecialchars((string)$s['id']) ?>">
                    <?= htmlspecialchars($s['name']) ?> (<?= number_format((float)$s['base_amount'], 2) ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="custom_base" class="field-label">Custom Base Amount (optional)</label>
            <input type="number" id="custom_base" name="custom_base" step="0.01" class="field-input" placeholder="Leave blank to use structure base">
            <p class="field-help">Override the salary structure's base amount if needed</p>
        </div>
        <div class="form-group">
            <label for="effective_from" class="field-label">Effective From *</label>
            <input type="date" id="effective_from" name="effective_from" class="field-input" value="<?= htmlspecialchars(date('Y-m-d')) ?>" required aria-required="true">
        </div>
        <button type="submit" class="btn btn-primary">Assign Employee</button>
    </form>
</section>
