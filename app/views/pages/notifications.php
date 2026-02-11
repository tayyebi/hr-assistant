<header>
    <div>
        <h2>🔔 Notifications Center</h2>
        <p>Manage system alerts, announcements, and user notifications.</p>
    </div>
</header>

<main>
    <section data-grid="1">
        <article>
            <h3>📬 Notifications System</h3>
            <p style="color: var(--text-muted);">Real-time notifications, announcements, and reminders.</p>
            
            <!-- Overview Stats -->
            <div style="display: flex; gap: var(--spacing-md); margin-bottom: var(--spacing-lg);">
                <div style="padding: var(--spacing-md); background: var(--surface-variant); border-radius: 8px; flex: 1;">
                    <h4 style="margin: 0 0 var(--spacing-xs) 0;">📊 Overview</h4>
                    <p style="margin: 0; color: var(--text-muted);">Unread: <?php echo $unreadCount ?? 0; ?></p>
                </div>
                
                <div style="padding: var(--spacing-md); background: var(--surface-variant); border-radius: 8px; flex: 1;">
                    <h4 style="margin: 0 0 var(--spacing-xs) 0;">📅 Recent Activity</h4>
                    <p style="margin: 0; color: var(--text-muted);">Last 24 hours</p>
                </div>
            </div>
            
            <!-- Create Notification Form -->
            <div style="margin-bottom: var(--spacing-lg);">
                <h4>📝 Send Notification</h4>
                <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/notifications/create/'); ?>">
                    <div data-grid="2">
                        <div>
                            <label>Title</label>
                            <input type="text" name="title" placeholder="Notification title" required>
                        </div>
                        <div>
                            <label>Type</label>
                            <select name="type" required>
                                <option value="announcement">📢 Announcement</option>
                                <option value="alert">⚠️ Alert</option>
                                <option value="info">ℹ️ Information</option>
                                <option value="reminder">⏰ Reminder</option>
                            </select>
                        </div>
                        <div style="grid-column: span 2;">
                            <label>Message</label>
                            <textarea name="message" rows="3" placeholder="Notification message" required></textarea>
                        </div>
                        <div>
                            <label>Priority</label>
                            <select name="priority">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div>
                            <label>Audience</label>
                            <select name="target_audience">
                                <option value="all">All Users</option>
                                <option value="employees">Employees Only</option>
                                <option value="admins">Admins Only</option>
                            </select>
                        </div>
                    </div>
                    <footer style="margin-top: var(--spacing-md);">
                        <button type="submit">Send Notification</button>
                    </footer>
                </form>
            </div>
            
            <!-- Notifications List -->
            <div>
                <h4>📋 Recent Notifications</h4>
                <?php if (empty($notifications)): ?>
                    <p style="color: var(--text-muted);">No notifications yet. Create your first notification above!</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
                        <?php foreach ($notifications as $notification): ?>
                            <div style="padding: var(--spacing-md); border: 1px solid var(--border-color); border-radius: 8px; <?php echo $notification['is_read'] ? 'opacity: 0.7;' : 'border-left: 3px solid var(--primary-color);'; ?>">
                                <div style="display: flex; justify-content: between; align-items: start; gap: var(--spacing-md);">
                                    <div style="flex: 1;">
                                        <h5 style="margin: 0 0 var(--spacing-xs) 0;"><?php echo htmlspecialchars($notification['title']); ?></h5>
                                        <p style="margin: 0 0 var(--spacing-xs) 0; color: var(--text-muted);"><?php echo htmlspecialchars($notification['message']); ?></p>
                                        <small style="color: var(--text-muted);">From: <?php echo htmlspecialchars($notification['from_user_name'] ?? 'System'); ?> • <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></small>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
                                        <span style="padding: 2px 8px; background: var(--surface-variant); border-radius: 12px; font-size: 0.8em;">
                                            <?php echo ucfirst($notification['type']); ?>
                                        </span>
                                        <?php if (!$notification['is_read']): ?>
                                            <button onclick="markAsRead('<?php echo $notification['id']; ?>')" style="padding: 4px 8px; font-size: 0.8em;">Mark Read</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    </section>
</main>

<script>
function markAsRead(notificationId) {
    fetch('<?php echo \App\Core\UrlHelper::workspace("/notifications/mark-read/"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'notification_id=' + encodeURIComponent(notificationId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}
</script>