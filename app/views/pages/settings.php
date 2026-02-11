<div class="section">
    <div class="level">
        <div>
            <h2 class="title">Workspace Settings</h2>
            <p class="subtitle">Manage integrations, communication channels, and system preferences.</p>
        </div>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="section">
        <div class="notification is-success">
            <a href="#" class="delete"></a>
            <?php echo htmlspecialchars($message); ?>
        </div>
    </div>
<?php endif; ?>

<!-- Quick Status Overview -->
<div class="section">
    <div class="columns is-multiline">
        <?php 
            $providerInstances = \App\Models\ProviderInstance::getAll($tenant['id']);
            $stats = [
                ['label' => 'Connected Services', 'value' => count($providerInstances), 'icon' => 'zap'],
                ['label' => 'Identity Providers', 'value' => count(array_filter($providerInstances, fn($p) => $p['type'] === 'iam')), 'icon' => 'lock'],
                ['label' => 'Repository Access', 'value' => count(array_filter($providerInstances, fn($p) => $p['type'] === 'git')), 'icon' => 'git-branch'],
                ['label' => 'Communication', 'value' => count(array_filter($providerInstances, fn($p) => in_array($p['type'], ['email', 'messenger']))), 'icon' => 'mail'],
            ];
            foreach ($stats as $stat):
        ?>
        <div class="column is-one-quarter-desktop is-one-third-tablet is-full-mobile">
            <div class="box has-background-grey-light">
                <div class="is-flex is-align-items-center" class="gap-1 mb-05">
                    <div class="is-flex is-align-items-center is-justify-content-center has-background-info has-text-white" class="w-40 h-40 rounded-sm">
                        <?php \App\Core\Icon::render($stat['icon'], 20, 20); ?>
                    </div>
                    <div>
                        <p class="is-size-7 has-text-grey-dark" class="m-0"><?php echo $stat['label']; ?></p>
                        <p class="title is-4" class="m-0 lh-tight"><?php echo $stat['value']; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Provider Management Section -->
<div class="section">
    <div class="mb-5">
        <h3 class="title is-4">🔌 Connected Services</h3>
        <p class="subtitle is-6">Manage integrations for identity, repositories, calendars, and messaging.</p>
    </div>

    <div class="columns is-multiline">
        <?php
            $services = [
                [
                    'title' => 'Identity Management',
                    'desc' => 'SSO, LDAP, SAML',
                    'icon' => 'lock',
                    'color' => 'has-background-info-light',
                    'iconColor' => '#0369a1',
                    'type' => 'iam',
                    'url' => '/identity/',
                ],
                [
                    'title' => 'Code Repositories',
                    'desc' => 'Git hosting, access control',
                    'icon' => 'git-branch',
                    'color' => 'has-background-danger-light',
                    'iconColor' => '#be185d',
                    'type' => 'git',
                    'url' => '/repositories/',
                ],
                [
                    'title' => 'Calendar Services',
                    'desc' => 'Google, Outlook, CalDAV',
                    'icon' => 'calendar',
                    'color' => 'has-background-success-light',
                    'iconColor' => '#15803d',
                    'type' => 'calendar',
                    'url' => '/calendars/',
                ],
                [
                    'title' => 'Password Management',
                    'desc' => 'Vault, Bitwarden, 1Password',
                    'icon' => 'key',
                    'color' => 'has-background-warning-light',
                    'iconColor' => '#7c3aed',
                    'type' => 'secrets',
                    'url' => '/secrets/',
                ],
                [
                    'title' => 'Messaging & Email',
                    'desc' => 'SMTP, Telegram, Slack',
                    'icon' => 'mail',
                    'color' => 'has-background-primary-light',
                    'iconColor' => '#ca8a04',
                    'type' => ['email', 'messenger'],
                    'url' => '/messages/',
                ],
            ];
            foreach ($services as $service):
                $typeFilter = is_array($service['type']) ? $service['type'] : [$service['type']];
                $instances = array_filter($providerInstances, fn($p) => in_array($p['type'], $typeFilter));
                $count = count($instances);
        ?>
        <div class="column is-one-third-desktop is-half-tablet is-full-mobile">
            <div class="card">
                <div class="card-content">
                    <div class="is-flex" class="gap-1 mb-1-5">
                        <div class="is-flex is-align-items-center is-justify-content-center <?php echo $service['color']; ?>" class="w-50 h-50 rounded-sm flex-shrink-0 min-w-50">
                            <?php \App\Core\Icon::render($service['icon'], 28, 28); ?>
                        </div>
                        <div class="flex-1">
                            <h4 class="title is-5" class="mb-025"><?php echo $service['title']; ?></h4>
                            <p class="is-size-7 has-text-grey-dark" class="m-0"><?php echo $service['desc']; ?></p>
                        </div>
                    </div>
                    
                    <div class="box has-background-grey-light" class="mb-1">
                        <p class="m-0 is-size-7">
                            <strong><?php echo $count; ?></strong>
                            <?php echo $count === 1 ? 'instance' : 'instances'; ?> configured
                        </p>
                    </div>

                    <?php if (!empty($instances)): ?>
                        <div class="box has-background-grey-light" class="mb-1 is-size-7">
                            <?php foreach (array_slice($instances, 0, 2) as $inst): ?>
                                <div class="is-flex is-align-items-center" class="gap-05 py-025">
                                    <span class="indicator-dot active"></span>
                                    <span><?php echo htmlspecialchars($inst['name']); ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($instances) > 2): ?>
                                <div class="has-text-grey-dark" class="py-025">+<?php echo count($instances) - 2; ?> more</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="buttons">
                        <a href="#setupModal" class="button is-success is-fullwidth">
                            Add New
                        </a>
                        <a href="<?php echo \App\Core\UrlHelper::workspace($service['url']); ?>" class="button is-info is-fullwidth">
                            <span class="icon is-small">
                                <?php \App\Core\Icon::render('arrow-right', 18, 18); ?>
                            </span>
                            <span>Manage</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Setup Modal -->
