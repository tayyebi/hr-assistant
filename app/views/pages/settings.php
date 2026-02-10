<header>
    <div>
        <h2>Provider Configuration</h2>
        <p>Manage integrations and create tenant-scoped provider instances to assign to employees.</p>
    </div>
</header>

<?php if (!empty($message)): ?>
    <output data-type="success"><?php echo htmlspecialchars($message); ?></output>
<?php endif; ?>

<section>
    <h3>Available Providers</h3>
    <p style="margin-top: 0; color: var(--text-muted);">Choose a provider type and create a named instance (e.g., "GitLab - Engineering"). Instances are tenant-scoped and can be assigned to employees.</p>

    <div id="providers-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: var(--spacing-md); margin-bottom: var(--spacing-lg);">
        <?php foreach (ProviderSettings::getProvidersMetadata() as $pKey => $pMeta): ?>
            <article data-provider="<?php echo htmlspecialchars($pKey); ?>" data-type="<?php echo htmlspecialchars($pMeta['type']); ?>" style="border: 1px solid var(--color-border); padding: var(--spacing-lg); border-radius: var(--radius-md); display:flex; gap: var(--spacing-md); align-items:center; cursor: pointer;">
                <div style="padding: var(--spacing-sm); background-color: <?php echo htmlspecialchars($pMeta['color']); ?>; border-radius: var(--radius-md); opacity: 0.85;">
                    <?php Icon::render($pMeta['icon'], 20, 20); ?>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($pMeta['name']); ?></strong>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($pMeta['description']); ?></div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<hr style="margin: var(--spacing-lg) 0;">

