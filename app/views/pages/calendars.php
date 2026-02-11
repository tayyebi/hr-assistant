<?php
$this->title = 'Calendars';
$this->layout('layouts/main', [
    'activeTab' => 'calendars'
]);
?>

<header>
    <div>
        <h2>Calendars</h2>
        <p>Manage calendar provider instances and view events.</p>
    </div>
</header>

<?php if (empty($providerInstances)): ?>
    <section data-empty style="padding: var(--spacing-xl); text-align: center;">
        <?php \App\Core\Icon::render('clock', 64, 64, 'stroke-width: 1; color: var(--text-muted);'); ?>
        <h3>No Calendar Providers Configured</h3>
        <p>Add a calendar provider (Google, Outlook, CalDAV) in Settings to manage calendars.</p>
        <a href="<?php echo \App\Core\UrlHelper::workspace('/settings'); ?>" data-button>
            Go to Settings
        </a>
    </section>
<?php else: ?>
    <section data-grid="1">
        <article>
            <header>
                <h3>Calendar Providers</h3>
            </header>
            
            <div data-table>
                <table>
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
                                    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                                        <?php \App\Core\Icon::render('clock', 16, 16); ?>
                                        <?php echo htmlspecialchars(\App\Core\ProviderType::getName($instance['provider'])); ?>
                                    </div>
                                </td>
                                <td><strong><?php echo htmlspecialchars($instance['name']); ?></strong></td>
                                <td>
                                    <mark data-status="active">Active</mark>
                                </td>
                                <td>
                                    <a href="#" data-button data-variant="ghost" data-size="sm">
                                        View Events
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
<?php endif; ?>
