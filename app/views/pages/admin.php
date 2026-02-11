<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>HR Assistant - System Administration</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body class="admin-page">
    <nav class="admin-navbar" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <strong>HR Assistant</strong>
            <span style="color: var(--color-text-secondary); margin-left: 0.5rem;">Admin</span>
        </div>
        <div class="admin-navbar-end">
            <span class="admin-navbar-item"><?php echo htmlspecialchars($user['email']); ?></span>
            <a href="<?php echo \App\Core\UrlHelper::url('/logout'); ?>" class="admin-navbar-item">
                <span class="icon">
                    <?php \App\Core\Icon::render('logout', 16, 16); ?>
                </span>
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <main class="admin-main">
        <div class="admin-container">
            <h2 class="admin-title">Tenant Management</h2>

            <?php if (!empty($message)): ?>
                <div class="admin-notification <?php echo ($messageType ?? 'success') === 'error' ? 'is-danger' : 'is-success'; ?>">
                    <span><?php echo htmlspecialchars($message); ?></span>
                    <button class="delete" aria-label="Close notification"></button>
                </div>
            <?php endif; ?>

            <div class="admin-grid">
                <!-- Tenant List -->
                <div>
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3>All Tenants (<?php echo count($tenants); ?>)</h3>
                        </div>
                        <div class="admin-card-body">
                            <?php if (!empty($tenants)): ?>
                                <?php foreach ($tenants as $tenant): 
                                    $isActive = ($tenant['status'] ?? 'active') === 'active';
                                ?>
                                    <div class="tenant-item <?php echo !$isActive ? 'inactive' : ''; ?>">
                                        <div class="tenant-item-header">
                                            <h4 class="tenant-item-name"><?php echo htmlspecialchars($tenant['name']); ?></h4>
                                            <span class="tenant-item-status <?php echo $isActive ? 'active' : 'inactive'; ?>">
                                                <?php echo htmlspecialchars($tenant['status'] ?? 'active'); ?>
                                            </span>
                                        </div>
                                        <p class="tenant-item-id"><?php echo htmlspecialchars($tenant['id']); ?></p>
                                        
                                        <div class="tenant-item-actions">
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
                                                <span>Edit</span>
                                            </a>
                                            <?php if ($isActive): ?>
                                                <form method="POST" action="/admin/tenants/deactivate" class="tenant-form">
                                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($tenant['id']); ?>">
                                                    <button type="submit" 
                                                            class="button is-small is-warning"
                                                            title="Deactivate Tenant">
                                                        <span class="icon is-small">
                                                            <?php \App\Core\Icon::render('pause', 14, 14); ?>
                                                        </span>
                                                        <span>Pause</span>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" action="/admin/tenants/activate" class="tenant-form">
                                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($tenant['id']); ?>">
                                                    <button type="submit" 
                                                            class="button is-small is-success"
                                                            title="Activate Tenant">
                                                        <span class="icon is-small">
                                                            <?php \App\Core\Icon::render('play', 14, 14); ?>
                                                        </span>
                                                        <span>Activate</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <a href="<?php echo '/admin/tenants/' . htmlspecialchars($tenant['id']) . '/delete'; ?>" 
                                               class="button is-small is-danger"
                                               title="Delete Tenant">
                                                <span class="icon is-small">
                                                    <?php \App\Core\Icon::render('trash', 14, 14); ?>
                                                </span>
                                                <span>Delete</span>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">📋</div>
                                    <p><strong>No tenants created yet.</strong></p>
                                    <p>Create your first tenant using the form on the right.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Create Tenant Form -->
                <div>
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3>Add New Tenant</h3>
                        </div>
                        <div class="admin-card-body">
                            <form method="POST" action="/admin/tenants">
                                <div class="form-field">
                                    <label for="tenant-name">Business Name</label>
                                    <input 
                                        id="tenant-name" 
                                        type="text" 
                                        name="name" 
                                        required 
                                        placeholder="e.g. Innovate Inc."
                                        aria-required="true">
                                </div>
                                <button type="submit" class="form-submit">
                                    <span class="icon is-small">
                                        <?php \App\Core\Icon::render('plus', 18, 18); ?>
                                    </span>
                                    <span>Create Tenant</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>

