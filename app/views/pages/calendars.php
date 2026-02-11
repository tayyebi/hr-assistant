<div class="section">
    <div class="level">
        <div>
            <h2 class="title">Calendars</h2>
            <p class="subtitle">Manage calendar provider instances and view events.</p>
        </div>
    </div>
</div>

<?php if (empty($providerInstances)): ?>
    <div class="section has-text-centered">
        <div class="block">
            <?php \App\Core\Icon::render('clock', 64, 64, 'stroke-width: 1;'); ?>
        </div>
        <h3>No Calendar Providers Configured</h3>
        <p>Add a calendar provider (Google, Outlook, CalDAV) in Settings to manage calendars.</p>
        <a href="<?php echo \App\Core\UrlHelper::workspace('/settings'); ?>" class="button is-primary">
            Go to Settings
        </a>
    </div>
<?php else: ?>
    <div class="columns">
        <div class="column is-12">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">Calendar Providers</p>
                </header>
                <div class="table-container card-content">
                    <table class="table is-striped is-fullwidth">
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th>Instance Name</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($providerInstances as $instance): ?>
                                <tr>
                                    <td>
                                        <div class="is-flex is-align-items-center gap-05">
                                            <span class="icon is-small">
                                                <?php \App\Core\Icon::render('clock', 16, 16); ?>
                                            </span>
                                            <?php echo htmlspecialchars(\App\Core\ProviderType::getName($instance['provider'])); ?>
                                        </div>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($instance['name']); ?></strong></td>
                                    <td>
                                        <span class="tag is-success">Active</span>
                                    </td>
                                    <td>
                                        <a href="#" class="button is-small is-ghost">
                                            View Events
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
