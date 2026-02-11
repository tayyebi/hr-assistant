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
            <output data-type="<?php echo htmlspecialchars($messageType ?? 'success'); ?>"><?php echo htmlspecialchars($message); ?></output>
        <?php endif; ?>

        <section data-grid="2-1">
            <!-- Tenant List -->
            <article>
                <h3>All Tenants (<?php echo count($tenants); ?>)</h3>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($tenants as $tenant): 
                        $isActive = ($tenant['status'] ?? 'active') === 'active';
                        $statusColor = $isActive ? 'var(--color-success)' : 'var(--text-muted)';
                    ?>
                        <li style="display: flex; align-items: center; gap: var(--spacing-md); padding: var(--spacing-md); border: 1px solid var(--border-color); border-radius: var(--radius); margin-bottom: var(--spacing-sm); <?php echo !$isActive ? 'opacity: 0.6;' : ''; ?>">
                            <img src="/icons/home.svg" alt="" width="20" height="20" style="filter: <?php echo $isActive ? 'invert(37%) sepia(93%) saturate(1352%) hue-rotate(200deg) brightness(97%) contrast(101%)' : 'invert(50%)'; ?>;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                                    <strong><?php echo htmlspecialchars($tenant['name']); ?></strong>
                                    <span style="font-size: 0.7rem; padding: 0.15rem 0.4rem; background: <?php echo $statusColor; ?>; color: white; border-radius: 3px; text-transform: uppercase;">
                                        <?php echo htmlspecialchars($tenant['status'] ?? 'active'); ?>
                                    </span>
                                </div>
                                <p style="margin: 0; font-size: 0.75rem; font-family: monospace; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($tenant['id']); ?>
                                </p>
                            </div>
                            <div style="display: flex; gap: var(--spacing-sm);">
                                <?php if ($isActive): ?>
                                    <a href="<?php echo \App\Core\UrlHelper::workspace('/dashboard', $tenant['id']); ?>" 
                                       title="Open Workspace"
                                       style="padding: 0.4rem 0.6rem; background: var(--accent-color); color: white; text-decoration: none; border-radius: 4px; font-size: 0.8rem; display: flex; align-items: center; gap: 0.3rem;">
                                        <img src="/icons/external-link.svg" alt="" width="14" height="14" style="filter: invert(100%);">
                                        Open
                                    </a>
                                <?php endif; ?>
                                <button type="button" 
                                        onclick="openEditModal('<?php echo htmlspecialchars($tenant['id']); ?>', '<?php echo htmlspecialchars($tenant['name']); ?>')"
                                        title="Edit Tenant"
                                        style="padding: 0.4rem 0.6rem; background: var(--color-info); color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">
                                    <img src="/icons/edit.svg" alt="" width="14" height="14" style="filter: invert(100%);">
                                </button>
                                <?php if ($isActive): ?>
                                    <form method="POST" action="/admin/tenants/deactivate" style="margin: 0;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($tenant['id']); ?>">
                                        <button type="submit" 
                                                title="Deactivate Tenant"
                                                onclick="return confirm('Deactivate this tenant? Users will not be able to access it.')"
                                                style="padding: 0.4rem; background: var(--color-warning); color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                            <img src="/icons/pause.svg" alt="" width="14" height="14" style="filter: invert(100%);">
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="/admin/tenants/activate" style="margin: 0;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($tenant['id']); ?>">
                                        <button type="submit" 
                                                title="Activate Tenant"
                                                style="padding: 0.4rem; background: var(--color-success); color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                            <img src="/icons/play.svg" alt="" width="14" height="14" style="filter: invert(100%);">
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <button type="button" 
                                        onclick="openDeleteModal('<?php echo htmlspecialchars($tenant['id']); ?>', '<?php echo htmlspecialchars($tenant['name']); ?>')"
                                        title="Delete Tenant"
                                        style="padding: 0.4rem; background: var(--color-danger); color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                    <img src="/icons/trash.svg" alt="" width="14" height="14" style="filter: invert(100%);">
                                </button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($tenants)): ?>
                        <li style="text-align: center; padding: var(--spacing-lg); color: var(--text-muted);">
                            No tenants created yet.
                        </li>
                    <?php endif; ?>
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

        <!-- Edit Modal -->
        <dialog id="editModal" style="border: 1px solid var(--border-color); border-radius: var(--radius); padding: 0; max-width: 500px;">
            <form method="POST" action="/admin/tenants/edit" style="padding: var(--spacing-lg);">
                <h3 style="margin-top: 0;">Edit Tenant</h3>
                <input type="hidden" name="id" id="editTenantId">
                <div style="margin-bottom: var(--spacing-md);">
                    <label>Business Name</label>
                    <input type="text" name="name" id="editTenantName" required placeholder="e.g. Innovate Inc.">
                </div>
                <div style="display: flex; gap: var(--spacing-sm); justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('editModal').close()" 
                            style="background: var(--bg-secondary); color: var(--text-primary);">
                        Cancel
                    </button>
                    <button type="submit" style="background: var(--color-info);">
                        <img src="/icons/save.svg" alt="" width="18" height="18">
                        Update Tenant
                    </button>
                </div>
            </form>
        </dialog>

        <!-- Delete Modal -->
        <dialog id="deleteModal" style="border: 1px solid var(--border-color); border-radius: var(--radius); padding: 0; max-width: 500px;">
            <form method="POST" action="/admin/tenants/delete" style="padding: var(--spacing-lg);">
                <h3 style="margin-top: 0; color: var(--color-danger);">⚠️ Delete Tenant</h3>
                <input type="hidden" name="id" id="deleteTenantId">
                <p style="margin-bottom: var(--spacing-md);">
                    You are about to permanently delete <strong id="deleteTenantName"></strong> and all associated data:
                </p>
                <ul style="margin-bottom: var(--spacing-md); color: var(--text-secondary);">
                    <li>All employees</li>
                    <li>All teams</li>
                    <li>All messages</li>
                    <li>All jobs</li>
                    <li>All assets</li>
                    <li>All configuration</li>
                </ul>
                <p style="margin-bottom: var(--spacing-md); color: var(--color-danger); font-weight: bold;">
                    This action cannot be undone!
                </p>
                <div style="margin-bottom: var(--spacing-md);">
                    <label>Type <strong>DELETE</strong> to confirm</label>
                    <input type="text" name="confirm" required placeholder="DELETE" autocomplete="off">
                </div>
                <div style="display: flex; gap: var(--spacing-sm); justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('deleteModal').close()" 
                            style="background: var(--bg-secondary); color: var(--text-primary);">
                        Cancel
                    </button>
                    <button type="submit" style="background: var(--color-danger);">
                        <img src="/icons/trash.svg" alt="" width="18" height="18">
                        Delete Forever
                    </button>
                </div>
            </form>
        </dialog>
    </main>

    <script>
        function openEditModal(id, name) {
            document.getElementById('editTenantId').value = id;
            document.getElementById('editTenantName').value = name;
            document.getElementById('editModal').showModal();
        }

        function openDeleteModal(id, name) {
            document.getElementById('deleteTenantId').value = id;
            document.getElementById('deleteTenantName').textContent = name;
            document.getElementById('deleteModal').showModal();
        }

        // Close modals on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('editModal').close();
                document.getElementById('deleteModal').close();
            }
        });
    </script>
</body>
</html>
