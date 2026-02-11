<div class="section">
    <div class="level">
        <div>
            <h2 class="title">Notifications Center</h2>
            <p class="subtitle">Manage system alerts, announcements, and user notifications.</p>
        </div>
    </div>
</div>

<main class="section">
    <div class="columns">
        <div class="column is-12">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">Notifications System</p>
                </header>
                <div class="card-content">
                    <p class="has-text-grey-light">Real-time notifications, announcements, and reminders.</p>
                    
                    <!-- Overview Stats -->
                    <div class="columns is-multiline" style="margin-top: 1rem;">
                        <div class="column is-half-tablet is-one-quarter-desktop">
                            <div class="box has-background-grey-light">
                                <p class="heading is-6">Overview</p>
                                <p class="has-text-grey-light">Unread: <?php echo $unreadCount ?? 0; ?></p>
                            </div>
                        </div>
                        
                        <div class="column is-half-tablet is-one-quarter-desktop">
                            <div class="box has-background-grey-light">
                                <p class="heading is-6">Recent Activity</p>
                                <p class="has-text-grey-light">Last 24 hours</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Create Notification Form -->
                    <div style="margin-top: 2rem;">
                        <h4 class="title is-5">Send Notification</h4>
                        <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/notifications/create/'); ?>">
                            <div class="columns is-multiline">
                                <div class="column is-half-tablet">
                                    <div class="field">
                                        <label class="label">Title</label>
                                        <div class="control">
                                            <input class="input" type="text" name="title" placeholder="Notification title" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-half-tablet">
                                    <div class="field">
                                        <label class="label">Type</label>
                                        <div class="control">
                                            <span class="select is-fullwidth">
                                                <select name="type" required>
                                                    <option value="announcement">Announcement</option>
                                                    <option value="alert">Alert</option>
                                                    <option value="info">Information</option>
                                                    <option value="reminder">Reminder</option>
                                                </select>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-full">
                                    <div class="field">
                                        <label class="label">Message</label>
                                        <div class="control">
                                            <textarea class="textarea" name="message" rows="3" placeholder="Notification message" required></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-half-tablet">
                                    <div class="field">
                                        <label class="label">Priority</label>
                                        <div class="control">
                                            <span class="select is-fullwidth">
                                                <select name="priority">
                                                    <option value="normal">Normal</option>
                                                    <option value="high">High</option>
                                                    <option value="critical">Critical</option>
                                                </select>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-half-tablet">
                                    <div class="field">
                                        <label class="label">Audience</label>
                                        <div class="control">
                                            <span class="select is-fullwidth">
                                                <select name="target_audience">
                                                    <option value="all">All Users</option>
                                                    <option value="employees">Employees Only</option>
                                                    <option value="admins">Admins Only</option>
                                                </select>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-full">
                                    <div class="field">
                                        <div class="control">
                                            <button type="submit" class="button is-primary">Send Notification</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Notifications List -->
                    <div style="margin-top: 2rem;">
                        <h4 class="title is-5">Recent Notifications</h4>
                        <?php if (empty($notifications)): ?>
                            <p class="has-text-grey-light">No notifications yet. Create your first notification above!</p>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <?php foreach ($notifications as $notification): ?>
                                    <div class="box <?php echo !$notification['is_read'] ? 'has-border-left' : ''; ?>" style="<?php echo !$notification['is_read'] ? 'border-left: 3px solid #3273dc; opacity: 1;' : 'opacity: 0.7;'; ?>">
                                        <div class="level" style="margin-bottom: 0.5rem;">
                                            <div class="level-left">
                                                <div class="level-item">
                                                    <h5 class="title is-6"><?php echo htmlspecialchars($notification['title']); ?></h5>
                                                </div>
                                            </div>
                                            <div class="level-right">
                                                <div class="level-item">
                                                    <span class="tag is-light is-small">
                                                        <?php echo ucfirst($notification['type']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="block"><?php echo htmlspecialchars($notification['message']); ?></p>
                                        <div class="is-flex is-justify-content-space-between is-align-items-center">
                                            <small class="has-text-grey-light">From: <?php echo htmlspecialchars($notification['from_user_name'] ?? 'System'); ?> • <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></small>
                                            <?php if (!$notification['is_read']): ?>
                                                <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/notifications/mark-read/'); ?>" style="display: inline;">
                                                    <input type="hidden" name="notification_id" value="<?php echo htmlspecialchars($notification['id']); ?>">
                                                    <button type="submit" class="button is-small is-light">Mark Read</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

