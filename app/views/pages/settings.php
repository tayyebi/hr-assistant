<header>
    <h2>Workspace Settings</h2>
    <p>Manage integrations, communication channels, and system preferences.</p>
</header>

<?php if (!empty($message)): ?>
    <output data-type="success"><?php echo htmlspecialchars($message); ?></output>
<?php endif; ?>

<!-- Quick Status Overview -->
<section style="margin-bottom: var(--spacing-xl);">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: var(--spacing-md);">
        <?php 
            $providerInstances = \App\Models\ProviderInstance::getAll($tenant['id']);
            $stats = [
                ['label' => 'Connected Services', 'value' => count($providerInstances), 'icon' => 'zap'],
                ['label' => 'Identity Providers', 'value' => count(array_filter($providerInstances, fn($p) => $p['type'] === 'iam')), 'icon' => 'lock'],
                ['label' => 'Repository Access', 'value' => count(array_filter($providerInstances, fn($p) => $p['type'] === 'git')), 'icon' => 'git-branch'],
                ['label' => 'Communication', 'value' => count(array_filter($providerInstances, fn($p) => in_array($p['type'], ['email', 'messenger']))), 'icon' => 'mail'],
            ];
            foreach ($stats as $stat):
        ?>
        <article style="background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%); padding: var(--spacing-lg); border-radius: var(--radius); border: 1px solid var(--border-color);">
            <div style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-sm);">
                <div style="width: 40px; height: 40px; background: var(--color-info); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; color: white;">
                    <?php \App\Core\Icon::render($stat['icon'], 20, 20); ?>
                </div>
                <div>
                    <p style="margin: 0; font-size: 0.8rem; color: var(--text-muted);"><?php echo $stat['label']; ?></p>
                    <p style="margin: 0; font-size: 1.8rem; font-weight: 700; color: var(--text-primary);"><?php echo $stat['value']; ?></p>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- Provider Management Section -->
