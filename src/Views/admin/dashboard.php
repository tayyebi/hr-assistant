<?php $layout = 'admin'; ?>

<header class="page-header">
    <h1 class="page-title">System Dashboard</h1>
    <p class="page-subtitle">Manage workspaces and system configuration</p>
</header>

<section>
    <div class="card-grid">
        <article class="card">
            <h2 class="card-label">Total Workspaces</h2>
            <p class="card-value"><?= htmlspecialchars((string)count($tenants ?? [])) ?></p>
        </article>
    </div>
</section>

<section>
    <h2 class="section-title">Workspaces</h2>
    
    <?php if (empty($tenants)): ?>
    <p class="text-muted">No workspaces found.</p>
    <?php else: ?>
    <div style="overflow-x: auto;">
        <table class="table" role="grid">
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Slug</th>
                    <th scope="col">Domain</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenants as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
                    <td><code><?= htmlspecialchars($t['slug']) ?></code></td>
                    <td><?= htmlspecialchars($t['domain'] ?? '—') ?></td>
                    <td>
                        <span class="badge" style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; <?= $t['is_active'] ? 'background: var(--success-light); color: var(--success);' : 'background: var(--border); color: var(--muted);' ?>">
                            <?= $t['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <a class="btn btn-sm" href="/w/<?= htmlspecialchars($t['slug']) ?>/dashboard" title="Open workspace">
                            Open
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
