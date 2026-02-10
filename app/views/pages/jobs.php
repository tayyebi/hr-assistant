<header>
    <div>
        <h2>System Jobs</h2>
        <p>Monitor background tasks and service synchronization.</p>
    </div>
    <a href="/jobs" style="text-decoration: none;">
        <button data-variant="secondary">
            <?php Icon::render('refresh', 18, 18); ?>
            Refresh
        </button>
    </a>
</header>

<?php if (!empty($message)): ?>
    <output data-type="info"><?php echo htmlspecialchars($message); ?></output>
<?php endif; ?>

<div data-table>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Service</th>
                <th>Action</th>
                <th>Target</th>
                <th>Result / Message</th>
                <th>Time</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($jobs)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: var(--spacing-xl);">
                        No jobs found. Perform actions in "Digital Assets" to see tasks here.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td>
                            <mark data-status="<?php echo htmlspecialchars($job['status']); ?>">
                                <?php if ($job['status'] === 'completed'): ?>
                                    <?php Icon::render('check', 14, 14); ?>
                                <?php elseif ($job['status'] === 'failed'): ?>
                                    <?php Icon::render('x-circle', 14, 14); ?>
                                <?php elseif ($job['status'] === 'processing'): ?>
                                    <?php Icon::render('refresh-cw', 14, 14, 'animation: spin 1s linear infinite;'); ?>
                                <?php else: ?>
                                    <?php Icon::render('clock', 14, 14); ?>
                                <?php endif; ?>
                                <?php echo ucfirst($job['status']); ?>
                            </mark>
                        </td>
                        <td style="text-transform: uppercase; font-weight: 500; font-size: 0.75rem;">
                            <?php echo htmlspecialchars($job['service']); ?>
                        </td>
                        <td><?php echo str_replace('_', ' ', htmlspecialchars($job['action'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($job['target_name']); ?></strong></td>
                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($job['result'] ?? ''); ?>">
                            <?php echo htmlspecialchars($job['result'] ?? '-'); ?>
                        </td>
                        <td style="font-size: 0.75rem; color: var(--text-muted);">
                            <?php echo date('H:i:s', strtotime($job['updated_at'])); ?>
                        </td>
                        <td style="text-align: right;">
                            <?php if ($job['status'] === 'failed'): ?>
                                <form method="POST" action="<?php echo View::workspaceUrl('jobs/retry'); ?>" style="display: inline;">
                                    <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['id']); ?>">
                                    <button type="submit" data-size="sm">
                                        <?php Icon::render('play', 12, 12); ?>
                                        Retry
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
