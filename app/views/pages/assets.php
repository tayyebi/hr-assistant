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

<!-- Dynamic Provider Type Tabs -->
<?php if (empty($typeMetadata)): ?>
    <output data-type="warning">
        No provider instances configured. <a href="<?php echo \App\Core\UrlHelper::workspace('/settings'); ?>">Configure provider instances</a> to manage digital assets.
    </output>
<?php else: ?>
    <menu role="tablist" style="margin-bottom: var(--spacing-lg); display:flex; gap: var(--spacing-md); overflow-x:auto; border-bottom: 1px solid var(--color-border); padding-bottom: var(--spacing-sm);">
        <?php $firstType = true; foreach ($typeMetadata as $type => $metadata): ?>
            <a href="#" data-type-tab="<?php echo htmlspecialchars($type); ?>" class="type-tab" data-active="<?php echo $firstType ? 'true' : 'false'; ?>" style="display:inline-flex; align-items:center; gap:8px; padding: 12px 16px; border-radius:6px 6px 0 0; border:1px solid var(--color-border); border-bottom:none; text-decoration:none; color:var(--text-color); <?php echo $firstType ? 'background-color: var(--color-background-secondary); border-bottom: 1px solid var(--color-background-secondary);' : 'background-color: var(--color-background);'; ?>">
                <div style="padding: 4px; background-color: <?php echo htmlspecialchars($metadata['color']); ?>; border-radius: 3px;">
                    <?php Icon::render($metadata['icon'], 16, 16); ?>
                </div>
                <span><?php echo htmlspecialchars($metadata['name']); ?></span>
                <small style="color: var(--text-muted);">(<?php echo count($metadata['instances']); ?>)</small>
            </a>
        <?php $firstType = false; endforeach; ?>
    </menu>
<?php endif; ?>

<!-- Dynamic Provider Type Content Panels -->
<?php foreach ($typeMetadata as $type => $metadata): ?>
    <article data-type-panel="<?php echo htmlspecialchars($type); ?>" class="type-panel" style="display: <?php echo $type === array_key_first($typeMetadata) ? 'block' : 'none'; ?>;">
        <header style="margin-bottom: var(--spacing-lg);">
            <h3 style="display: flex; align-items: center; gap: var(--spacing-sm);">
                <div style="padding: 6px; background-color: <?php echo htmlspecialchars($metadata['color']); ?>; border-radius: 4px;">
                    <?php Icon::render($metadata['icon'], 18, 18); ?>
                </div>
                <?php echo htmlspecialchars($metadata['name']); ?>
            </h3>
            <p style="margin: 0; color: var(--text-muted);">Manage digital assets for <?php echo strtolower($metadata['name']); ?> providers</p>
        </header>

        <!-- Provider Instance Tabs for this type -->
        <menu role="tablist" id="instance-tabs-<?php echo htmlspecialchars($type); ?>" style="margin-bottom: var(--spacing-lg); display:flex; gap: var(--spacing-md); overflow-x:auto;">
            <?php if (empty($metadata['instances'])): ?>
                <div style="color: var(--text-muted);">No instances configured for this type. <a href="<?php echo \App\Core\UrlHelper::workspace('/settings'); ?>">Create one</a></div>
            <?php else: ?>
                <?php $first = true; foreach ($metadata['instances'] as $inst): ?>
                    <a href="#" data-instance-id="<?php echo htmlspecialchars($inst['id']); ?>" data-type="<?php echo htmlspecialchars($type); ?>" class="instance-tab" data-active="<?php echo $first ? 'true' : 'false'; ?>" style="display:inline-flex; align-items:center; gap:8px; padding: 8px 12px; border-radius:6px; border:1px solid var(--color-border); text-decoration:none; color:var(--text-color); <?php echo $first ? 'background-color: var(--color-background-secondary);' : ''; ?>">
                        <div style="width:18px; height:18px; background-color:<?php echo htmlspecialchars($inst['name'] ? '#ddd' : '#eee'); ?>; border-radius:3px;"></div>
                        <span><?php echo htmlspecialchars($inst['name']); ?></span>
                    </a>
                <?php $first = false; endforeach; ?>
            <?php endif; ?>
        </menu>

        <!-- Instance-specific assets area for this type -->
        <div id="instance-assets-container-<?php echo htmlspecialchars($type); ?>">
            <?php if (empty($metadata['instances'])): ?>
                <output data-type="warning">No provider instances configured for <?php echo strtolower($metadata['name']); ?>. <a href="<?php echo \App\Core\UrlHelper::workspace('/settings'); ?>">Create one</a></output>
            <?php else: ?>
                <?php foreach ($metadata['instances'] as $inst): ?>
                    <?php $instId = $inst['id']; $data = $instanceAssets[$instId] ?? ['instance' => $inst, 'assets' => []]; ?>
                    <article data-instance-panel="<?php echo htmlspecialchars($instId); ?>" data-type="<?php echo htmlspecialchars($type); ?>" class="instance-panel" style="display: none;">
                        <header>
                            <h4><?php echo htmlspecialchars($data['instance']['name']); ?> — <?php echo htmlspecialchars(\App\Core\ProviderType::getName($data['instance']['provider'])); ?></h4>
                            <p>Available accounts from this instance</p>
                        </header>

                        <?php if (empty($data['assets'])): ?>
                            <output data-type="info">No assets available from this instance.</output>
                        <?php else: ?>
                            <details>
                                <summary><?php echo count($data['assets']); ?> accounts available</summary>
                                <ul style="list-style: none; padding: var(--spacing-sm) 0; font-size: 0.875rem;">
                                    <?php foreach ($data['assets'] as $asset): ?>
                                        <li style="padding: var(--spacing-xs) 0;">
                                            <code><?php echo htmlspecialchars($asset['identifier']); ?></code>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </details>
                        <?php endif; ?>
                        
                        <!-- Asset assignment section -->
                        <section style="margin-top: var(--spacing-xl);">
                            <h5>Asset Assignments</h5>
                            <?php 
                                // Filter assignments by this provider instance
                                $typeAssignments = [];
                                foreach ($employees as $emp) {
                                    $empAssets = $assignedAssets[$emp['id']] ?? [];
                                    foreach ($empAssets as $asset) {
                                        if ($asset['provider'] === $inst['provider']) {
                                            $typeAssignments[] = ['employee' => $emp, 'asset' => $asset];
                                        }
                                    }
                                }
                            ?>

                            <?php if (empty($typeAssignments)): ?>
                                <output data-type="info">No assets assigned from this instance yet.</output>
                            <?php else: ?>
                                <div data-table>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Asset ID</th>
                                                <th>Status</th>
                                                <th style="text-align: right;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($typeAssignments as $row): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['employee']['full_name']); ?></td>
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
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </article>
<?php endforeach; ?>

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



