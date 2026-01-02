<header>
    <div>
        <h2>Digital Assets Management</h2>
        <p>Connect providers, discover available assets, and assign them to employees.</p>
    </div>
</header>

<?php if (!empty($message)): ?>
    <output data-type="<?php echo htmlspecialchars($messageType ?? 'success'); ?>">
        <?php echo htmlspecialchars($message); ?>
    </output>
<?php endif; ?>

<!-- Asset Type Tabs -->
<menu role="tablist" style="margin-bottom: var(--spacing-lg);">
    <li>
        <a href="#email-assets" data-asset-type="email" data-active="true">
            <?php Icon::render('mail', 18, 18); ?>
            Email
        </a>
    </li>
    <li>
        <a href="#git-assets" data-asset-type="git">
            <?php Icon::render('git-branch', 18, 18); ?>
            Git
        </a>
    </li>
    <li>
        <a href="#messaging-assets" data-asset-type="messenger">
            <?php Icon::render('message-circle', 18, 18); ?>
            Messaging
        </a>
    </li>
    <li>
        <a href="#iam-assets" data-asset-type="iam">
            <?php Icon::render('key', 18, 18); ?>
            Identity
        </a>
    </li>
</menu>

<!-- Email Assets Section -->
<article id="email-assets" class="asset-section" style="display: block;">
    <header>
        <h3>Email Accounts</h3>
        <p>Available email accounts from configured providers</p>
    </header>

    <?php if (empty($availableAssets['email']['providers']) || empty(array_filter($availableAssets['email']['providers']))): ?>
        <output data-type="warning">
            No email providers configured. <a href="/settings">Configure email providers</a> first.
        </output>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-lg);">
            <?php foreach ($availableAssets['email']['providers'] as $providerType => $providerData): ?>
                <article style="border: 1px solid var(--border-color); border-radius: var(--radius); padding: var(--spacing-md);">
                    <header style="margin-bottom: var(--spacing-md); display: flex; justify-content: space-between; align-items: center;">
                        <h4><?php echo htmlspecialchars($providerData['name']); ?></h4>
                        <button class="test-connection-btn" data-provider="<?php echo htmlspecialchars($providerType); ?>" data-size="sm" data-variant="secondary">
                            Test
                        </button>
                    </header>

                    <?php if (empty($providerData['assets'])): ?>
                        <output data-type="info" style="font-size: 0.875rem;">No assets available</output>
                    <?php else: ?>
                        <details>
                            <summary><?php echo count($providerData['assets']); ?> accounts available</summary>
                            <ul style="list-style: none; padding: var(--spacing-sm) 0; font-size: 0.875rem;">
                                <?php foreach ($providerData['assets'] as $asset): ?>
                                    <li style="padding: var(--spacing-xs) 0;">
                                        <code><?php echo htmlspecialchars($asset['identifier']); ?></code>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Email Asset Assignments -->
    <section style="margin-top: var(--spacing-xl);">
        <h4>Assigned Email Accounts</h4>
        <?php 
            $emailAssignments = [];
            foreach ($employees as $emp) {
                $empAssets = $assignedAssets[$emp['id']] ?? [];
                foreach ($empAssets as $asset) {
                    if ($asset['asset_type'] === 'email') {
                        $emailAssignments[] = ['employee' => $emp, 'asset' => $asset];
                    }
                }
            }
        ?>

        <?php if (empty($emailAssignments)): ?>
            <output data-type="info">No email accounts assigned yet.</output>
        <?php else: ?>
            <div data-table>
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Provider</th>
                            <th>Account</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emailAssignments as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['employee']['full_name']); ?></td>
                                <td><?php echo htmlspecialchars(ProviderType::getName($row['asset']['provider'])); ?></td>
                                <td><code><?php echo htmlspecialchars($row['asset']['identifier']); ?></code></td>
                                <td>
                                    <mark data-status="<?php echo htmlspecialchars($row['asset']['status']); ?>">
                                        <?php echo ucfirst($row['asset']['status']); ?>
                                    </mark>
                                </td>
                                <td style="text-align: right;">
                                    <button class="unassign-asset-btn" data-asset-id="<?php echo htmlspecialchars($row['asset']['id']); ?>" data-size="sm" data-variant="danger">
                                        Unassign
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</article>

