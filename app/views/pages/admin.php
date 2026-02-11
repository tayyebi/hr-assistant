<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>HR Assistant - System Administration</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body data-page="admin">
    <nav class="navbar is-primary" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <div class="navbar-item">
                <h1 class="title is-5">HR Assistant</h1>
            </div>
        </div>
        <div class="navbar-end">
            <div class="navbar-item">
                <div class="is-flex is-align-items-center gap-05 text-inherit">
                    <small><?php echo htmlspecialchars($user['email']); ?></small>
                    <a href="<?php echo \App\Core\UrlHelper::url('/logout'); ?>" class="is-flex is-align-items-center gap-05 text-inherit">
                        <span class="icon is-small">
                            <?php \App\Core\Icon::render('logout', 16, 16); ?>
                        </span>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="section">
        <h2 class="title">Tenant Management</h2>

        <?php if (!empty($message)): ?>
            <div class="notification <?php echo ($messageType ?? 'success') === 'error' ? 'is-danger' : 'is-success'; ?>">
                <button class="delete"></button>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="columns is-multiline">
            <!-- Tenant List -->
            <div class="column is-two-thirds-tablet is-full-mobile">
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">All Tenants (<?php echo count($tenants); ?>)</p>
                    </header>
                    <div class="card-content">
                        <?php if (!empty($tenants)): ?>
                            <div class="content">
                                <?php foreach ($tenants as $tenant): 
                                    $isActive = ($tenant['status'] ?? 'active') === 'active';
                                ?>
                                    <div class="box" style="<?php echo !$isActive ? 'opacity: 0.6;' : ''; ?>">
                                        <div class="is-flex is-justify-content-space-between is-align-items-center">
                                            <div style="flex: 1;">
                                                <div class="is-flex is-align-items-center" style="gap: 0.75rem; margin-bottom: 0.5rem;">
                                                    <strong><?php echo htmlspecialchars($tenant['name']); ?></strong>
                                                    <span class="tag <?php echo $isActive ? 'is-success' : 'is-grey'; ?>">
                                                        <?php echo htmlspecialchars($tenant['status'] ?? 'active'); ?>
                                                    </span>
                                                </div>
                                                <p style="color: var(--color-text-secondary); font-size: 0.75rem; font-family: 'Monaco', 'Courier New', monospace; margin: 0;">
                                                    <?php echo htmlspecialchars($tenant['id']); ?>
                                                </p>
                                            </div>
                                            <div class="is-flex" style="gap: 0.5rem;">
                                                <?php if ($isActive): ?>
                                                    <a href="<?php echo \App\Core\UrlHelper::workspace('/dashboard', $tenant['id']); ?>" 
                                                       class="button is-small is-info is-light"
                                                       title="Open Workspace">
                                                        <span class="icon is-small">
                                                            <?php \App\Core\Icon::render('external-link', 14, 14); ?>
                                                        </span>
                                                        <span>Open</span>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?php echo '/admin/tenants/' . htmlspecialchars($tenant['id']) . '/edit'; ?>" 
                                                   class="button is-small is-info"
                                                   title="Edit Tenant">
                                                    <span class="icon is-small">
                                                        <?php \App\Core\Icon::render('edit', 14, 14); ?>
                                                    </span>
                                                </a>
                                                <?php if ($isActive): ?>
                                                    <form method="POST" action="/admin/tenants/deactivate" style="margin: 0; display: inline;">
                                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($tenant['id']); ?>">
                                                        <button type="submit" 
                                                                class="button is-small is-warning"
                                                                title="Deactivate Tenant">
                                                            <span class="icon is-small">
                                                                <?php \App\Core\Icon::render('pause', 14, 14); ?>
                                                            </span>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" action="/admin/tenants/activate" style="margin: 0; display: inline;">
                                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($tenant['id']); ?>">
                                                        <button type="submit" 
                                                                class="button is-small is-success"
                                                                title="Activate Tenant">
                                                            <span class="icon is-small">
                                                                <?php \App\Core\Icon::render('play', 14, 14); ?>
                                                            </span>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <a href="<?php echo '/admin/tenants/' . htmlspecialchars($tenant['id']) . '/delete'; ?>" 
                                                   class="button is-small is-danger"
                                                   title="Delete Tenant">
                                                    <span class="icon is-small">
                                                        <?php \App\Core\Icon::render('trash', 14, 14); ?>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="has-text-centered" style="color: var(--color-text-secondary);">
                                <p>No tenants created yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Create Tenant Form -->
            <div class="column is-one-third-tablet is-full-mobile">
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">Add New Tenant</p>
                    </header>
                    <div class="card-content">
                        <form method="POST" action="/admin/tenants">
                            <div class="field">
                                <label class="label">Business Name</label>
                                <div class="control">
                                    <input class="input" type="text" name="name" required placeholder="e.g. Innovate Inc.">
                                </div>
                            </div>
                            <div class="field">
                                <div class="control">
                                    <button type="submit" class="button is-primary" style="width: 100%;">
                                        <span class="icon is-small">
                                            <?php \App\Core\Icon::render('plus', 18, 18); ?>
                                        </span>
                                        <span>Create Tenant</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>

