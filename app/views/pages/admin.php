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
    <header>
        <div>
            <h1 style="color: var(--color-primary); margin: 0;">HR Assistant</h1>
            <p style="font-size: 0.875rem; margin: 0;">System Administration</p>
        </div>
        <div style="display: flex; align-items: center; gap: var(--spacing-md);">
            <span style="font-size: 0.875rem; color: var(--text-secondary);"><?php echo htmlspecialchars($user['email']); ?></span>
            <a href="<?php echo \App\Core\UrlHelper::url('/logout'); ?>" style="display: flex; align-items: center; gap: var(--spacing-sm); color: var(--color-danger);">
                <img src="/icons/logout.svg" alt="" width="16" height="16" style="filter: invert(33%) sepia(93%) saturate(2467%) hue-rotate(341deg) brightness(91%) contrast(91%);">
                Logout
            </a>
        </div>
    </header>

    <main>
        <h2>Tenant Management</h2>

        <?php if (!empty($message)): ?>
            <output data-type="success"><?php echo htmlspecialchars($message); ?></output>
        <?php endif; ?>

        <section data-grid="2-1">
            <!-- Tenant List -->
            <article>
                <h3>All Tenants</h3>
                <ul>
                    <?php foreach ($tenants as $tenant): ?>
                        <li>
                            <img src="/icons/home.svg" alt="" width="20" height="20" style="filter: invert(37%) sepia(93%) saturate(1352%) hue-rotate(200deg) brightness(97%) contrast(101%);">
                            <div style="flex: 1;">
                                <strong><?php echo htmlspecialchars($tenant['name']); ?></strong>
                                <p style="margin: 0; font-size: 0.75rem; font-family: monospace; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($tenant['id']); ?>
                                </p>
                            </div>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/dashboard', $tenant['id']); ?>" 
                               style="padding: 0.25rem 0.5rem; background: var(--accent-color); color: white; text-decoration: none; border-radius: 4px; font-size: 0.8rem;">
                                Open Workspace
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </article>

            <!-- Create Tenant Form -->
            <article>
                <h3>Add New Tenant</h3>
                <form method="POST" action="/admin/tenants">
                    <div>
                        <label>Business Name</label>
                        <input type="text" name="name" required placeholder="e.g. Innovate Inc.">
                    </div>
                    <button type="submit">
                        <img src="/icons/plus.svg" alt="" width="18" height="18">
                        Create Tenant
                    </button>
                </form>
            </article>
        </section>
    </main>
</body>
</html>