<!-- Git Assets Section -->
<article id="git-assets" class="asset-section" style="display: none;">
    <header>
        <h3>Git Accounts</h3>
        <p>Available git user accounts from configured providers</p>
    </header>

    <?php if (empty($availableAssets['git']['providers']) || empty(array_filter($availableAssets['git']['providers']))): ?>
        <output data-type="warning">
            No git providers configured. <a href="/settings">Configure git providers</a> first.
        </output>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-lg);">
            <?php foreach ($availableAssets['git']['providers'] as $providerType => $providerData): ?>
                <article style="border: 1px solid var(--border-color); border-radius: var(--radius); padding: var(--spacing-md);">
                    <header style="margin-bottom: var(--spacing-md); display: flex; justify-content: space-between; align-items: center;">
                        <h4><?php echo htmlspecialchars($providerData['name']); ?></h4>
                        <button class="test-connection-btn" data-provider="<?php echo htmlspecialchars($providerType); ?>" data-size="sm" data-variant="secondary">
                            Test
                        </button>
                    </header>

                    <?php if (empty($providerData['assets'])): ?>
                        <output data-type="info" style="font-size: 0.875rem;">No assets available</output>
                    <?php else: ?>
                        <details>
                            <summary><?php echo count($providerData['assets']); ?> accounts available</summary>
                            <ul style="list-style: none; padding: var(--spacing-sm) 0; font-size: 0.875rem;">
                                <?php foreach ($providerData['assets'] as $asset): ?>
                                    <li style="padding: var(--spacing-xs) 0;">
                                        <code><?php echo htmlspecialchars($asset['identifier']); ?></code>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Git Asset Assignments -->
    <section style="margin-top: var(--spacing-xl);">
        <h4>Assigned Git Accounts</h4>
        <?php 
            $gitAssignments = [];
            foreach ($employees as $emp) {
                $empAssets = $assignedAssets[$emp['id']] ?? [];
                foreach ($empAssets as $asset) {
                    if ($asset['asset_type'] === 'git') {
                        $gitAssignments[] = ['employee' => $emp, 'asset' => $asset];
                    }
                }
            }
        ?>

        <?php if (empty($gitAssignments)): ?>
            <output data-type="info">No git accounts assigned yet.</output>
        <?php else: ?>
            <div data-table>
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Provider</th>
                            <th>Username</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gitAssignments as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['employee']['full_name']); ?></td>
                                <td><?php echo htmlspecialchars(ProviderType::getName($row['asset']['provider'])); ?></td>
                                <td><code><?php echo htmlspecialchars($row['asset']['identifier']); ?></code></td>
                                <td>
                                    <mark data-status="<?php echo htmlspecialchars($row['asset']['status']); ?>">
                                        <?php echo ucfirst($row['asset']['status']); ?>
                                    </mark>
                                </td>
                                <td style="text-align: right;">
                                    <button class="unassign-asset-btn" data-asset-id="<?php echo htmlspecialchars($row['asset']['id']); ?>" data-size="sm" data-variant="danger">
                                        Unassign
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</article>

<!-- Messaging Assets Section -->
<article id="messaging-assets" class="asset-section" style="display: none;">
    <header>
        <h3>Messaging Accounts</h3>
        <p>Available messaging accounts from configured providers</p>
    </header>

    <?php if (empty($availableAssets['messenger']['providers']) || empty(array_filter($availableAssets['messenger']['providers']))): ?>
        <output data-type="warning">
            No messaging providers configured. <a href="/settings">Configure messaging providers</a> first.
        </output>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-lg);">
            <?php foreach ($availableAssets['messenger']['providers'] as $providerType => $providerData): ?>
                <article style="border: 1px solid var(--border-color); border-radius: var(--radius); padding: var(--spacing-md);">
                    <header style="margin-bottom: var(--spacing-md); display: flex; justify-content: space-between; align-items: center;">
                        <h4><?php echo htmlspecialchars($providerData['name']); ?></h4>
                        <button class="test-connection-btn" data-provider="<?php echo htmlspecialchars($providerType); ?>" data-size="sm" data-variant="secondary">
                            Test
                        </button>
                    </header>

                    <?php if (empty($providerData['assets'])): ?>
                        <output data-type="info" style="font-size: 0.875rem;">No assets available</output>
                    <?php else: ?>
                        <details>
                            <summary><?php echo count($providerData['assets']); ?> accounts available</summary>
                            <ul style="list-style: none; padding: var(--spacing-sm) 0; font-size: 0.875rem;">
                                <?php foreach ($providerData['assets'] as $asset): ?>
                                    <li style="padding: var(--spacing-xs) 0;">
                                        <code><?php echo htmlspecialchars($asset['identifier']); ?></code>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Messaging Asset Assignments -->
    <section style="margin-top: var(--spacing-xl);">
        <h4>Assigned Messaging Accounts</h4>
        <?php 
            $messengerAssignments = [];
            foreach ($employees as $emp) {
                $empAssets = $assignedAssets[$emp['id']] ?? [];
                foreach ($empAssets as $asset) {
                    if ($asset['asset_type'] === 'messenger') {
                        $messengerAssignments[] = ['employee' => $emp, 'asset' => $asset];
                    }
                }
            }
        ?>

        <?php if (empty($messengerAssignments)): ?>
            <output data-type="info">No messaging accounts assigned yet.</output>
        <?php else: ?>
            <div data-table>
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Provider</th>
                            <th>Account</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messengerAssignments as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['employee']['full_name']); ?></td>
                                <td><?php echo htmlspecialchars(ProviderType::getName($row['asset']['provider'])); ?></td>
                                <td><code><?php echo htmlspecialchars($row['asset']['identifier']); ?></code></td>
                                <td>
                                    <mark data-status="<?php echo htmlspecialchars($row['asset']['status']); ?>">
                                        <?php echo ucfirst($row['asset']['status']); ?>
                                    </mark>
                                </td>
                                <td style="text-align: right;">
                                    <button class="unassign-asset-btn" data-asset-id="<?php echo htmlspecialchars($row['asset']['id']); ?>" data-size="sm" data-variant="danger">
                                        Unassign
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</article>

