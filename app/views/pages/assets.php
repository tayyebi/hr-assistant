<header>
    <div>
        <h2>Digital Assets & Services</h2>
        <p>Provision accounts and manage access via background tasks.</p>
    </div>
</header>

<?php if (!empty($message)): ?>
    <output data-type="info"><?php echo htmlspecialchars($message); ?></output>
<?php endif; ?>

<!-- Service Tabs -->
<menu role="tablist">
    <li>
        <a href="/assets?service=mailcow" <?php echo $activeService === 'mailcow' ? 'data-active="true"' : ''; ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            Mail Service
        </a>
    </li>
    <li>
        <a href="/assets?service=gitlab" <?php echo $activeService === 'gitlab' ? 'data-active="true"' : ''; ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="6" y1="3" x2="6" y2="15"></line>
                <circle cx="18" cy="6" r="3"></circle>
                <circle cx="6" cy="18" r="3"></circle>
                <path d="M18 9a9 9 0 0 1-9 9"></path>
            </svg>
            Git Service
        </a>
    </li>
    <li>
        <a href="/assets?service=keycloak" <?php echo $activeService === 'keycloak' ? 'data-active="true"' : ''; ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
            </svg>
            Keycloak IAM
        </a>
    </li>
</menu>

<article>
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-lg);">
        <h3>
            <?php 
                $serviceNames = [
                    'mailcow' => 'Mail Service Management',
                    'gitlab' => 'Git Service Users & Projects',
                    'keycloak' => 'Identity & Access Management'
                ];
                echo $serviceNames[$activeService] ?? 'Service Management';
            ?>
        </h3>
        <small style="color: var(--text-muted);">
            Connected to: <?php echo htmlspecialchars($config[$activeService . '_url'] ?? 'Not configured'); ?>
        </small>
    </header>

    <div data-table>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Account Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                    <?php 
                        $accounts = $emp['accounts'] ?? [];
                        $account = null;
                        foreach ($accounts as $acc) {
                            if ($acc['service'] === $activeService) {
                                $account = $acc;
                                break;
                            }
                        }
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($emp['full_name']); ?></strong>
                        </td>
                        <td>
                            <?php if ($account): ?>
                                <mark data-status="<?php echo $account['status'] ?? 'active'; ?>">
                                    <?php echo ucfirst($account['status'] ?? 'active'); ?>: <?php echo htmlspecialchars($account['accountId'] ?? ''); ?>
                                </mark>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-style: italic; font-size: 0.75rem;">No account</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <?php if ($account): ?>
                                <form method="POST" action="/assets/provision" style="display: inline;">
                                    <input type="hidden" name="service" value="<?php echo htmlspecialchars($activeService); ?>">
                                    <input type="hidden" name="action" value="RESET_CREDENTIAL">
                                    <input type="hidden" name="target_name" value="<?php echo htmlspecialchars($emp['full_name']); ?>">
                                    <input type="hidden" name="metadata" value="<?php echo htmlspecialchars(json_encode(['accountId' => $account['accountId']])); ?>">
                                    <button type="submit" data-variant="secondary" data-size="sm">Reset PW</button>
                                </form>
                                <form method="POST" action="/assets/provision" style="display: inline;">
                                    <input type="hidden" name="service" value="<?php echo htmlspecialchars($activeService); ?>">
                                    <input type="hidden" name="action" value="DEACTIVATE">
                                    <input type="hidden" name="target_name" value="<?php echo htmlspecialchars($emp['full_name']); ?>">
                                    <input type="hidden" name="metadata" value="<?php echo htmlspecialchars(json_encode(['accountId' => $account['accountId']])); ?>">
                                    <button type="submit" data-variant="danger" data-size="sm">Suspend</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="/assets/provision">
                                    <input type="hidden" name="service" value="<?php echo htmlspecialchars($activeService); ?>">
                                    <input type="hidden" name="action" value="PROVISION">
                                    <input type="hidden" name="target_name" value="<?php echo htmlspecialchars($emp['full_name']); ?>">
                                    <input type="hidden" name="metadata" value="<?php echo htmlspecialchars(json_encode($emp)); ?>">
                                    <button type="submit" data-size="sm">
                                        <?php 
                                            $createLabels = [
                                                'mailcow' => 'Create Mailbox',
                                                'gitlab' => 'Create User',
                                                'keycloak' => 'Federate User'
                                            ];
                                            echo $createLabels[$activeService] ?? 'Provision';
                                        ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($employees)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-muted); padding: var(--spacing-xl);">
                            No employees found. Add employees first.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</article>
