<div class="section">
    <div class="level">
        <div>
            <h2 class="title">Secrets Management</h2>
            <p class="subtitle">Manage password manager access (Passbolt, Bitwarden, 1Password, etc.).</p>
        </div>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="notification is-success">
        <a href="#" class="delete"></a>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if (empty($secretsInstances)): ?>
    <div class="section has-text-centered">
        <div class="block">
            <?php \App\Core\Icon::render('key', 64, 64, 'stroke-width: 1;'); ?>
        </div>
        <h3>No Secrets Providers Configured</h3>
        <p>Add a secrets provider (Passbolt, Bitwarden, 1Password, HashiCorp Vault) in Settings to manage password access.</p>
        <a href="<?php echo \App\Core\UrlHelper::workspace('/settings'); ?>" class="button is-primary">
            Go to Settings
        </a>
    </div>
<?php else: ?>
    <div class="columns is-multiline">
        <!-- Provider Instances List -->
        <div class="column is-three-quarters-desktop is-full-tablet">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">Secrets Providers</p>
                </header>
                <div class="table-container card-content">
                    <table class="table is-striped is-fullwidth">
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
                                <tr <?php echo ($selectedInstance && $selectedInstance['id'] === $instance['id']) ? 'class="is-selected"' : ''; ?>>
                                    <td>
                                        <div class="is-flex is-align-items-center gap-05">
                                            <span class="icon is-small">
                                                <?php \App\Core\Icon::render('key', 16, 16); ?>
                                            </span>
                                            <?php echo htmlspecialchars(\App\Core\ProviderType::getName($instance['provider'])); ?>
                                        </div>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($instance['name']); ?></strong></td>
                                    <td>
                                        <?php 
                                        $linkedCount = count($secretsAccounts[$instance['id']]['employees'] ?? []);
                                        if ($linkedCount > 0): ?>
                                            <span class="tag is-success"><?php echo $linkedCount; ?> linked</span>
                                        <?php else: ?>
                                            <span class="has-text-grey-light">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo \App\Core\UrlHelper::withQuery(\App\Core\UrlHelper::workspace('/secrets'), ['instance' => $instance['id']]); ?>" 
                                           class="button is-small is-ghost">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="column is-one-quarter-desktop is-full-tablet">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">Overview</p>
                </header>
                <div class="card-content">
                    <div class="flex-col-gap">
                        <div class="box has-background-grey-light">
                            <p class="heading is-6">Total Providers</p>
                            <p class="title is-4"><?php echo count($secretsInstances); ?></p>
                        </div>
                        <div class="box has-background-grey-light">
                            <p class="heading is-6">Linked Employees</p>
                            <p class="title is-4">
                                <?php 
                                $totalLinked = 0;
                                foreach ($secretsAccounts as $data) {
                                    $totalLinked += count($data['employees']);
                                }
                                echo $totalLinked;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($selectedInstance): ?>
        <section class="mt-2">
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <div>
                            <h3 class="title is-4">
                                <span class="icon is-small" class="align-middle">
                                    <?php \App\Core\Icon::render('key', 24, 24); ?>
                                </span>
                                <?php echo htmlspecialchars($selectedInstance['name']); ?>
                            </h3>
                            <p class="subtitle">
                                <?php echo htmlspecialchars(\App\Core\ProviderType::getName($selectedInstance['provider'])); ?> instance
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="columns is-multiline">
                <!-- Linked Employees -->
                <div class="column is-half-desktop">
                    <div class="card">
                        <header class="card-header">
                            <p class="card-header-title">Linked Employees</p>
                            <div class="card-header-icon">
                                <button class="button is-small is-primary">
                                    <span class="icon is-small">
                                        <?php \App\Core\Icon::render('plus', 14, 14); ?>
                                    </span>
                                    <span>Link Employee</span>
                                </button>
                            </div>
                        </header>
                        <div class="card-content">
                            <?php $instanceEmployees = $secretsAccounts[$selectedInstance['id']]['employees'] ?? []; ?>
                            
                            <?php if (empty($instanceEmployees)): ?>
                                <p class="has-text-grey-light">
                                    No employees linked to this secrets provider.
                                </p>
                            <?php else: ?>
                                <div class="table-container">
                                    <table class="table is-striped is-fullwidth">
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
                                                        <div class="is-flex is-align-items-center gap-05">
                                                            <div class="image is-28x28">
                                                                <div class="is-flex is-align-items-center is-justify-content-center has-background-info has-text-white" class="w-100-h-100-rounded">
                                                                    <?php echo strtoupper(substr($empData['employee']['full_name'], 0, 1)); ?>
                                                                </div>
                                                            </div>
                                                            <?php echo htmlspecialchars($empData['employee']['full_name']); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <code><?php echo htmlspecialchars($empData['username']); ?></code>
                                                    </td>
                                                    <td>
                                                        <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/secrets/unassign'); ?>" class="display-inline">
                                                            <input type="hidden" name="instance_id" value="<?php echo htmlspecialchars($selectedInstance['id']); ?>">
                                                            <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($empData['employee']['id']); ?>">
                                                            <button type="submit" class="button is-small is-danger is-ghost">
                                                                <span class="icon is-small">
                                                                    <?php \App\Core\Icon::render('trash', 14, 14); ?>
                                                                </span>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Groups / Vaults -->
                <div class="column is-half-desktop">
                    <div class="card">
                        <header class="card-header">
                            <p class="card-header-title">Groups / Vaults</p>
                        </header>
                        <div class="card-content">
                            <?php if (empty($groups)): ?>
                                <p class="has-text-grey-light">
                                    No groups/vaults available or unable to connect to the provider.
                                </p>
                            <?php else: ?>
                                <ul class="list-none p-0 m-0">
                                    <?php foreach ($groups as $group): ?>
                                        <li class="py-05 border-bottom-light display-flex items-center gap-05">
                                            <span class="icon is-small">
                                                <?php \App\Core\Icon::render('folder', 16, 16); ?>
                                            </span>
                                            <span><?php echo htmlspecialchars($group['name'] ?? 'Unknown'); ?></span>
                                            <?php if (isset($group['members_count'])): ?>
                                                <span class="tag is-light" class="ml-auto"><?php echo $group['members_count']; ?> members</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Link Employee Dialog -->
        <div class="modal" id="link-employee-dialog">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Link Employee to <?php echo htmlspecialchars($selectedInstance['name']); ?></p>
                    <button class="delete" aria-label="close"></button>
                </header>

                <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/secrets/assign'); ?>">
                    <section class="modal-card-body">
                        <input type="hidden" name="instance_id" value="<?php echo htmlspecialchars($selectedInstance['id']); ?>">
                        
                        <div class="field">
                            <label class="label">Employee</label>
                            <div class="control">
                                <span class="select is-fullwidth">
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
                                </span>
                            </div>
                        </div>
                        
                        <div class="field">
                            <label class="label">Username / Email in <?php echo htmlspecialchars(\App\Core\ProviderType::getName($selectedInstance['provider'])); ?></label>
                            <div class="control">
                                <input class="input" type="text" name="username" placeholder="e.g. john.doe or john@company.com" required>
                            </div>
                        </div>
                    </section>

                    <footer class="modal-card-foot">
                        <button type="button" class="button">Cancel</button>
                        <button type="submit" class="button is-primary">Link Employee</button>
                    </footer>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
