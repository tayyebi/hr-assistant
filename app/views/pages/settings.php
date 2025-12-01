<header>
    <div>
        <h2>System Configuration</h2>
        <p>Manage integrations, keys, and backend communication for this tenant.</p>
    </div>
    <button type="submit" form="settings-form">
        <?php Icon::render('save', 18, 18); ?>
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
                    <?php Icon::render('messages', 24, 24, 'stroke: var(--color-primary);'); ?>
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
                    <?php Icon::render('mail', 24, 24, 'stroke: var(--color-warning);'); ?>
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
                    <?php Icon::render('server', 24, 24, 'stroke: #ea580c;'); ?>
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
                    <?php Icon::render('lock', 24, 24, 'stroke: #4f46e5;'); ?>
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
                    <?php Icon::render('git-branch', 24, 24, 'stroke: #dc2626;'); ?>
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
