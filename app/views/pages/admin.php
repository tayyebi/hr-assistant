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
            <a href="/logout" style="display: flex; align-items: center; gap: var(--spacing-sm); color: var(--color-danger);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
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
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                            <div>
                                <strong><?php echo htmlspecialchars($tenant['name']); ?></strong>
                                <p style="margin: 0; font-size: 0.75rem; font-family: monospace; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($tenant['id']); ?>
                                </p>
                            </div>
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
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Create Tenant
                    </button>
                </form>
            </article>
        </section>
    </main>
</body>
</html>
