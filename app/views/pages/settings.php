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
                        <form method="POST" action="/settings/providers/delete" style="display:inline; margin-left: var(--spacing-md);">
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
        <form method="POST" action="/settings/providers">
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
                <div style="grid-column: span 3;">
                    <label>Settings (JSON)</label>
                    <textarea name="settings" placeholder='{"gitlab_url":"https://gitlab.example","gitlab_token":"token"}' style="min-height: 120px; width: 100%;"></textarea>
                    <small style="color: var(--text-muted);">Optional JSON with provider-specific settings. These will be used when querying provider APIs.</small>
                </div>
            </section>
            <footer style="margin-top: var(--spacing-md);">
                <button type="submit">Add Provider Instance</button>
            </footer>
        </form>
    </article>
</section>

<script>
// Provider instance form: filter provider list based on selected type
const typeSelect = document.querySelector('select[name="type"]');
const providerSelect = document.getElementById('provider-select');
if (typeSelect && providerSelect) {
    typeSelect.addEventListener('change', (e) => {
        const val = e.target.value;
        providerSelect.disabled = !val;
        for (const opt of providerSelect.querySelectorAll('option[data-type]')) {
            opt.style.display = (opt.dataset.type === val) ? 'block' : 'none';
        }
        providerSelect.value = '';
    });

    // Click on provider card to prefill the create form
    document.querySelectorAll('#providers-grid article[data-provider]').forEach(card => {
        card.addEventListener('click', () => {
            const prov = card.dataset.provider;
            const type = card.dataset.type;
            typeSelect.value = type;
            // trigger change to enable provider select and filter
            typeSelect.dispatchEvent(new Event('change'));
            providerSelect.value = prov;
            // focus name input and scroll into view
            document.querySelector('input[name="name"]').focus();
            document.querySelector('input[name="name"]').scrollIntoView({behavior: 'smooth', block: 'center'});
        });
    });
}
</script>

<style>
[role="tablist"] {
    position: sticky;
    top: 0;
}

/* Simplified settings page - provider tabs removed */
</style>