<script>
// Type tab switching
document.querySelectorAll('.type-tab').forEach(tab => {
    tab.addEventListener('click', (e) => {
        e.preventDefault();
        const type = tab.dataset.typeTab;
        
        // Update active tab
        document.querySelectorAll('.type-tab').forEach(t => t.dataset.active = 'false');
        tab.dataset.active = 'true';
        
        // Show corresponding type panel
        document.querySelectorAll('.type-panel').forEach(p => p.style.display = 'none');
        const panel = document.querySelector(`[data-type-panel="${type}"]`);
        if (panel) {
            panel.style.display = 'block';
            
            // Auto-click the first instance tab in this type
            const firstInstanceTab = panel.querySelector('.instance-tab[data-active="true"]');
            if (firstInstanceTab) {
                firstInstanceTab.click();
            }
        }
    });
});

// Instance tab switching (within each type)
document.querySelectorAll('.instance-tab').forEach(tab => {
    tab.addEventListener('click', (e) => {
        e.preventDefault();
        const id = tab.dataset.instanceId;
        const type = tab.dataset.type;
        
        // Update active instance tab within this type
        const typeContainer = document.querySelector(`#instance-tabs-${type}`);
        if (typeContainer) {
            typeContainer.querySelectorAll('.instance-tab').forEach(t => t.dataset.active = 'false');
        }
        tab.dataset.active = 'true';
        
        // Show corresponding instance panel within this type
        document.querySelectorAll(`[data-instance-panel][data-type="${type}"]`).forEach(p => p.style.display = 'none');
        const panel = document.querySelector(`[data-instance-panel="${id}"][data-type="${type}"]`);
        if (panel) panel.style.display = 'block';
    });
});

// Initialize: Show first type and first instance
const firstTypeTab = document.querySelector('.type-tab[data-active="true"]');
if (firstTypeTab) firstTypeTab.click();

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


