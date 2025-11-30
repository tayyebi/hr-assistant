<header>
    <div>
        <h2>System Configuration</h2>
        <p>Manage integrations, keys, and backend communication for this tenant.</p>
    </div>
    <button type="submit" form="settings-form">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
            <polyline points="17 21 17 13 7 13 7 21"></polyline>
            <polyline points="7 3 7 8 15 8"></polyline>
        </svg>
        Save All Changes
    </button>
</header>

<?php if (!empty($message)): ?>
    <output data-type="success"><?php echo htmlspecialchars($message); ?></output>
<?php endif; ?>

<form method="POST" action="/settings" id="settings-form">
    <section data-grid="2">
        <!-- Telegram & Backend -->
        <article style="grid-column: span 2;">
            <header style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-lg);">
                <div style="padding: var(--spacing-sm); background-color: var(--color-primary-light); border-radius: var(--radius-md);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 style="margin: 0;">Telegram & Backend</h3>
                    <p style="margin: 0; font-size: 0.75rem;">Configure bot tokens and webhook settings.</p>
                </div>
            </header>

            <section data-grid="2">
                <div>
                    <label>Telegram Bot API Token</label>
                    <input type="password" name="telegram_bot_token" value="<?php echo htmlspecialchars($config['telegram_bot_token'] ?? ''); ?>">
                </div>
                <div>
                    <label>Telegram Mode</label>
                    <div style="display: flex; gap: var(--spacing-lg); padding: var(--spacing-sm) 0;">
                        <label style="display: flex; align-items: center; gap: var(--spacing-sm); cursor: pointer;">
                            <input type="radio" name="telegram_mode" value="webhook" <?php echo ($config['telegram_mode'] ?? 'webhook') === 'webhook' ? 'checked' : ''; ?>>
                            Webhook
                        </label>
                        <label style="display: flex; align-items: center; gap: var(--spacing-sm); cursor: pointer;">
                            <input type="radio" name="telegram_mode" value="polling" <?php echo ($config['telegram_mode'] ?? '') === 'polling' ? 'checked' : ''; ?>>
                            Polling
                        </label>
                    </div>
                </div>
                <div style="grid-column: span 2;">
                    <label>Backend API / Webhook URL</label>
                    <input type="text" name="webhook_url" value="<?php echo htmlspecialchars($config['webhook_url'] ?? ''); ?>" placeholder="https://api.your-backend.com">
                    <small style="color: var(--text-muted);">Used for Telegram webhook and data sync.</small>
                </div>
            </section>
        </article>

        <!-- Email Gateway -->
        <article style="grid-column: span 2;">
            <header style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-lg);">
                <div style="padding: var(--spacing-sm); background-color: var(--color-warning-light); border-radius: var(--radius-md);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-warning)" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </div>
                <div>
                    <h3 style="margin: 0;">Email Gateway</h3>
                    <p style="margin: 0; font-size: 0.75rem;">IMAP/SMTP for Inbox & Outbox</p>
                </div>
            </header>

            <h4>IMAP (Incoming)</h4>
            <section data-grid="2" style="margin-bottom: var(--spacing-lg);">
                <div>
                    <label>Host</label>
                    <input type="text" name="imap_host" value="<?php echo htmlspecialchars($config['imap_host'] ?? ''); ?>">
                </div>
                <div>
                    <label>Port</label>
                    <input type="number" name="imap_port" value="<?php echo htmlspecialchars($config['imap_port'] ?? '993'); ?>">
                </div>
                <div>
                    <label>Username</label>
                    <input type="text" name="imap_user" value="<?php echo htmlspecialchars($config['imap_user'] ?? ''); ?>">
                </div>
                <div>
                    <label>Password</label>
                    <input type="password" name="imap_pass" value="<?php echo htmlspecialchars($config['imap_pass'] ?? ''); ?>">
                </div>
                <div style="grid-column: span 2;">
                    <label style="display: flex; align-items: center; gap: var(--spacing-sm); cursor: pointer;">
                        <input type="checkbox" name="imap_tls" <?php echo ($config['imap_tls'] ?? '1') === '1' ? 'checked' : ''; ?>>
                        Use TLS/SSL
                    </label>
                </div>
            </section>

            <h4>SMTP (Outgoing)</h4>
            <section data-grid="2">
                <div>
                    <label>Host</label>
                    <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($config['smtp_host'] ?? ''); ?>">
                </div>
                <div>
                    <label>Port</label>
                    <input type="number" name="smtp_port" value="<?php echo htmlspecialchars($config['smtp_port'] ?? '465'); ?>">
                </div>
                <div>
                    <label>Username</label>
                    <input type="text" name="smtp_user" value="<?php echo htmlspecialchars($config['smtp_user'] ?? ''); ?>">
                </div>
                <div>
                    <label>Password</label>
                    <input type="password" name="smtp_pass" value="<?php echo htmlspecialchars($config['smtp_pass'] ?? ''); ?>">
                </div>
                <div style="grid-column: span 2;">
                    <label style="display: flex; align-items: center; gap: var(--spacing-sm); cursor: pointer;">
                        <input type="checkbox" name="smtp_tls" <?php echo ($config['smtp_tls'] ?? '1') === '1' ? 'checked' : ''; ?>>
                        Use TLS/SSL
                    </label>
                </div>
            </section>
        </article>

        <!-- Mail Service API -->
        <article>
            <header style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-lg);">
                <div style="padding: var(--spacing-sm); background-color: #fed7aa; border-radius: var(--radius-md);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2">
                        <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                        <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                        <line x1="6" y1="6" x2="6.01" y2="6"></line>
                        <line x1="6" y1="18" x2="6.01" y2="18"></line>
                    </svg>
                </div>
                <div>
                    <h3 style="margin: 0;">Mail Service API</h3>
                    <p style="margin: 0; font-size: 0.75rem;">Admin Management</p>
                </div>
            </header>

            <div style="margin-bottom: var(--spacing-md);">
                <label>URL</label>
                <input type="text" name="mailcow_url" value="<?php echo htmlspecialchars($config['mailcow_url'] ?? ''); ?>">
            </div>
            <div>
                <label>API Key</label>
                <input type="password" name="mailcow_api_key" value="<?php echo htmlspecialchars($config['mailcow_api_key'] ?? ''); ?>">
            </div>
        </article>

        <!-- Keycloak IAM -->
        <article>
            <header style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-lg);">
                <div style="padding: var(--spacing-sm); background-color: #c7d2fe; border-radius: var(--radius-md);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <div>
                    <h3 style="margin: 0;">Keycloak IAM</h3>
                    <p style="margin: 0; font-size: 0.75rem;">Auth Server API</p>
                </div>
            </header>

            <div style="margin-bottom: var(--spacing-md);">
                <label>Base URL</label>
                <input type="text" name="keycloak_url" value="<?php echo htmlspecialchars($config['keycloak_url'] ?? ''); ?>">
            </div>
            <section data-grid="2" style="margin-bottom: var(--spacing-md);">
                <div>
                    <label>Realm</label>
                    <input type="text" name="keycloak_realm" value="<?php echo htmlspecialchars($config['keycloak_realm'] ?? ''); ?>">
                </div>
                <div>
                    <label>Client ID</label>
                    <input type="text" name="keycloak_client_id" value="<?php echo htmlspecialchars($config['keycloak_client_id'] ?? ''); ?>">
                </div>
            </section>
            <div>
                <label>Client Secret</label>
                <input type="password" name="keycloak_client_secret" value="<?php echo htmlspecialchars($config['keycloak_client_secret'] ?? ''); ?>">
            </div>
        </article>

        <!-- GitLab -->
        <article style="grid-column: span 2;">
            <header style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-lg);">
                <div style="padding: var(--spacing-sm); background-color: #fecaca; border-radius: var(--radius-md);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
                        <line x1="6" y1="3" x2="6" y2="15"></line>
                        <circle cx="18" cy="6" r="3"></circle>
                        <circle cx="6" cy="18" r="3"></circle>
                        <path d="M18 9a9 9 0 0 1-9 9"></path>
                    </svg>
                </div>
                <div>
                    <h3 style="margin: 0;">GitLab</h3>
                    <p style="margin: 0; font-size: 0.75rem;">Git Service API</p>
                </div>
            </header>

            <section data-grid="2">
                <div>
                    <label>URL</label>
                    <input type="text" name="gitlab_url" value="<?php echo htmlspecialchars($config['gitlab_url'] ?? ''); ?>">
                </div>
                <div>
                    <label>Access Token</label>
                    <input type="password" name="gitlab_token" value="<?php echo htmlspecialchars($config['gitlab_token'] ?? ''); ?>">
                </div>
            </section>
        </article>
    </section>
</form>