<section style="margin-bottom: var(--spacing-xl);">
    <header style="margin-bottom: var(--spacing-lg);">
        <h3 style="margin: 0 0 0.5rem 0;">🔌 Connected Services</h3>
        <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">Manage integrations for identity, repositories, calendars, and messaging.</p>
    </header>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--spacing-lg);">
        <?php
            $services = [
                [
                    'title' => 'Identity Management',
                    'desc' => 'SSO, LDAP, SAML',
                    'icon' => 'lock',
                    'color' => '#dbeafe',
                    'iconColor' => '#0369a1',
                    'type' => 'iam',
                    'url' => '/identity/',
                ],
                [
                    'title' => 'Code Repositories',
                    'desc' => 'Git hosting, access control',
                    'icon' => 'git-branch',
                    'color' => '#fce7f3',
                    'iconColor' => '#be185d',
                    'type' => 'git',
                    'url' => '/repositories/',
                ],
                [
                    'title' => 'Calendar Services',
                    'desc' => 'Google, Outlook, CalDAV',
                    'icon' => 'calendar',
                    'color' => '#dcfce7',
                    'iconColor' => '#15803d',
                    'type' => 'calendar',
                    'url' => '/calendars/',
                ],
                [
                    'title' => 'Password Management',
                    'desc' => 'Vault, Bitwarden, 1Password',
                    'icon' => 'key',
                    'color' => '#f3e8ff',
                    'iconColor' => '#7c3aed',
                    'type' => 'secrets',
                    'url' => '/secrets/',
                ],
                [
                    'title' => 'Messaging & Email',
                    'desc' => 'SMTP, Telegram, Slack',
                    'icon' => 'mail',
                    'color' => '#fef08a',
                    'iconColor' => '#ca8a04',
                    'type' => ['email', 'messenger'],
                    'url' => '/messages/',
                ],
            ];
            foreach ($services as $service):
                $typeFilter = is_array($service['type']) ? $service['type'] : [$service['type']];
                $instances = array_filter($providerInstances, fn($p) => in_array($p['type'], $typeFilter));
                $count = count($instances);
        ?>
        <article style="border: 2px solid var(--border-color); border-radius: var(--radius); padding: var(--spacing-lg); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.borderColor='var(--color-info)'; this.style.boxShadow='0 4px 12px rgba(59, 130, 246, 0.1)'" onmouseout="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'">
            <div style="display: flex; gap: var(--spacing-md); margin-bottom: var(--spacing-lg);">
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, <?php echo $service['color']; ?> 0%, rgba(255,255,255,0.5)); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <?php \App\Core\Icon::render($service['icon'], 28, 28, 'color: ' . $service['iconColor'] . ';'); ?>
                </div>
                <div style="flex: 1;">
                    <h4 style="margin: 0; font-size: 1.1rem;"><?php echo $service['title']; ?></h4>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-muted); font-size: 0.85rem;"><?php echo $service['desc']; ?></p>
                </div>
            </div>
            
            <div style="padding: var(--spacing-md); background: var(--bg-secondary); border-radius: var(--radius); margin-bottom: var(--spacing-md);">
                <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted);">
                    <strong style="color: var(--text-primary);"><?php echo $count; ?></strong>
                    <?php echo $count === 1 ? 'instance' : 'instances'; ?> configured
                </p>
            </div>

            <?php if (!empty($instances)): ?>
                <div style="padding: var(--spacing-md); background: var(--bg-secondary); border-radius: var(--radius); margin-bottom: var(--spacing-md); font-size: 0.85rem;">
                    <?php foreach (array_slice($instances, 0, 2) as $inst): ?>
                        <div style="padding: 0.4rem 0; display: flex; align-items: center; gap: var(--spacing-sm); color: var(--text-muted);">
                            <span style="width: 8px; height: 8px; background: var(--color-success); border-radius: 50%;"></span>
                            <?php echo htmlspecialchars($inst['name']); ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($instances) > 2): ?>
                        <div style="padding: 0.4rem 0; color: var(--text-muted);">+<?php echo count($instances) - 2; ?> more</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div style="display: flex; gap: var(--spacing-sm);">
                <button type="button" onclick="openSetupModal('<?php echo htmlspecialchars(implode(',', $typeFilter)); ?>')" style="flex: 1; padding: var(--spacing-md); background: var(--color-success); color: white; border: none; border-radius: var(--radius); text-decoration: none; font-weight: 500; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    Add New
                </button>
                <a href="<?php echo \App\Core\UrlHelper::workspace($service['url']); ?>" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: var(--spacing-sm); padding: var(--spacing-md); background: var(--color-info); color: white; border-radius: var(--radius); text-decoration: none; font-weight: 500; transition: all 0.2s ease;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    Manage <?php echo $count > 0 ? 'Instances' : 'Details'; ?>
                    <?php \App\Core\Icon::render('arrow-right', 18, 18); ?>
                </a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- Setup Modal -->
<div id="setupModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: var(--bg-primary); border-radius: var(--radius); max-width: 500px; margin: var(--spacing-xl) auto; padding: var(--spacing-xl); box-shadow: 0 20px 25px rgba(0,0,0,0.15); position: relative;">
        <button type="button" onclick="closeSetupModal()" style="position: absolute; top: var(--spacing-md); right: var(--spacing-md); background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">✕</button>
        
        <h2 style="margin: 0 0 var(--spacing-lg) 0;">Add Provider Instance</h2>
        
        <form id="setupForm" style="display: none;">
            <div style="margin-bottom: var(--spacing-lg);">
                <label style="display: block; margin-bottom: var(--spacing-sm); font-weight: 500;">Instance Name *</label>
                <input type="text" name="name" id="instanceName" placeholder="e.g., Main GitLab Server" style="width: 100%; padding: var(--spacing-md); border: 1px solid var(--border-color); border-radius: var(--radius); box-sizing: border-box;" required>
            </div>

            <div style="margin-bottom: var(--spacing-lg);">
                <label style="display: block; margin-bottom: var(--spacing-sm); font-weight: 500;">Provider Type *</label>
                <select id="providerType" onchange="updateProviderSelector()" style="width: 100%; padding: var(--spacing-md); border: 1px solid var(--border-color); border-radius: var(--radius); box-sizing: border-box;" required>
                    <option value="">Select Provider Type</option>
                </select>
            </div>

            <div style="margin-bottom: var(--spacing-lg);">
                <label style="display: block; margin-bottom: var(--spacing-sm); font-weight: 500;">Provider *</label>
                <select name="provider" id="provider" onchange="loadProviderFields()" style="width: 100%; padding: var(--spacing-md); border: 1px solid var(--border-color); border-radius: var(--radius); box-sizing: border-box;" required>
                    <option value="">Select a provider</option>
                </select>
            </div>

            <div id="configFields"></div>

            <div style="display: flex; gap: var(--spacing-md); margin-top: var(--spacing-lg);">
                <button type="button" id="testBtn" onclick="testConnection()" style="padding: var(--spacing-md) var(--spacing-lg); background: var(--color-warning); color: white; border: none; border-radius: var(--radius); font-weight: 500; cursor: pointer; display: none;">Test Connection</button>
                <button type="submit" style="flex: 1; padding: var(--spacing-md) var(--spacing-lg); background: var(--color-info); color: white; border: none; border-radius: var(--radius); font-weight: 500; cursor: pointer;">Add Provider</button>
                <button type="button" onclick="closeSetupModal()" style="padding: var(--spacing-md) var(--spacing-lg); background: var(--color-muted); color: white; border: none; border-radius: var(--radius); font-weight: 500; cursor: pointer;">Cancel</button>
            </div>

            <div id="formMessage" style="margin-top: var(--spacing-md); padding: var(--spacing-md); border-radius: var(--radius); display: none;"></div>
        </form>
    </div>
