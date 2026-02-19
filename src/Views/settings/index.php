<?php $layout = 'app'; ?>

<header class="page-header">
    <h1 class="page-title">Workspace Settings</h1>
    <p class="page-subtitle">Manage workspace configuration and plugins</p>
</header>

<section>
    <h2 class="section-title">Plugin Settings</h2>
    
    <?php if (empty($settings)): ?>
    <p class="text-muted">No plugin settings have been configured yet.</p>
    <?php else: ?>
    <div style="overflow-x: auto;">
        <table class="table table-compact" role="grid">
            <thead>
                <tr>
                    <th scope="col">Plugin</th>
                    <th scope="col">Setting Key</th>
                    <th scope="col">Value</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($settings as $s): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($s['plugin_name']) ?></strong></td>
                    <td><code><?= htmlspecialchars($s['key']) ?></code></td>
                    <td><code><?= htmlspecialchars($s['value'] ?? '') ?></code></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
