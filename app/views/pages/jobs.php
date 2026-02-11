<div class="section">
    <div class="level">
        <div>
            <h2 class="title">System Jobs</h2>
            <p class="subtitle">Monitor background tasks and service synchronization.</p>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="<?php echo \App\Core\UrlHelper::workspace('/jobs'); ?>" class="button is-info">
                    <span class="icon is-small">
                        <?php Icon::render('refresh', 18, 18); ?>
                    </span>
                    <span>Refresh</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="notification is-info">
        <a href="#" class="delete"></a>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="table-container">
    <table class="table is-striped is-hoverable is-fullwidth">
        <thead>
            <tr>
                <th>Status</th>
                <th>Service</th>
                <th>Action</th>
                <th>Target</th>
                <th>Result / Message</th>
                <th>Time</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($jobs)): ?>
                <tr>
                    <td colspan="7" class="has-text-centered has-text-grey-light">
                        No jobs found. Perform actions in "Digital Assets" to see tasks here.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td>
                            <span class="tag <?php 
                                echo match($job['status']) {
                                    'completed' => 'is-success',
                                    'failed' => 'is-danger',
                                    'processing' => 'is-warning',
                                    default => 'is-info'
                                };
                            ?>">
                                <span class="icon is-small">
                                    <?php if ($job['status'] === 'completed'): ?>
                                        <?php Icon::render('check', 14, 14); ?>
                                    <?php elseif ($job['status'] === 'failed'): ?>
                                        <?php Icon::render('x-circle', 14, 14); ?>
                                    <?php elseif ($job['status'] === 'processing'): ?>
                                        <?php Icon::render('refresh-cw', 14, 14, 'animation: spin 1s linear infinite;'); ?>
                                    <?php else: ?>
                                        <?php Icon::render('clock', 14, 14); ?>
                                    <?php endif; ?>
                                </span>
                                <span><?php echo ucfirst($job['status']); ?></span>
                            </span>
                        </td>
                        <td><span class="has-text-weight-bold is-size-7"><?php echo htmlspecialchars($job['service']); ?></span></td>
                        <td><?php echo str_replace('_', ' ', htmlspecialchars($job['action'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($job['target_name']); ?></strong></td>
                        <td title="<?php echo htmlspecialchars($job['result'] ?? ''); ?>">
                            <span class="is-size-7"><?php echo htmlspecialchars(substr($job['result'] ?? '-', 0, 50)); ?><?php echo strlen($job['result'] ?? '') > 50 ? '...' : ''; ?></span>
                        </td>
                        <td class="is-size-7 has-text-grey-light">
                            <?php echo date('H:i:s', strtotime($job['updated_at'])); ?>
                        </td>
                        <td class="text-right">
                            <?php if ($job['status'] === 'failed'): ?>
                                <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/jobs/retry/'); ?>" class="display-inline">
                                    <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['id']); ?>">
                                    <button type="submit" class="button is-small is-info">
                                        <span class="icon is-small">
                                            <?php Icon::render('play', 12, 12); ?>
                                        </span>
                                        <span>Retry</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