</div>

<!-- Communication Channels -->
<section style="margin-bottom: var(--spacing-xl);">
    <header style="margin-bottom: var(--spacing-lg);">
        <h3 style="margin: 0 0 0.5rem 0;">💬 Communication Channels</h3>
        <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">Control which channels are available for team communication.</p>
    </header>

    <?php if (!empty($messagingChannels)): ?>
        <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/settings/'); ?>" style="background: var(--bg-secondary); padding: var(--spacing-lg); border-radius: var(--radius); border: 1px solid var(--border-color);">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-lg);">
                <?php foreach ($messagingChannels as $key => $ch): ?>
                    <label style="display: flex; align-items: flex-start; gap: var(--spacing-md); padding: var(--spacing-md); background: var(--bg-primary); border-radius: var(--radius); border: 1px solid var(--border-color); cursor: pointer; transition: all 0.2s ease; opacity: <?php echo $ch['hasProvider'] ? '1' : '0.6'; ?>" onmouseover="this.style.borderColor='var(--color-info)'" onmouseout="this.style.borderColor='var(--border-color)'">
                        <div style="margin-top: 2px;">
                            <input type="checkbox" name="messaging_<?php echo htmlspecialchars($key); ?>_enabled" 
                                   <?php echo $ch['enabled'] ? 'checked' : ''; ?>
                                   <?php echo !$ch['hasProvider'] ? 'disabled' : ''; ?>
                                   style="cursor: pointer;">
                        </div>
                        <div>
                            <div style="font-weight: 500; color: var(--text-primary);">
                                <?php echo htmlspecialchars($ch['name']); ?>
                            </div>
                            <?php if (!$ch['hasProvider']): ?>
                                <p style="margin: 0.5rem 0 0 0; font-size: 0.8rem; color: var(--text-muted);">Provider not configured yet</p>
                            <?php else: ?>
                                <p style="margin: 0.5rem 0 0 0; font-size: 0.8rem; color: var(--color-success);">✓ Provider connected</p>
                            <?php endif; ?>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            <div style="display: flex; gap: var(--spacing-md);">
                <button type="submit" style="padding: var(--spacing-md) var(--spacing-lg); background: var(--color-info); color: white; border: none; border-radius: var(--radius); font-weight: 500; cursor: pointer;">
                    Save Communication Preferences
                </button>
                <p style="margin: 0; padding: var(--spacing-md); color: var(--text-muted); font-size: 0.9rem; align-self: center;">
                    Changes take effect immediately
                </p>
            </div>
        </form>
    <?php endif; ?>
</section>

