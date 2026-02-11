<header>
    <div>
        <h2>Identity Management</h2>
        <p>Manage IAM provider access (Keycloak, Okta, Azure AD, etc.).</p>
    </div>
</header>

<?php if (!empty($message)): ?>
    <output data-type="success"><?php echo htmlspecialchars($message); ?></output>
<?php endif; ?>

<?php if (empty($iamInstances)): ?>
    <section data-empty style="padding: var(--spacing-xl); text-align: center;">
        <?php \App\Core\Icon::render('lock', 64, 64, 'stroke-width: 1; color: var(--text-muted);'); ?>
        <h3>No Identity Providers Configured</h3>
        <p>Add an identity provider (Keycloak, Okta, Azure AD) in Settings to manage user identities.</p>
        <a href="<?php echo \App\Core\UrlHelper::workspace('/settings'); ?>" data-button>
            Go to Settings
        </a>
    </section>
<?php else: ?>
    <section data-grid="3-1">
        <!-- Provider Instances List -->
        <article>
            <header>
                <h3>Identity Providers</h3>
            </header>
            
            <div data-table>
                <table>
                    <thead>
                        <tr>
                            <th>Provider</th>
                            <th>Instance Name</th>
                            <th>Linked Employees</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($iamInstances as $instance): ?>
                            <tr <?php echo ($selectedInstance && $selectedInstance['id'] === $instance['id']) ? 'style="background: var(--bg-tertiary);"' : ''; ?>>
                                <td>
                                    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                                        <?php \App\Core\Icon::render('lock', 16, 16); ?>
                                        <?php echo htmlspecialchars(\App\Core\ProviderType::getName($instance['provider'])); ?>
                                    </div>
                                </td>
                                <td><strong><?php echo htmlspecialchars($instance['name']); ?></strong></td>
                                <td>
                                    <?php 
                                    $linkedCount = count($iamAccounts[$instance['id']]['employees'] ?? []);
                                    if ($linkedCount > 0): ?>
                                        <mark data-status="active"><?php echo $linkedCount; ?> linked</mark>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo \App\Core\UrlHelper::withQuery(\App\Core\UrlHelper::workspace('/identity'), ['instance' => $instance['id']]); ?>" 
                                       data-button data-variant="ghost" data-size="sm">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>

        <!-- Quick Stats -->
        <article>
            <header>
                <h3>Overview</h3>
            </header>
            
            <section data-grid="1" style="gap: var(--spacing-md);">
                <div style="padding: var(--spacing-md); background: var(--bg-tertiary); border-radius: var(--radius-md);">
                    <h4 style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Total Providers</h4>
                    <p style="margin: 0; font-size: 1.5rem; font-weight: 700;"><?php echo count($iamInstances); ?></p>
                </div>
                <div style="padding: var(--spacing-md); background: var(--bg-tertiary); border-radius: var(--radius-md);">
                    <h4 style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Linked Identities</h4>
                    <p style="margin: 0; font-size: 1.5rem; font-weight: 700;">
                        <?php 
                        $totalLinked = 0;
                        foreach ($iamAccounts as $data) {
                            $totalLinked += count($data['employees']);
                        }
                        echo $totalLinked;
                        ?>
                    </p>
                </div>
                <div style="padding: var(--spacing-md); background: var(--bg-tertiary); border-radius: var(--radius-md);">
                    <h4 style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Total Employees</h4>
                    <p style="margin: 0; font-size: 1.5rem; font-weight: 700;"><?php echo count($employees); ?></p>
                </div>
            </section>
        </article>
    </section>

    <?php if ($selectedInstance): ?>
        <section style="margin-top: var(--spacing-xl);">
            <header style="margin-bottom: var(--spacing-lg); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3>
                        <?php \App\Core\Icon::render('lock', 24, 24, 'vertical-align: middle; margin-right: var(--spacing-sm);'); ?>
                        <?php echo htmlspecialchars($selectedInstance['name']); ?>
                    </h3>
                    <p style="color: var(--text-muted);">
                        <?php echo htmlspecialchars(\App\Core\ProviderType::getName($selectedInstance['provider'])); ?> instance
                    </p>
                </div>
                <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/identity/sync'); ?>">
                    <input type="hidden" name="instance_id" value="<?php echo htmlspecialchars($selectedInstance['id']); ?>">
                    <button type="submit" data-variant="secondary">
                        <?php \App\Core\Icon::render('refresh-cw', 16, 16); ?>
                        Sync Users
                    </button>
                </form>
            </header>

            <?php if ($syncStatus): ?>
                <article style="margin-bottom: var(--spacing-lg); background: var(--bg-tertiary); padding: var(--spacing-md); border-radius: var(--radius-md);">
                    <div style="display: flex; gap: var(--spacing-lg); flex-wrap: wrap;">
                        <div>
                            <small style="color: var(--text-muted);">Last Sync</small>
                            <p style="margin: 0;"><?php echo htmlspecialchars($syncStatus['last_sync'] ?? 'Never'); ?></p>
                        </div>
                        <div>
                            <small style="color: var(--text-muted);">Status</small>
                            <p style="margin: 0;">
                                <mark data-status="<?php echo ($syncStatus['status'] ?? 'unknown') === 'success' ? 'active' : 'pending'; ?>">
                                    <?php echo htmlspecialchars($syncStatus['status'] ?? 'Unknown'); ?>
                                </mark>
                            </p>
                        </div>
                        <div>
                            <small style="color: var(--text-muted);">Users Synced</small>
                            <p style="margin: 0;"><?php echo $syncStatus['users_count'] ?? 0; ?></p>
                        </div>
                    </div>
                </article>
            <?php endif; ?>

            <section data-grid="2">
                <!-- Linked Employees -->
                <article>
                    <header>
                        <h4>Linked Employees</h4>
                        <button onclick="document.getElementById('link-employee-dialog').showModal()" data-size="sm">
                            <?php \App\Core\Icon::render('plus', 14, 14); ?>
                            Link Employee
                        </button>
                    </header>
                    
                    <?php $instanceEmployees = $iamAccounts[$selectedInstance['id']]['employees'] ?? []; ?>
                    
                    <?php if (empty($instanceEmployees)): ?>
                        <p style="color: var(--text-muted); padding: var(--spacing-md);">
                            No employees linked to this identity provider.
                        </p>
                    <?php else: ?>
                        <div data-table>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>IAM Username</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($instanceEmployees as $empData): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                                                    <figure data-avatar style="width: 28px; height: 28px; font-size: 0.7rem;">
                                                        <?php echo strtoupper(substr($empData['employee']['full_name'], 0, 1)); ?>
                                                    </figure>
                                                    <?php echo htmlspecialchars($empData['employee']['full_name']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <code><?php echo htmlspecialchars($empData['username']); ?></code>
                                            </td>
                                            <td>
                                                <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/identity/unassign'); ?>" style="display: inline;">
                                                    <input type="hidden" name="instance_id" value="<?php echo htmlspecialchars($selectedInstance['id']); ?>">
                                                    <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($empData['employee']['id']); ?>">
                                                    <button type="submit" data-variant="ghost" data-size="sm" onclick="return confirm('Remove this employee from the identity provider?')">
                                                        <?php \App\Core\Icon::render('trash', 14, 14, 'color: var(--color-danger);'); ?>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </article>

                <!-- IAM Users from Provider -->
                <article>
                    <header>
                        <h4>Users in Provider</h4>
                        <mark><?php echo count($iamUsers); ?> users</mark>
                    </header>
                    
                    <?php if (empty($iamUsers)): ?>
                        <p style="color: var(--text-muted); padding: var(--spacing-md);">
                            No users found or unable to connect to the provider.
                        </p>
                    <?php else: ?>
                        <div style="max-height: 350px; overflow-y: auto;">
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <?php foreach (array_slice($iamUsers, 0, 20) as $iamUser): ?>
                                    <li style="padding: var(--spacing-sm) var(--spacing-md); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: var(--spacing-sm);">
                                        <?php \App\Core\Icon::render('user', 14, 14); ?>
                                        <div style="flex: 1;">
                                            <span style="font-size: 0.875rem;"><?php echo htmlspecialchars($iamUser['username'] ?? $iamUser['email'] ?? 'Unknown'); ?></span>
                                            <?php if (!empty($iamUser['full_name'])): ?>
                                                <small style="display: block; color: var(--text-muted);">
                                                    <?php echo htmlspecialchars($iamUser['full_name']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (isset($iamUser['enabled'])): ?>
                                            <mark data-status="<?php echo $iamUser['enabled'] ? 'active' : 'inactive'; ?>" style="font-size: 0.7rem;">
                                                <?php echo $iamUser['enabled'] ? 'Active' : 'Disabled'; ?>
                                            </mark>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </article>
            </section>

            <!-- Groups -->
            <?php if (!empty($iamGroups)): ?>
                <article style="margin-top: var(--spacing-lg);">
                    <header>
                        <h4>Groups / Roles</h4>
                    </header>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-sm);">
                        <?php foreach ($iamGroups as $group): ?>
                            <div style="padding: var(--spacing-sm) var(--spacing-md); background: var(--bg-tertiary); border-radius: var(--radius-md); display: flex; align-items: center; gap: var(--spacing-sm);">
                                <?php \App\Core\Icon::render('users', 14, 14); ?>
                                <span><?php echo htmlspecialchars($group['name'] ?? 'Unknown'); ?></span>
                                <?php if (isset($group['members_count'])): ?>
                                    <small style="color: var(--text-muted);">(<?php echo $group['members_count']; ?>)</small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endif; ?>
        </section>

        <!-- Link Employee Dialog -->
        <dialog id="link-employee-dialog">
            <article>
                <header>
                    <h3>Link Employee to <?php echo htmlspecialchars($selectedInstance['name']); ?></h3>
                    <button type="button" data-variant="ghost" data-size="icon" onclick="this.closest('dialog').close()">
                        <?php \App\Core\Icon::render('close', 24, 24); ?>
                    </button>
                </header>

                <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/identity/assign'); ?>">
                    <input type="hidden" name="instance_id" value="<?php echo htmlspecialchars($selectedInstance['id']); ?>">
                    
                    <section>
                        <div style="margin-bottom: var(--spacing-md);">
                            <label>Employee</label>
                            <select name="employee_id" required>
                                <option value="">Select employee...</option>
                                <?php foreach ($employees as $emp): ?>
                                    <?php 
                                    $alreadyLinked = false;
                                    foreach ($instanceEmployees as $linked) {
                                        if ($linked['employee']['id'] === $emp['id']) {
                                            $alreadyLinked = true;
                                            break;
                                        }
                                    }
                                    if ($alreadyLinked) continue;
                                    ?>
                                    <option value="<?php echo htmlspecialchars($emp['id']); ?>">
                                        <?php echo htmlspecialchars($emp['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label>Username in <?php echo htmlspecialchars(\App\Core\ProviderType::getName($selectedInstance['provider'])); ?></label>
                            <input type="text" name="username" placeholder="e.g. john.doe" required>
                        </div>
                    </section>

                    <footer>
                        <button type="button" data-variant="secondary" onclick="this.closest('dialog').close()">Cancel</button>
                        <button type="submit">Link Employee</button>
                    </footer>
                </form>
            </article>
        </dialog>
    <?php endif; ?>
<?php endif; ?>
