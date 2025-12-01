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
            <?php Icon::render('mail', 18, 18); ?>
            Mail Service
        </a>
    </li>
    <li>
        <a href="/assets?service=gitlab" <?php echo $activeService === 'gitlab' ? 'data-active="true"' : ''; ?>>
            <?php Icon::render('git-branch', 18, 18); ?>
            Git Service
        </a>
    </li>
    <li>
        <a href="/assets?service=keycloak" <?php echo $activeService === 'keycloak' ? 'data-active="true"' : ''; ?>>
            <?php Icon::render('key', 18, 18); ?>
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