<!-- Help & Support -->
<section style="background: var(--bg-secondary); padding: var(--spacing-lg); border-radius: var(--radius); border: 1px solid var(--border-color);">
    <h3 style="margin-top: 0;">Need Help?</h3>
    <p style="color: var(--text-muted); margin-bottom: var(--spacing-md);">
        Each service can be configured in its respective module. Start by clicking "Add New" on any service card above to quickly add your first provider instance.
    </p>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-md);">
        <div>
            <strong style="display: block; margin-bottom: 0.5rem;">Identity (IAM)</strong>
            <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Set up SSO and centralized user management</p>
        </div>
        <div>
            <strong style="display: block; margin-bottom: 0.5rem;">Repositories</strong>
            <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Link GitHub, GitLab, or other Git platforms</p>
        </div>
        <div>
            <strong style="display: block; margin-bottom: 0.5rem;">Calendars</strong>
            <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Connect Google Calendar, Outlook, or CalDAV</p>
        </div>
        <div>
            <strong style="display: block; margin-bottom: 0.5rem;">Secrets Management</strong>
            <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Integrate password vaults and secret stores</p>
        </div>
    </div>
</section>

<script>
// Provider metadata for dynamic form generation
const providerMetadata = <?php echo json_encode(\App\Core\ProviderSettings::getProvidersMetadata()); ?>;
const providerFields = <?php echo json_encode(array_map(function($provider) {
    return \App\Core\ProviderSettings::getFields($provider);
}, array_keys(\App\Core\ProviderSettings::getProvidersMetadata()))); ?>;

// Map providers by their keys
const providersData = {};
<?php foreach (\App\Core\ProviderSettings::getProvidersMetadata() as $key => $meta): ?>
    providersData['<?php echo addslashes($key); ?>'] = <?php echo json_encode($meta); ?>;
<?php endforeach; ?>

const allFields = {};
<?php foreach (\App\Core\ProviderSettings::getProvidersMetadata() as $key => $meta): ?>
    allFields['<?php echo addslashes($key); ?>'] = <?php echo json_encode(\App\Core\ProviderSettings::getFields($key)); ?>;
<?php endforeach; ?>

let selectedTypes = [];

function openSetupModal(types) {
    selectedTypes = types ? types.split(',').map(t => t.trim()) : [];
    const modal = document.getElementById('setupModal');
    const form = document.getElementById('setupForm');
    form.style.display = 'block';
    modal.style.display = 'flex';
    
    // Populate provider types
    const typeSelect = document.getElementById('providerType');
    typeSelect.innerHTML = '<option value="">Select Provider Type</option>';
    
    selectedTypes.forEach(type => {
        const providers = Object.entries(providersData).filter(([_, meta]) => meta.type === type);
        if (providers.length > 0) {
            const option = document.createElement('option');
            option.value = type;
            option.textContent = type.charAt(0).toUpperCase() + type.slice(1);
            typeSelect.appendChild(option);
        }
    });
}

function closeSetupModal() {
    document.getElementById('setupModal').style.display = 'none';
    document.getElementById('setupForm').reset();
    document.getElementById('configFields').innerHTML = '';
    document.getElementById('formMessage').style.display = 'none';
    selectedTypes = [];
}

function updateProviderSelector() {
    const type = document.getElementById('providerType').value;
    const providerSelect = document.getElementById('provider');
    providerSelect.innerHTML = '<option value="">Select a provider</option>';
    document.getElementById('configFields').innerHTML = '';
    document.getElementById('testBtn').style.display = 'none';
    
    if (!type) return;
    
    Object.entries(providersData).forEach(([key, meta]) => {
        if (meta.type === type) {
            const option = document.createElement('option');
            option.value = key;
            option.textContent = meta.name;
            providerSelect.appendChild(option);
        }
    });
}

