<header>
    <div>
        <h2>System Jobs</h2>
        <p>Monitor background tasks and service synchronization.</p>
    </div>
    <a href="/jobs" style="text-decoration: none;">
        <button data-variant="secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 4 23 10 17 10"></polyline>
                <polyline points="1 20 1 14 7 14"></polyline>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
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
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                <?php elseif ($job['status'] === 'failed'): ?>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="15" y1="9" x2="9" y2="15"></line>
                                        <line x1="9" y1="9" x2="15" y2="15"></line>
                                    </svg>
                                <?php elseif ($job['status'] === 'processing'): ?>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" data-spin>
                                        <polyline points="23 4 23 10 17 10"></polyline>
                                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                                    </svg>
                                <?php else: ?>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
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
                                <form method="POST" action="/jobs/retry" style="display: inline;">
                                    <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['id']); ?>">
                                    <button type="submit" data-size="sm">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                        </svg>
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
