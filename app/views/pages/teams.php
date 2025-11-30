<header>
    <div>
        <h2>Team Management</h2>
        <p>Organize people and assign functional aliases.</p>
    </div>
    <button onclick="document.querySelector('dialog[data-create-team]').showModal()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Create Team
    </button>
</header>

<?php if (!empty($message)): ?>
    <output data-type="success"><?php echo htmlspecialchars($message); ?></output>
<?php endif; ?>

<?php if (empty($teams)): ?>
    <section data-empty>
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <circle cx="19" cy="11" r="2"></circle>
        </svg>
        <p>No teams yet. Create your first team to get started.</p>
    </section>
<?php else: ?>
    <section data-grid="2">
        <?php foreach ($teams as $team): ?>
            <article>
                <header>
                    <div>
                        <h3><?php echo htmlspecialchars($team['name']); ?></h3>
                        <p style="margin: 0; font-size: 0.875rem;"><?php echo htmlspecialchars($team['description']); ?></p>
                    </div>
                    <button data-variant="ghost" data-size="icon" onclick="openAliasModal('<?php echo htmlspecialchars($team['id']); ?>', '<?php echo htmlspecialchars($team['name']); ?>')">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </button>
                </header>

                <?php if (!empty($team['email_aliases'])): ?>
                    <div style="background-color: var(--color-primary-light); padding: var(--spacing-md); border-radius: var(--radius-md); margin: var(--spacing-md) 0;">
                        <p style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-primary); margin-bottom: var(--spacing-sm);">Active Email Aliases</p>
                        <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-xs);">
                            <?php foreach ($team['email_aliases'] as $alias): ?>
                                <form method="POST" action="/teams/remove-alias" style="display: inline;">
                                    <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($team['id']); ?>">
                                    <input type="hidden" name="alias" value="<?php echo htmlspecialchars($alias); ?>">
                                    <mark>
                                        <?php echo htmlspecialchars($alias); ?>
                                        <button type="submit" data-variant="ghost" data-size="sm" style="padding: 0; margin-left: var(--spacing-xs);" onclick="return confirm('Remove this alias?')">×</button>
                                    </mark>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-sm);">
                        <h4 style="margin: 0;">Members (<?php echo count($team['member_ids']); ?>)</h4>
                        <form method="POST" action="/teams/add-member" style="display: flex; gap: var(--spacing-xs);">
                            <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($team['id']); ?>">
                            <select name="employee_id" onchange="this.form.submit()" style="font-size: 0.75rem; padding: var(--spacing-xs);">
                                <option value="">+ Add Member</option>
                                <?php foreach ($employees as $emp): ?>
                                    <?php if (!in_array($emp['id'], $team['member_ids'])): ?>
                                        <option value="<?php echo htmlspecialchars($emp['id']); ?>">
                                            <?php echo htmlspecialchars($emp['full_name']); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>

                    <ul>
                        <?php if (empty($team['member_ids'])): ?>
                            <li style="color: var(--text-muted); font-style: italic; font-size: 0.875rem;">No members yet.</li>
                        <?php else: ?>
                            <?php foreach ($team['member_ids'] as $memberId): ?>
                                <?php 
                                    $member = array_values(array_filter($employees, fn($e) => $e['id'] === $memberId))[0] ?? null;
                                    if (!$member) continue;
                                ?>
                                <li style="justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                                        <figure data-avatar="sm">
                                            <?php echo strtoupper(substr($member['full_name'], 0, 1)); ?>
                                        </figure>
                                        <span style="font-size: 0.875rem;"><?php echo htmlspecialchars($member['full_name']); ?></span>
                                    </div>
                                    <form method="POST" action="/teams/remove-member" style="display: inline;">
                                        <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($team['id']); ?>">
                                        <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($memberId); ?>">
                                        <button type="submit" data-variant="ghost" data-size="icon" style="padding: 0;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <footer style="background-color: var(--bg-tertiary); padding: var(--spacing-md); margin: var(--spacing-lg) calc(var(--spacing-lg) * -1) calc(var(--spacing-lg) * -1); border-radius: 0 0 var(--radius-xl) var(--radius-xl);">
                    <p style="text-align: center; color: var(--text-muted); font-size: 0.875rem; margin: 0;">Sentiment analysis unavailable.</p>
                </footer>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<!-- Create Team Modal -->
<dialog data-create-team>
    <article>
        <header>
            <h3>Create New Team</h3>
            <button type="button" data-variant="ghost" data-size="icon" onclick="this.closest('dialog').close()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </header>

        <form method="POST" action="/teams">
            <div>
                <label>Team Name</label>
                <input type="text" name="name" required placeholder="e.g. Marketing Team">
            </div>

            <footer>
                <button type="button" data-variant="secondary" onclick="this.closest('dialog').close()">Cancel</button>
                <button type="submit">Create Team</button>
            </footer>
        </form>
    </article>
</dialog>

<!-- Add Alias Modal -->
<dialog data-add-alias>
    <article>
        <header>
            <h3>Add Email Alias</h3>
            <button type="button" data-variant="ghost" data-size="icon" onclick="this.closest('dialog').close()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </header>

        <form method="POST" action="/teams/add-alias">
            <input type="hidden" name="team_id">
            <p style="font-size: 0.875rem;">This will create an alias in your configured Mail service.</p>
            
            <div>
                <label>Email Alias</label>
                <input type="email" name="alias" required placeholder="e.g. support@example.com">
            </div>

            <footer>
                <button type="button" data-variant="secondary" onclick="this.closest('dialog').close()">Cancel</button>
                <button type="submit">Create Alias</button>
            </footer>
        </form>
    </article>
</dialog>

<script>
function openAliasModal(teamId, teamName) {
    const dialog = document.querySelector('dialog[data-add-alias]');
    dialog.querySelector('[name="team_id"]').value = teamId;
    dialog.querySelector('h3').textContent = 'Add Email Alias for ' + teamName;
    dialog.showModal();
}
</script>