<div class="modal" id="setupModal">
    <div class="modal-background"></div>
    <div class="modal-card" class="max-w-500">
        <header class="modal-card-head">
            <p class="modal-card-title">Add Provider Instance</p>
            <a href="#" class="delete"></a>
        </header>
        
        <form id="setupForm" class="display-none">
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Instance Name *</label>
                    <div class="control">
                        <input type="text" class="input" name="name" id="instanceName" placeholder="e.g., Main GitLab Server" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Provider Type *</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="providerType" id="providerType" required>
                                <option value="">Select Provider Type</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Provider *</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="provider" id="provider" required>
                                <option value="">Select a provider</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="configFields"></div>
            </section>

            <footer class="modal-card-foot">
                <div class="field is-grouped">
                    <div class="control">
                        <button type="button" id="testBtn" class="button is-warning" class="display-none">Test Connection</button>
                    </div>
                    <div class="control">
                        <button type="submit" class="button is-info">Add Provider</button>
                    </div>
                    <div class="control">
                        <a href="#" class="button is-light">Cancel</a>
                    </div>
                </div>

                <div id="formMessage" class="notification" class="display-none mt-1 w-full"></div>
            </footer>
        </form>
    </div>
</div>

<!-- Communication Channels -->
<div class="section">
    <div class="mb-4">
        <h3 class="title is-4">💬 Communication Channels</h3>
        <p class="subtitle is-6">Control which channels are available for team communication.</p>
    </div>

    <?php if (!empty($messagingChannels)): ?>
        <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/settings/'); ?>">
            <div class="box has-background-grey-light">
                <div class="columns is-multiline">
                    <?php foreach ($messagingChannels as $key => $ch): ?>
                        <div class="column is-one-third-desktop is-half-tablet is-full-mobile">
                            <label class="provider-checkbox-label <?php echo $ch['hasProvider'] ? '' : 'disabled'; ?>">
                                <div class="mt-025">
                                    <input type="checkbox" name="messaging_<?php echo htmlspecialchars($key); ?>_enabled" 
                                           <?php echo $ch['enabled'] ? 'checked' : ''; ?>
                                           <?php echo !$ch['hasProvider'] ? 'disabled' : ''; ?>>
                                </div>
                                <div>
                                    <div class="label-text">
                                        <?php echo htmlspecialchars($ch['name']); ?>
                                    </div>
                                    <?php if (!$ch['hasProvider']): ?>
                                        <small class="has-text-grey-dark">Provider not configured yet</small>
                                    <?php else: ?>
                                        <small class="has-text-success">✓ Provider connected</small>
                                    <?php endif; ?>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="field is-grouped mt-4">
                <div class="control">
                    <button type="submit" class="button is-info">
                        Save Communication Preferences
                    </button>
                </div>
                <div class="control is-expanded">
                    <small class="has-text-grey-dark">Changes take effect immediately</small>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<!-- Help & Support -->
<div class="section">
    <div class="box has-background-grey-light">
        <h3 class="title is-5">Need Help?</h3>
        <p class="has-text-grey-dark mb-3">
            Each service can be configured in its respective module. Start by clicking "Add New" on any service card above to quickly add your first provider instance.
        </p>
        <div class="columns is-multiline">
            <div class="column is-one-quarter-desktop is-half-tablet is-full-mobile">
                <div>
                    <strong>Identity (IAM)</strong>
                    <p class="is-size-7 has-text-grey-dark mt-2" class="mb-0">Set up SSO and centralized user management</p>
                </div>
            </div>
            <div class="column is-one-quarter-desktop is-half-tablet is-full-mobile">
                <div>
                    <strong>Repositories</strong>
                    <p class="is-size-7 has-text-grey-dark mt-2" class="mb-0">Link GitHub, GitLab, or other Git platforms</p>
                </div>
            </div>
            <div class="column is-one-quarter-desktop is-half-tablet is-full-mobile">
                <div>
                    <strong>Calendars</strong>
                    <p class="is-size-7 has-text-grey-dark mt-2" class="mb-0">Connect Google Calendar, Outlook, or CalDAV</p>
                </div>
            </div>
            <div class="column is-one-quarter-desktop is-half-tablet is-full-mobile">
                <div>
                    <strong>Secrets Management</strong>
                    <p class="is-size-7 has-text-grey-dark mt-2" class="mb-0">Integrate password vaults and secret stores</p>
                </div>
            </div>
        </div>
    </div>
</div>

