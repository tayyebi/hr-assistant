<header>
    <div>
        <h2>Repositories</h2>
        <p>Manage Git repository access levels and view activity.</p>
    </div>
</header>

<?php if (!empty($message)): ?>
    <output data-type="success"><?php echo htmlspecialchars($message); ?></output>
<?php endif; ?>

<?php if (empty($gitInstances)): ?>
    <section data-empty style="padding: var(--spacing-xl); text-align: center;">
        <?php \App\Core\Icon::render('git-branch', 64, 64, 'stroke-width: 1; color: var(--text-muted);'); ?>
        <h3>No Git Providers Configured</h3>
        <p>Add a Git provider (GitLab, GitHub, Gitea) in Settings to manage repositories.</p>
        <a href="<?php echo \App\Core\UrlHelper::workspace('/settings'); ?>" data-button>
            Go to Settings
        </a>
    </section>
<?php else: ?>
    <section data-grid="3-1">
        <!-- Provider Instances List -->
        <article>
            <header>
                <h3>Git Providers</h3>
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
                        <?php foreach ($gitInstances as $instance): ?>
                            <tr <?php echo ($selectedInstance && $selectedInstance['id'] === $instance['id']) ? 'style="background: var(--bg-tertiary);"' : ''; ?>>
                                <td>
                                    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                                        <?php \App\Core\Icon::render('git-branch', 16, 16); ?>
                                        <?php echo htmlspecialchars(\App\Core\ProviderType::getName($instance['provider'])); ?>
                                    </div>
                                </td>
                                <td><strong><?php echo htmlspecialchars($instance['name']); ?></strong></td>
                                <td>
                                    <?php 
                                    $linkedCount = count($gitAccounts[$instance['id']]['employees'] ?? []);
                                    if ($linkedCount > 0): ?>
                                        <mark data-status="active"><?php echo $linkedCount; ?> linked</mark>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo \App\Core\UrlHelper::withQuery(\App\Core\UrlHelper::workspace('/repositories'), ['instance' => $instance['id']]); ?>" 
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

        <!-- Linked Employees Overview -->
        <article>
            <header>
                <h3>Linked Employees</h3>
            </header>
            
            <?php 
            $allLinkedEmployees = [];
            foreach ($gitAccounts as $instanceId => $data) {
                foreach ($data['employees'] as $empData) {
                    $allLinkedEmployees[$empData['employee']['id']] = $empData;
                }
            }
            ?>
            
            <?php if (empty($allLinkedEmployees)): ?>
                <p style="color: var(--text-muted); padding: var(--spacing-md);">No employees linked to Git accounts.</p>
            <?php else: ?>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach (array_slice($allLinkedEmployees, 0, 10) as $empData): ?>
                        <li style="display: flex; align-items: center; gap: var(--spacing-md); padding: var(--spacing-sm) var(--spacing-md); border-bottom: 1px solid var(--border-color);">
                            <figure data-avatar style="width: 32px; height: 32px; font-size: 0.75rem;">
                                <?php echo strtoupper(substr($empData['employee']['full_name'], 0, 1)); ?>
                            </figure>
                            <div style="flex: 1;">
                                <strong style="font-size: 0.875rem;"><?php echo htmlspecialchars($empData['employee']['full_name']); ?></strong>
                                <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted);">@<?php echo htmlspecialchars($empData['username']); ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>
    </section>

    <?php if ($selectedInstance): ?>
        <section style="margin-top: var(--spacing-xl);">
            <header style="margin-bottom: var(--spacing-lg);">
                <h3>
                    <?php \App\Core\Icon::render('git-branch', 24, 24, 'vertical-align: middle; margin-right: var(--spacing-sm);'); ?>
                    <?php echo htmlspecialchars($selectedInstance['name']); ?>
                </h3>
                <p style="color: var(--text-muted);">
                    <?php echo htmlspecialchars(\App\Core\ProviderType::getName($selectedInstance['provider'])); ?> instance
                </p>
            </header>

            <section data-grid="2">
                <!-- Repositories -->
                <article>
                    <header>
                        <h4>Repositories</h4>
                        <mark><?php echo count($repositories); ?> found</mark>
                    </header>
                    
                    <?php if (empty($repositories)): ?>
                        <p style="color: var(--text-muted); padding: var(--spacing-md);">
                            No repositories found or unable to connect to the provider.
                        </p>
                    <?php else: ?>
                        <div data-table style="max-height: 400px; overflow-y: auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Repository</th>
                                        <th>Visibility</th>
                                        <th>Last Activity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($repositories as $repo): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($repo['name'] ?? $repo['path'] ?? 'Unknown'); ?></strong>
                                                <?php if (!empty($repo['description'])): ?>
                                                    <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted);">
                                                        <?php echo htmlspecialchars(substr($repo['description'], 0, 60)); ?>
                                                    </p>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <mark data-status="<?php echo ($repo['visibility'] ?? 'private') === 'public' ? 'active' : 'pending'; ?>">
                                                    <?php echo htmlspecialchars($repo['visibility'] ?? 'private'); ?>
                                                </mark>
                                            </td>
                                            <td style="font-size: 0.75rem; color: var(--text-muted);">
                                                <?php echo htmlspecialchars($repo['last_activity'] ?? 'N/A'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </article>

                <!-- Recent Activity / Commits -->
                <article>
                    <header>
                        <h4>Recent Activity</h4>
                    </header>
                    
                    <?php if (empty($recentActivity)): ?>
                        <p style="color: var(--text-muted); padding: var(--spacing-md);">
                            No recent activity available.
                        </p>
                    <?php else: ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($recentActivity as $activity): ?>
                                <div style="padding: var(--spacing-sm); border-bottom: 1px solid var(--border-color);">
                                    <div style="display: flex; align-items: flex-start; gap: var(--spacing-sm);">
                                        <?php \App\Core\Icon::render('git-commit', 14, 14, 'margin-top: 3px;'); ?>
                                        <div>
                                            <strong style="font-size: 0.875rem;"><?php echo htmlspecialchars($activity['author'] ?? 'Unknown'); ?></strong>
                                            <p style="margin: 0; font-size: 0.8rem;">
                                                <?php echo htmlspecialchars($activity['message'] ?? ''); ?>
                                            </p>
                                            <small style="color: var(--text-muted);">
                                                <?php echo htmlspecialchars($activity['date'] ?? ''); ?> • 
                                                <?php echo htmlspecialchars($activity['repo'] ?? ''); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            </section>

            <!-- Access Management -->
            <article style="margin-top: var(--spacing-lg);">
                <header>
                    <h4>Access Management</h4>
                </header>
                
                <div data-table>
                    <table>
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Git Username</th>
                                <th>Access Level</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $instanceEmployees = $gitAccounts[$selectedInstance['id']]['employees'] ?? [];
                            if (empty($instanceEmployees)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: var(--spacing-lg);">
                                        No employees linked to this provider instance.
                                    </td>
                                </tr>
                            <?php else: ?>
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
                                            <code>@<?php echo htmlspecialchars($empData['username']); ?></code>
                                        </td>
                                        <td>
                                            <mark data-status="active">Developer</mark>
                                        </td>
                                        <td>
                                            <button data-variant="ghost" data-size="sm" onclick="alert('Access management coming soon')">
                                                <?php \App\Core\Icon::render('edit', 14, 14); ?>
                                                Edit Access
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    <?php endif; ?>
<?php endif; ?>
