<header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title" style="margin: 0;">Onboarding</h1>
    <a href="<?= $prefix ?>/onboarding/templates" class="btn btn-sm" title="Manage templates">
        Templates
    </a>
</header>

<section>
    <h2 class="section-title">Onboarding Processes</h2>
    
    <?php if (empty($processes)): ?>
    <p class="text-muted">No onboarding processes have been started yet.</p>
    <?php else: ?>
    <div style="overflow-x: auto;">
        <table class="table" role="grid">
            <thead>
                <tr>
                    <th scope="col">Employee</th>
                    <th scope="col">Template</th>
                    <th scope="col">Started</th>
                    <th scope="col">Status</th>
                    <th scope="col" style="width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($processes as $p): ?>
                <tr>
                    <td>
                        <strong>
                            <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                        </strong>
                    </td>
                    <td><?= htmlspecialchars($p['template_name']) ?></td>
                    <td class="text-muted text-sm">
                        <?= htmlspecialchars($p['started_at']) ?>
                    </td>
                    <td>
                        <?php 
                        $status = $p['status'];
                        $statusColor = match($status) {
                            'completed' => 'var(--success)',
                            'cancelled' => 'var(--muted)',
                            default => 'var(--warning)'
                        };
                        ?>
                        <span style="color: <?= $statusColor ?>; font-weight: 600;">
                            <?= htmlspecialchars(ucfirst($status)) ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= $prefix ?>/onboarding/process/<?= $p['id'] ?>" class="btn btn-sm" title="View process">
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
