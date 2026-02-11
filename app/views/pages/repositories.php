<div class="section">
    <div class="level">
        <div>
            <h2 class="title">Repositories</h2>
            <p class="subtitle">Manage Git repository access levels and view activity.</p>
        </div>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="notification is-success">
        <a href="#" class="delete"></a>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if (empty($gitInstances)): ?>
    <div class="section has-text-centered">
        <div class="block">
            <?php \App\Core\Icon::render('git-branch', 64, 64, 'stroke-width: 1;'); ?>
        </div>
        <h3>No Git Providers Configured</h3>
        <p>Add a Git provider (GitLab, GitHub, Gitea) in Settings to manage repositories.</p>
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
                    <p class="card-header-title">Git Providers</p>
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
                            <?php foreach ($gitInstances as $instance): ?>
                                <tr <?php echo ($selectedInstance && $selectedInstance['id'] === $instance['id']) ? 'class="is-selected"' : ''; ?>>
                                    <td>
                                        <div class="gap-05">
                                            <span class="icon is-small">
                                                <?php \App\Core\Icon::render('git-branch', 16, 16); ?>
                                            </span>
                                            <?php echo htmlspecialchars(\App\Core\ProviderType::getName($instance['provider'])); ?>
                                        </div>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($instance['name']); ?></strong></td>
                                    <td>
                                        <?php 
                                        $linkedCount = count($gitAccounts[$instance['id']]['employees'] ?? []);
                                        if ($linkedCount > 0): ?>
                                            <span class="tag is-success"><?php echo $linkedCount; ?> linked</span>
                                        <?php else: ?>
                                            <span class="has-text-grey-light">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo \App\Core\UrlHelper::withQuery(\App\Core\UrlHelper::workspace('/repositories'), ['instance' => $instance['id']]); ?>" 
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

        <!-- Linked Employees Overview -->
        <div class="column is-one-quarter-desktop is-full-tablet">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">Linked Employees</p>
                </header>
                <div class="card-content">
                    <?php 
                    $allLinkedEmployees = [];
                    foreach ($gitAccounts as $instanceId => $data) {
                        foreach ($data['employees'] as $empData) {
                            $allLinkedEmployees[$empData['employee']['id']] = $empData;
                        }
                    }
                    ?>
                    
                    <?php if (empty($allLinkedEmployees)): ?>
                        <p class="has-text-grey-light">No employees linked to Git accounts.</p>
                    <?php else: ?>
                        <ul class="list-none p-0 m-0">
                            <?php foreach (array_slice($allLinkedEmployees, 0, 10) as $empData): ?>
                                <li class="display-flex items-center gap-075 py-05 border-bottom-light">
                                    <div class="image is-32x32">
                                        <div class="w-100-h-100-rounded is-size-7">
                                            <?php echo strtoupper(substr($empData['employee']['full_name'], 0, 1)); ?>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <strong class="is-size-7"><?php echo htmlspecialchars($empData['employee']['full_name']); ?></strong>
                                        <p class="m-0 is-size-7 has-text-grey-light">@<?php echo htmlspecialchars($empData['username']); ?></p>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
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
                                <span class="align-middle">
                                    <?php \App\Core\Icon::render('git-branch', 24, 24); ?>
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
                <!-- Repositories -->
                <div class="column is-half-desktop">
                    <div class="card">
                        <header class="card-header">
                            <p class="card-header-title">Repositories</p>
                            <div class="card-header-icon">
                                <span class="tag"><?php echo count($repositories); ?> found</span>
                            </div>
                        </header>
                        <div class="card-content">
                            <?php if (empty($repositories)): ?>
                                <p class="has-text-grey-light">
                                    No repositories found or unable to connect to the provider.
                                </p>
                            <?php else: ?>
                                <div class="max-h-400 overflow-y-auto">
                                    <table class="table is-striped is-fullwidth">
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
                                                            <p class="m-0 is-size-7 has-text-grey-light">
                                                                <?php echo htmlspecialchars(substr($repo['description'], 0, 60)); ?>
                                                            </p>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="tag <?php echo ($repo['visibility'] ?? 'private') === 'public' ? 'is-warning' : 'is-info'; ?>">
                                                            <?php echo htmlspecialchars($repo['visibility'] ?? 'private'); ?>
                                                        </span>
                                                    </td>
                                                    <td class="is-size-7 has-text-grey-light">
                                                        <?php echo htmlspecialchars($repo['last_activity'] ?? 'N/A'); ?>
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

                <!-- Recent Activity / Commits -->
                <div class="column is-half-desktop">
                    <div class="card">
                        <header class="card-header">
                            <p class="card-header-title">Recent Activity</p>
                        </header>
                        <div class="card-content">
                            <?php if (empty($recentActivity)): ?>
                                <p class="has-text-grey-light">
                                    No recent activity available.
                                </p>
                            <?php else: ?>
                                <div class="max-h-400 overflow-y-auto">
                                    <?php foreach ($recentActivity as $activity): ?>
                                        <div class="py-05 border-bottom-light">
                                            <div class="gap-05">
                                                <span class="mt-025">
                                                    <?php \App\Core\Icon::render('git-commit', 14, 14); ?>
                                                </span>
                                                <div>
                                                    <strong class="is-size-7"><?php echo htmlspecialchars($activity['author'] ?? 'Unknown'); ?></strong>
                                                    <p class="m-0 is-size-7">
                                                        <?php echo htmlspecialchars($activity['message'] ?? ''); ?>
                                                    </p>
                                                    <small class="has-text-grey-light">
                                                        <?php echo htmlspecialchars($activity['date'] ?? ''); ?> • 
                                                        <?php echo htmlspecialchars($activity['repo'] ?? ''); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Access Management -->
            <div class="mt-2">
                <header class="card-header">
                    <p class="card-header-title">Access Management</p>
                </header>
                <div class="table-container card-content">
                    <table class="table is-striped is-fullwidth">
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
                                    <td colspan="4" class="has-text-centered has-text-grey-light">
                                        No employees linked to this provider instance.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($instanceEmployees as $empData): ?>
                                    <tr>
                                        <td>
                                            <div class="gap-05">
                                                <div class="image is-28x28">
                                                    <div class="is-flex is-align-items-center is-justify-content-center has-background-info has-text-white" class="w-100-h-100-rounded">
                                                        <?php echo strtoupper(substr($empData['employee']['full_name'], 0, 1)); ?>
                                                    </div>
                                                </div>
                                                <?php echo htmlspecialchars($empData['employee']['full_name']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <code>@<?php echo htmlspecialchars($empData['username']); ?></code>
                                        </td>
                                        <td>
                                            <span class="tag is-success">Developer</span>
                                        </td>
                                        <td>
                                            <button class="button is-small is-ghost">
                                                <span class="icon is-small">
                                                    <?php \App\Core\Icon::render('edit', 14, 14); ?>
                                                </span>
                                                <span>Edit Access</span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>