<!-- IAM Assets Section -->
<article id="iam-assets" class="asset-section" style="display: none;">
    <header>
        <h3>Identity & Access Management</h3>
        <p>Available IAM user accounts from configured providers</p>
    </header>

    <?php if (empty($availableAssets['iam']['providers']) || empty(array_filter($availableAssets['iam']['providers']))): ?>
        <output data-type="warning">
            No IAM providers configured. <a href="/settings">Configure IAM providers</a> first.
        </output>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-lg);">
            <?php foreach ($availableAssets['iam']['providers'] as $providerType => $providerData): ?>
                <article style="border: 1px solid var(--border-color); border-radius: var(--radius); padding: var(--spacing-md);">
                    <header style="margin-bottom: var(--spacing-md); display: flex; justify-content: space-between; align-items: center;">
                        <h4><?php echo htmlspecialchars($providerData['name']); ?></h4>
                        <button class="test-connection-btn" data-provider="<?php echo htmlspecialchars($providerType); ?>" data-size="sm" data-variant="secondary">
                            Test
                        </button>
                    </header>

                    <?php if (empty($providerData['assets'])): ?>
                        <output data-type="info" style="font-size: 0.875rem;">No assets available</output>
                    <?php else: ?>
                        <details>
                            <summary><?php echo count($providerData['assets']); ?> accounts available</summary>
                            <ul style="list-style: none; padding: var(--spacing-sm) 0; font-size: 0.875rem;">
                                <?php foreach ($providerData['assets'] as $asset): ?>
                                    <li style="padding: var(--spacing-xs) 0;">
                                        <code><?php echo htmlspecialchars($asset['identifier']); ?></code>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- IAM Asset Assignments -->
    <section style="margin-top: var(--spacing-xl);">
        <h4>Assigned IAM Accounts</h4>
        <?php 
            $iamAssignments = [];
            foreach ($employees as $emp) {
                $empAssets = $assignedAssets[$emp['id']] ?? [];
                foreach ($empAssets as $asset) {
                    if ($asset['asset_type'] === 'iam') {
                        $iamAssignments[] = ['employee' => $emp, 'asset' => $asset];
                    }
                }
            }
        ?>

        <?php if (empty($iamAssignments)): ?>
            <output data-type="info">No IAM accounts assigned yet.</output>
        <?php else: ?>
            <div data-table>
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Provider</th>
                            <th>Username</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($iamAssignments as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['employee']['full_name']); ?></td>
                                <td><?php echo htmlspecialchars(ProviderType::getName($row['asset']['provider'])); ?></td>
                                <td><code><?php echo htmlspecialchars($row['asset']['identifier']); ?></code></td>
                                <td>
                                    <mark data-status="<?php echo htmlspecialchars($row['asset']['status']); ?>">
                                        <?php echo ucfirst($row['asset']['status']); ?>
                                    </mark>
                                </td>
                                <td style="text-align: right;">
                                    <button class="unassign-asset-btn" data-asset-id="<?php echo htmlspecialchars($row['asset']['id']); ?>" data-size="sm" data-variant="danger">
                                        Unassign
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</article>

<script>
// Tab navigation
document.querySelectorAll('menu[role="tablist"] a').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('menu[role="tablist"] a').forEach(t => t.removeAttribute('data-active'));
        this.setAttribute('data-active', 'true');
        
        document.querySelectorAll('.asset-section').forEach(s => s.style.display = 'none');
        document.querySelector(this.getAttribute('href')).style.display = 'block';
    });
});

// Test connection
document.querySelectorAll('.test-connection-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const provider = this.dataset.provider;
        const originalText = this.textContent;
        this.textContent = 'Testing...';
        this.disabled = true;
        
        try {
            const response = await fetch('/assets/testConnection', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'provider=' + encodeURIComponent(provider)
            });
            const data = await response.json();
            
            if (data.success) {
                alert('✓ Connection successful!');
            } else {
                alert('✗ Connection failed: ' + (data.error || 'Unknown error'));
            }
        } catch (err) {
            alert('✗ Error testing connection: ' + err.message);
        } finally {
            this.textContent = originalText;
            this.disabled = false;
        }
    });
});

// Unassign asset
document.querySelectorAll('.unassign-asset-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Are you sure you want to unassign this asset?')) return;
        
        const assetId = this.dataset.assetId;
        
        try {
            const response = await fetch('/assets/unassignAsset', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'asset_id=' + encodeURIComponent(assetId)
            });
            const data = await response.json();
            
            if (data.success) {
                alert('Asset unassigned successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        } catch (err) {
            alert('Error unassigning asset: ' + err.message);
        }
    });
});
</script>

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