function loadProviderFields() {
    const provider = document.getElementById('provider').value;
    const configFields = document.getElementById('configFields');
    configFields.innerHTML = '';
    document.getElementById('testBtn').style.display = 'inline-block';
    
    if (!provider || !allFields[provider]) return;
    
    const fields = allFields[provider];
    Object.entries(fields).forEach(([fieldName, fieldConfig]) => {
        const fieldDiv = document.createElement('div');
        fieldDiv.style.marginBottom = 'var(--spacing-lg)';
        
        let fieldHtml = `<label style="display: block; margin-bottom: var(--spacing-sm); font-weight: 500;">
            ${fieldConfig.label || fieldName} ${fieldConfig.required ? '*' : ''}
        </label>`;
        
        switch(fieldConfig.type) {
            case 'checkbox':
                fieldHtml += `<input type="checkbox" name="config[${fieldName}]" style="cursor: pointer;">`;
                break;
            case 'textarea':
                fieldHtml += `<textarea name="config[${fieldName}]" placeholder="${fieldConfig.placeholder || ''}" style="width: 100%; padding: var(--spacing-md); border: 1px solid var(--border-color); border-radius: var(--radius); box-sizing: border-box; min-height: 150px; font-family: monospace;" ${fieldConfig.required ? 'required' : ''}></textarea>`;
                break;
            case 'number':
                fieldHtml += `<input type="number" name="config[${fieldName}]" value="${fieldConfig.value || ''}" placeholder="${fieldConfig.placeholder || ''}" style="width: 100%; padding: var(--spacing-md); border: 1px solid var(--border-color); border-radius: var(--radius); box-sizing: border-box;" ${fieldConfig.required ? 'required' : ''}>`;
                break;
            case 'password':
                fieldHtml += `<input type="password" name="config[${fieldName}]" placeholder="${fieldConfig.placeholder || ''}" style="width: 100%; padding: var(--spacing-md); border: 1px solid var(--border-color); border-radius: var(--radius); box-sizing: border-box;" ${fieldConfig.required ? 'required' : ''}>`;
                break;
            default:
                fieldHtml += `<input type="text" name="config[${fieldName}]" placeholder="${fieldConfig.placeholder || ''}" style="width: 100%; padding: var(--spacing-md); border: 1px solid var(--border-color); border-radius: var(--radius); box-sizing: border-box;" ${fieldConfig.required ? 'required' : ''}>`;
        }
        
        if (fieldConfig.description) {
            fieldHtml += `<p style="margin: 0.5rem 0 0 0; font-size: 0.8rem; color: var(--text-muted);">${fieldConfig.description}</p>`;
        }
        
        fieldDiv.innerHTML = fieldHtml;
        configFields.appendChild(fieldDiv);
    });
}

function testConnection() {
    const provider = document.getElementById('provider').value;
    const config = new FormData(document.getElementById('setupForm'));
    
    const configData = {};
    const configEntries = new FormData(document.getElementById('setupForm'));
    for (let [key, value] of configEntries.entries()) {
        if (key.startsWith('config[')) {
            const fieldName = key.replace('config[', '').replace(']', '');
            configData[fieldName] = value;
        }
    }
    
    if (!provider) {
        showMessage('Please select a provider', false);
        return;
    }
    
    showMessage('Testing connection...', null);
    
    fetch('<?php echo \App\Core\UrlHelper::workspace('/api/provider/test-connection'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            provider: provider,
            config: configData
        })
    })
    .then(response => response.json())
    .then(data => {
        showMessage(data.message, data.success);
    })
    .catch(error => {
        showMessage('Connection test failed: ' + error.message, false);
    });
}

function showMessage(message, success) {
    const msgDiv = document.getElementById('formMessage');
    msgDiv.textContent = message;
    msgDiv.style.display = 'block';
    
    if (success === null) {
        msgDiv.style.background = 'var(--color-info)';
        msgDiv.style.color = 'white';
    } else if (success) {
        msgDiv.style.background = 'var(--color-success)';
        msgDiv.style.color = 'white';
    } else {
        msgDiv.style.background = '#fee2e2';
        msgDiv.style.color = '#991b1b';
    }
}

document.getElementById('setupForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const type = document.getElementById('providerType').value;
    const provider = document.getElementById('provider').value;
    const name = document.getElementById('instanceName').value;
    
    if (!type || !provider || !name) {
        showMessage('Please fill in all required fields', false);
        return;
    }
    
    const configData = {};
    const formData = new FormData(this);
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('config[')) {
            const fieldName = key.replace('config[', '').replace(']', '');
            configData[fieldName] = value;
        }
    }
    
    try {
        const response = await fetch('<?php echo \App\Core\UrlHelper::workspace('/api/provider/create'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                type: type,
                provider: provider,
                name: name,
                config: configData
            })
        });
        
        const data = await response.json();
        if (data.success) {
            showMessage('Provider created successfully! Reloading...', true);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showMessage(data.message || 'Failed to create provider', false);
        }
    } catch (error) {
        showMessage('Error: ' + error.message, false);
    }
});

// Close modal when clicking outside
document.getElementById('setupModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeSetupModal();
    }
});
</script>