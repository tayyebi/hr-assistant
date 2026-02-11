<header>
    <div>
        <h2>Secrets Management</h2>
        <p>Manage password manager access (Passbolt, Bitwarden, 1Password, etc.).</p>
    </div>
</header>

<?php if (!empty($message)): ?>
    <output data-type="success"><?php echo htmlspecialchars($message); ?></output>
<?php endif; ?>

<?php if (empty($secretsInstances)): ?>
    <section data-empty style="padding: var(--spacing-xl); text-align: center;">
        <?php \App\Core\Icon::render('key', 64, 64, 'stroke-width: 1; color: var(--text-muted);'); ?>
        <h3>No Secrets Providers Configured</h3>
        <p>Add a secrets provider (Passbolt, Bitwarden, 1Password, HashiCorp Vault) in Settings to manage password access.</p>
        <a href="<?php echo \App\Core\UrlHelper::workspace('/settings'); ?>" data-button>
            Go to Settings
        </a>
    </section>
<?php else: ?>
    <section data-grid="3-1">
        <!-- Provider Instances List -->
        <article>
            <header>
                <h3>Secrets Providers</h3>
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
                        <?php foreach ($secretsInstances as $instance): ?>
                            <tr <?php echo ($selectedInstance && $selectedInstance['id'] === $instance['id']) ? 'style="background: var(--bg-tertiary);"' : ''; ?>>
                                <td>
                                    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                                        <?php \App\Core\Icon::render('key', 16, 16); ?>
                                        <?php echo htmlspecialchars(\App\Core\ProviderType::getName($instance['provider'])); ?>
                                    </div>
                                </td>
                                <td><strong><?php echo htmlspecialchars($instance['name']); ?></strong></td>
                                <td>
                                    <?php 
                                    $linkedCount = count($secretsAccounts[$instance['id']]['employees'] ?? []);
                                    if ($linkedCount > 0): ?>
                                        <mark data-status="active"><?php echo $linkedCount; ?> linked</mark>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo \App\Core\UrlHelper::withQuery(\App\Core\UrlHelper::workspace('/secrets'), ['instance' => $instance['id']]); ?>" 
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
                    <p style="margin: 0; font-size: 1.5rem; font-weight: 700;"><?php echo count($secretsInstances); ?></p>
                </div>
                <div style="padding: var(--spacing-md); background: var(--bg-tertiary); border-radius: var(--radius-md);">
                    <h4 style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Linked Employees</h4>
                    <p style="margin: 0; font-size: 1.5rem; font-weight: 700;">
                        <?php 
                        $totalLinked = 0;
                        foreach ($secretsAccounts as $data) {
                            $totalLinked += count($data['employees']);
                        }
                        echo $totalLinked;
                        ?>
                    </p>
                </div>
            </section>
        </article>
    </section>

    <?php if ($selectedInstance): ?>
        <section style="margin-top: var(--spacing-xl);">
            <header style="margin-bottom: var(--spacing-lg);">
                <h3>
                    <?php \App\Core\Icon::render('key', 24, 24, 'vertical-align: middle; margin-right: var(--spacing-sm);'); ?>
                    <?php echo htmlspecialchars($selectedInstance['name']); ?>
                </h3>
                <p style="color: var(--text-muted);">
                    <?php echo htmlspecialchars(\App\Core\ProviderType::getName($selectedInstance['provider'])); ?> instance
                </p>
            </header>

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
                    
                    <?php $instanceEmployees = $secretsAccounts[$selectedInstance['id']]['employees'] ?? []; ?>
                    
                    <?php if (empty($instanceEmployees)): ?>
                        <p style="color: var(--text-muted); padding: var(--spacing-md);">
                            No employees linked to this secrets provider.
                        </p>
                    <?php else: ?>
                        <div data-table>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Username</th>
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
                                                <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/secrets/unassign'); ?>" style="display: inline;">
                                                    <input type="hidden" name="instance_id" value="<?php echo htmlspecialchars($selectedInstance['id']); ?>">
                                                    <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($empData['employee']['id']); ?>">
                                                    <button type="submit" data-variant="ghost" data-size="sm" onclick="return confirm('Remove this employee from the secrets provider?')">
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

                <!-- Groups / Vaults -->
                <article>
                    <header>
                        <h4>Groups / Vaults</h4>
                    </header>
                    
                    <?php if (empty($groups)): ?>
                        <p style="color: var(--text-muted); padding: var(--spacing-md);">
                            No groups/vaults available or unable to connect to the provider.
                        </p>
                    <?php else: ?>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php foreach ($groups as $group): ?>
                                <li style="padding: var(--spacing-sm) var(--spacing-md); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: var(--spacing-sm);">
                                    <?php \App\Core\Icon::render('folder', 16, 16); ?>
                                    <span><?php echo htmlspecialchars($group['name'] ?? 'Unknown'); ?></span>
                                    <?php if (isset($group['members_count'])): ?>
                                        <mark style="margin-left: auto;"><?php echo $group['members_count']; ?> members</mark>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </article>
            </section>
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

                <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/secrets/assign'); ?>">
                    <input type="hidden" name="instance_id" value="<?php echo htmlspecialchars($selectedInstance['id']); ?>">
                    
                    <section>
                        <div style="margin-bottom: var(--spacing-md);">
                            <label>Employee</label>
                            <select name="employee_id" required>
                                <option value="">Select employee...</option>
                                <?php foreach ($employees as $emp): ?>
                                    <?php 
                                    // Skip already linked employees
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
                            <label>Username / Email in <?php echo htmlspecialchars(\App\Core\ProviderType::getName($selectedInstance['provider'])); ?></label>
                            <input type="text" name="username" placeholder="e.g. john.doe or john@company.com" required>
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
