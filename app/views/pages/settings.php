<header>
    <div>
        <h2>Provider Configuration</h2>
        <p>Manage integrations and configure your preferred providers for email, messaging, git, and identity management.</p>
    </div>
    <button type="submit" form="settings-form">
        <?php Icon::render('save', 18, 18); ?>
        Save Configuration
    </button>
</header>

<?php if (!empty($message)): ?>
    <output data-type="success"><?php echo htmlspecialchars($message); ?></output>
<?php endif; ?>

<form method="POST" action="/settings" id="settings-form">
    <section data-grid="auto">
        <!-- Provider Selection Tab -->
        <menu role="tablist" style="grid-column: 1;">
            <h3 style="margin: 0 0 var(--spacing-md) 0; padding: 0 var(--spacing-md); font-size: 0.875rem; color: var(--text-muted);">
                PROVIDERS
            </h3>
            
            <?php 
            $assetTypes = [
                ProviderType::TYPE_EMAIL => 'Email',
                ProviderType::TYPE_GIT => 'Git',
                ProviderType::TYPE_MESSENGER => 'Messaging',
                ProviderType::TYPE_IAM => 'Identity'
            ];
            
            foreach ($assetTypes as $typeKey => $typeName): ?>
                <li>
                    <a href="#" data-type="<?php echo htmlspecialchars($typeKey); ?>" class="provider-type-tab">
                        <?php echo htmlspecialchars($typeName); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </menu>

        <!-- Provider Configuration -->
        <div style="grid-column: 2 / -1;">
            <?php 
            $grouped = [];
            foreach ($providers as $providerKey => $provider) {
                $type = $provider['type'];
                if (!isset($grouped[$type])) {
                    $grouped[$type] = [];
                }
                $grouped[$type][$providerKey] = $provider;
            }
            
            foreach ($assetTypes as $typeKey => $typeName): 
                $typeProviders = $grouped[$typeKey] ?? [];
                $providersList = array_keys($typeProviders);
                ?>
                <div data-type-content="<?php echo htmlspecialchars($typeKey); ?>" style="display: none;">
                    <h3><?php echo htmlspecialchars($typeName); ?> Providers</h3>
                    
                    <section data-grid="auto" style="gap: var(--spacing-lg);">
                        <?php foreach ($typeProviders as $providerKey => $providerMeta): 
                            $isConfigured = isset($providersConfig[$providerKey]);
                            $fields = ProviderSettings::getFields($providerKey);
                            $values = $providersConfig[$providerKey]['values'] ?? [];
                            ?>
                            <article style="border: 1px solid var(--color-border); padding: var(--spacing-lg); border-radius: var(--radius-md);">
                                <header style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-lg); border-bottom: 1px solid var(--color-border); padding-bottom: var(--spacing-md);">
                                    <div style="padding: var(--spacing-sm); background-color: <?php echo htmlspecialchars($providerMeta['color']); ?>; border-radius: var(--radius-md); opacity: 0.7;">
                                        <?php Icon::render($providerMeta['icon'], 24, 24); ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <h4 style="margin: 0; color: var(--text-primary);">
                                            <?php echo htmlspecialchars($providerMeta['name']); ?>
                                        </h4>
                                        <p style="margin: var(--spacing-xs) 0 0 0; font-size: 0.75rem; color: var(--text-muted);">
                                            <?php echo htmlspecialchars($providerMeta['description']); ?>
                                        </p>
                                    </div>
                                    <label style="display: flex; align-items: center; gap: var(--spacing-sm); cursor: pointer; margin: 0;">
                                        <input type="checkbox" class="provider-toggle" data-provider="<?php echo htmlspecialchars($providerKey); ?>" <?php echo $isConfigured ? 'checked' : ''; ?>>
                                        <span style="font-size: 0.875rem;">Enabled</span>
                                    </label>
                                </header>

                                <div class="provider-fields" data-provider="<?php echo htmlspecialchars($providerKey); ?>" style="display: <?php echo $isConfigured ? 'block' : 'none'; ?>;">
                                    <section data-grid="2" style="gap: var(--spacing-md);">
                                        <?php foreach ($fields as $fieldKey => $field): 
                                            $value = $values[$fieldKey] ?? ($field['value'] ?? '');
                                            $fieldId = htmlspecialchars("{$providerKey}_{$fieldKey}");
                                            $name = htmlspecialchars($fieldKey);
                                            ?>
                                            <?php if ($field['type'] === 'checkbox'): ?>
                                                <div style="display: flex; align-items: center; grid-column: span 2;">
                                                    <label style="display: flex; align-items: center; gap: var(--spacing-sm); cursor: pointer; margin: 0;">
                                                        <input type="checkbox" id="<?php echo $fieldId; ?>" name="<?php echo $name; ?>" <?php echo $value === '1' ? 'checked' : ''; ?>>
                                                        <span><?php echo htmlspecialchars($field['label']); ?></span>
                                                    </label>
                                                </div>
                                            <?php elseif ($field['type'] === 'radio'): ?>
                                                <div style="grid-column: span 2;">
                                                    <label style="display: block; margin-bottom: var(--spacing-sm);"><?php echo htmlspecialchars($field['label']); ?></label>
                                                    <div style="display: flex; gap: var(--spacing-lg);">
                                                        <?php foreach ($field['options'] ?? [] as $optKey => $optLabel): ?>
                                                            <label style="display: flex; align-items: center; gap: var(--spacing-sm); cursor: pointer;">
                                                                <input type="radio" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($optKey); ?>" <?php echo $value === $optKey ? 'checked' : ''; ?>>
                                                                <?php echo htmlspecialchars($optLabel); ?>
                                                            </label>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div>
                                                    <label for="<?php echo $fieldId; ?>">
                                                        <?php echo htmlspecialchars($field['label']); ?>
                                                        <?php if ($field['required'] ?? false): ?>
                                                            <span style="color: var(--color-danger);">*</span>
                                                        <?php endif; ?>
                                                    </label>
                                                    <input 
                                                        type="<?php echo htmlspecialchars($field['type']); ?>" 
                                                        id="<?php echo $fieldId; ?>" 
                                                        name="<?php echo $name; ?>" 
                                                        value="<?php echo htmlspecialchars($value); ?>"
                                                        placeholder="<?php echo htmlspecialchars($field['placeholder'] ?? ''); ?>"
                                                        <?php echo $field['required'] ?? false ? 'required' : ''; ?>
                                                    >
                                                    <?php if (!empty($field['description'])): ?>
                                                        <small style="color: var(--text-muted);">
                                                            <?php echo htmlspecialchars($field['description']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </section>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </section>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</form>

<script>
document.querySelectorAll('.provider-type-tab').forEach(tab => {
    tab.addEventListener('click', (e) => {
        e.preventDefault();
        const type = e.target.dataset.type;
        document.querySelectorAll('[data-type-content]').forEach(el => {
            el.style.display = 'none';
        });
        document.querySelector(`[data-type-content="${type}"]`).style.display = 'block';
        
        // Update active tab
        document.querySelectorAll('.provider-type-tab').forEach(t => {
            t.dataset.active = t === e.target ? 'true' : '';
        });
    });
});

// Show first tab by default
document.querySelector('.provider-type-tab')?.click();

// Toggle provider fields visibility
document.querySelectorAll('.provider-toggle').forEach(toggle => {
    toggle.addEventListener('change', (e) => {
        const provider = e.target.dataset.provider;
        const fields = document.querySelector(`[data-provider="${provider}"].provider-fields`);
        if (fields) {
            fields.style.display = e.target.checked ? 'block' : 'none';
        }
    });
});
</script>

<style>
[role="tablist"] {
    position: sticky;
    top: 0;
}

.provider-type-tab {
    display: block;
    padding: var(--spacing-md);
    border-left: 3px solid transparent;
    cursor: pointer;
    transition: all 0.2s ease;
}

.provider-type-tab:hover {
    background-color: var(--color-background-secondary);
    border-left-color: var(--color-primary);
}

.provider-type-tab[data-active="true"] {
    background-color: var(--color-background-secondary);
    border-left-color: var(--color-primary);
    font-weight: 500;
}
</style>