<section>
    <h3>Provider Instances (Tenant-level)</h3>
    <p style="margin-top: 0; color: var(--text-muted);">Create named instances of providers (e.g., "GitLab - Engineering") that can be assigned to employees.</p>

    <article style="margin-bottom: var(--spacing-md);">
        <h4>Existing Instances</h4>
        <?php $instances = ProviderInstance::getAll($tenant['id']); ?>
        <?php if (empty($instances)): ?>
            <p style="color: var(--text-muted);">No provider instances configured.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($instances as $inst): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($inst['name']); ?></strong>
                        <small style="margin-left: var(--spacing-sm); color: var(--text-muted);">(<?php echo htmlspecialchars($inst['type']); ?> / <?php echo htmlspecialchars($inst['provider']); ?>)</small>
                        <form method="POST" action="<?php echo View::workspaceUrl('settings/providers/delete'); ?>" style="display:inline; margin-left: var(--spacing-md);">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($inst['id']); ?>">
                            <button type="submit" data-variant="ghost" data-size="icon">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>

    <article>
        <h4>Create Provider Instance</h4>
        <form method="POST" action="<?php echo View::workspaceUrl('settings/providers'); ?>" id="provider-form">
            <section data-grid="3">
                <div>
                    <label>Type</label>
                    <select name="type" required>
                        <option value="">Select type</option>
                        <option value="<?php echo ProviderType::TYPE_EMAIL; ?>">Email</option>
                        <option value="<?php echo ProviderType::TYPE_GIT; ?>">Git</option>
                        <option value="<?php echo ProviderType::TYPE_MESSENGER; ?>">Messaging</option>
                        <option value="<?php echo ProviderType::TYPE_IAM; ?>">Identity</option>
                    </select>
                </div>
                <div>
                    <label>Provider</label>
                    <select name="provider" required id="provider-select" disabled>
                        <option value="">Choose provider</option>
                        <?php foreach (ProviderSettings::getProvidersMetadata() as $pKey => $pMeta): ?>
                            <option value="<?php echo htmlspecialchars($pKey); ?>" data-type="<?php echo htmlspecialchars($pMeta['type']); ?>"><?php echo htmlspecialchars($pMeta['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Name</label>
                    <input type="text" name="name" placeholder="e.g. GitLab - Engineering" required>
                </div>
            </section>
            
            <!-- Provider-specific configuration fields -->
            <div id="provider-config" style="margin-top: var(--spacing-md); display: none;">
                <h5>Provider Configuration</h5>
                <section data-grid="2" id="config-fields">
                    <!-- Dynamic fields will be inserted here -->
                </section>
            </div>
            <footer style="margin-top: var(--spacing-md);">
                <button type="submit">Add Provider Instance</button>
            </footer>
        </form>
    </article>
</section>

<script>
// Provider instance form: filter provider list based on selected type and show config fields
const typeSelect = document.querySelector('select[name="type"]');
const providerSelect = document.getElementById('provider-select');
const providerConfig = document.getElementById('provider-config');
const configFields = document.getElementById('config-fields');

// Provider field definitions loaded from PHP
const providerFieldsData = <?php 
echo json_encode(array_reduce(
    array_keys(ProviderSettings::getProvidersMetadata()),
    function($carry, $provider) {
        $carry[$provider] = ProviderSettings::getFields($provider);
        return $carry;
    },
    []
));
?>;

if (typeSelect && providerSelect) {
    typeSelect.addEventListener('change', (e) => {
        const val = e.target.value;
        providerSelect.disabled = !val;
        
        // Filter provider options
        for (const opt of providerSelect.querySelectorAll('option[data-type]')) {
            opt.style.display = (opt.dataset.type === val) ? 'block' : 'none';
        }
        providerSelect.value = '';
        hideProviderConfig();
    });
    
    providerSelect.addEventListener('change', (e) => {
        const selectedProvider = e.target.value;
        
        if (selectedProvider && providerFieldsData[selectedProvider]) {
            showProviderConfig(selectedProvider, providerFieldsData[selectedProvider]);
        } else {
            hideProviderConfig();
        }
    });

    // Click on provider card to prefill the create form
    document.querySelectorAll('#providers-grid article[data-provider]').forEach(card => {
        card.addEventListener('click', () => {
            const prov = card.dataset.provider;
            const type = card.dataset.type;
            typeSelect.value = type;
            typeSelect.dispatchEvent(new Event('change'));
            providerSelect.value = prov;
            providerSelect.dispatchEvent(new Event('change'));
            document.querySelector('input[name="name"]').focus();
            document.querySelector('input[name="name"]').scrollIntoView({behavior: 'smooth', block: 'center'});
        });
    });
}

function showProviderConfig(provider, fields) {
    configFields.innerHTML = '';
    
    Object.entries(fields).forEach(([fieldName, fieldConfig]) => {
        const fieldDiv = document.createElement('div');
        
        // Create label
        const label = document.createElement('label');
        label.textContent = fieldConfig.label;
        if (fieldConfig.required) {
            const required = document.createElement('span');
            required.style.color = 'var(--danger, #dc2626)';
            required.textContent = ' *';
            label.appendChild(required);
        }
        fieldDiv.appendChild(label);
        
        // Create input field
        let input;
        if (fieldConfig.type === 'textarea') {
            input = document.createElement('textarea');
            input.rows = 3;
        } else if (fieldConfig.type === 'select' && fieldConfig.options) {
            input = document.createElement('select');
            Object.entries(fieldConfig.options).forEach(([value, text]) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = text;
                input.appendChild(option);
            });
        } else if (fieldConfig.type === 'checkbox') {
            input = document.createElement('input');
            input.type = 'checkbox';
            input.value = '1';
        } else {
            input = document.createElement('input');
            input.type = fieldConfig.type || 'text';
        }
        
        input.name = `config[${fieldName}]`;
        if (fieldConfig.required) input.required = true;
        if (fieldConfig.placeholder) input.placeholder = fieldConfig.placeholder;
        if (fieldConfig.value) input.value = fieldConfig.value;
        
        fieldDiv.appendChild(input);
        
        // Add description if provided
        if (fieldConfig.description) {
            const desc = document.createElement('small');
            desc.style.color = 'var(--text-muted, #6b7280)';
            desc.style.display = 'block';
            desc.style.marginTop = '0.25rem';
            desc.textContent = fieldConfig.description;
            fieldDiv.appendChild(desc);
        }
        
        configFields.appendChild(fieldDiv);
    });
    
    providerConfig.style.display = 'block';
}

function hideProviderConfig() {
    providerConfig.style.display = 'none';
    configFields.innerHTML = '';
}
</script>

<style>
[role="tablist"] {
    position: sticky;
    top: 0;
}

/* Simplified settings page - provider tabs removed */
</style>
