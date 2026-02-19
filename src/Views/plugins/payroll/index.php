<section>
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 class="page-title" style="margin: 0;">Payroll</h1>
        <nav style="display: flex; gap: 12px;">
            <a href="<?= $prefix ?>/payroll/structures" class="btn btn-sm" title="Manage salary structures">
                Salary Structures
            </a>
            <a href="<?= $prefix ?>/payroll/assignments" class="btn btn-sm" title="Manage employee assignments">
                Assignments
            </a>
        </nav>
    </header>
</section>

<section>
    <h2 class="section-title">Payroll Runs</h2>
    
    <?php if (empty($runs)): ?>
    <p class="text-muted">No payroll runs have been created yet. Create your first run below.</p>
    <?php else: ?>
    <div style="overflow-x: auto;">
        <table class="table" role="grid">
            <thead>
                <tr>
                    <th scope="col">Period</th>
                    <th scope="col">Status</th>
                    <th scope="col">Created</th>
                    <th scope="col" style="width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($runs as $r): ?>
                <tr>
                    <td>
                        <strong>
                            <?= htmlspecialchars($r['period_start']) ?> 
                            → 
                            <?= htmlspecialchars($r['period_end']) ?>
                        </strong>
                    </td>
                    <td>
                        <?php 
                        $status = $r['status'];
                        $statusColor = match($status) {
                            'completed' => 'var(--success)',
                            'processing' => 'var(--warning)',
                            'cancelled' => 'var(--muted)',
                            default => 'var(--muted)'
                        };
                        ?>
                        <span style="color: <?= $statusColor ?>; font-weight: 600;">
                            <?= htmlspecialchars(ucfirst($status)) ?>
                        </span>
                    </td>
                    <td class="text-muted text-sm">
                        <?= htmlspecialchars($r['created_at']) ?>
                    </td>
                    <td>
                        <a href="<?= $prefix ?>/payroll/run/<?= $r['id'] ?>" class="btn btn-sm" title="View payroll run">
                            View
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>

<section style="margin-top: 32px;">
    <h2 class="section-title">Create New Payroll Run</h2>
    <form method="post" action="<?= $prefix ?>/payroll/run" class="form-row" aria-label="Create payroll run">
        <div class="form-col">
            <label for="period_start" class="field-label">Period Start *</label>
            <input 
                type="date" 
                id="period_start"
                name="period_start" 
                class="field-input" 
                required
                aria-required="true"
            >
        </div>
        <div class="form-col">
            <label for="period_end" class="field-label">Period End *</label>
            <input 
                type="date" 
                id="period_end"
                name="period_end" 
                class="field-input" 
                required
                aria-required="true"
            >
        </div>
        <div style="display: flex; align-items: flex-end;">
            <button type="submit" class="btn btn-primary" title="Execute payroll run">
                Run Payroll
            </button>
        </div>
    </form>
</section>
