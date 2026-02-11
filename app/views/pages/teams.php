<div class="section">
    <div class="level">
        <div>
            <h2 class="title">Team Management</h2>
            <p class="subtitle">Organize people and assign functional aliases.</p>
        </div>
        <div class="level-right">
            <div class="level-item">
                <button class="button is-primary">
                    <span class="icon is-small">
                        <?php Icon::render('plus', 18, 18); ?>
                    </span>
                    <span>Create Team</span>
                </button>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="notification is-success">
        <a href="#" class="delete"></a>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if (empty($teams)): ?>
    <div class="section has-text-centered">
        <div class="block">
            <?php Icon::render('teams', 64, 64, 'stroke-width: 1;'); ?>
        </div>
        <p>No teams yet. Create your first team to get started.</p>
    </div>
<?php else: ?>
    <div class="columns is-multiline">
        <?php foreach ($teams as $team): ?>
            <div class="column is-half-tablet is-one-third-desktop">
                <div class="card">
                    <div class="card-header">
                        <div class="card-header-title">
                            <div>
                                <h3 class="title is-5"><?php echo htmlspecialchars($team['name']); ?></h3>
                                <p class="subtitle is-6"><?php echo htmlspecialchars($team['description']); ?></p>
                            </div>
                        </div>
                        <div class="card-header-icon is-hidden-mobile">
                            <div class="buttons are-small">
                                <button class="button is-ghost" title="Edit Team">
                                    <span class="icon is-small">
                                        <?php Icon::render('edit', 18, 18); ?>
                                    </span>
                                </button>
                                <button class="button is-ghost" title="Manage Aliases">
                                    <span class="icon is-small">
                                        <?php Icon::render('mail', 18, 18); ?>
                                    </span>
                                </button>
                                <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/teams/delete'); ?>" class="display-inline">
                                    <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($team['id']); ?>">
                                    <button type="submit" class="button is-ghost is-danger" title="Delete Team">
                                        <span class="icon is-small">
                                            <?php Icon::render('trash', 18, 18); ?>
                                        </span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-content">
                        <?php if (!empty($team['email_aliases'])): ?>
                            <div class="box has-background-info-light">
                                <p class="heading is-size-7">Active Email Aliases</p>
                                <div class="tags">
                                    <?php foreach ($team['email_aliases'] as $alias): ?>
                                        <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/teams/remove-alias'); ?>" class="display-inline">
                                            <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($team['id']); ?>">
                                            <input type="hidden" name="alias" value="<?php echo htmlspecialchars($alias); ?>">
                                            <span class="tag is-info">
                                                <?php echo htmlspecialchars($alias); ?>
                                                <button type="submit" class="button-reset is-hidden-mobile">×</button>
                                            </span>
                                        </form>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="block">
                            <div class="level">
                                <div class="level-left">
                                    <div class="level-item">
                                        <h4 class="title is-6">Members (<?php echo count($team['member_ids']); ?>)</h4>
                                    </div>
                                </div>
                                <div class="level-right">
                                    <div class="level-item">
                                        <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/teams/add-member'); ?>">
                                            <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($team['id']); ?>">
                                            <div class="field has-addons">
                                                <p class="control is-expanded">
                                                    <span class="select is-fullwidth">
                                                        <select name="employee_id">
                                                            <option value="">+ Add Member</option>
                                                            <?php foreach ($employees as $emp): ?>
                                                                <?php if (!in_array($emp['id'], $team['member_ids'])): ?>
                                                                    <option value="<?php echo htmlspecialchars($emp['id']); ?>">
                                                                        <?php echo htmlspecialchars($emp['full_name']); ?>
                                                                    </option>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </span>
                                                </p>
                                                <p class="control">
                                                    <button type="submit" class="button is-info">Add</button>
                                                </p>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="block">
                                <?php if (empty($team['member_ids'])): ?>
                                    <p class="has-text-grey-light is-italic">No members yet.</p>
                                <?php else: ?>
                                    <ul>
                                        <?php foreach ($team['member_ids'] as $memberId): ?>
                                            <?php 
                                                $member = array_values(array_filter($employees, fn($e) => $e['id'] === $memberId))[0] ?? null;
                                                if (!$member) continue;
                                            ?>
                                            <li class="level">
                                                <div class="level-left">
                                                    <div class="level-item">
                                                        <div class="is-flex is-align-items-center" class="gap-075">
                                                            <div class="image is-36x36">
                                                                <div class="is-flex is-align-items-center is-justify-content-center has-background-info has-text-white" class="w-100-h-100-rounded">
                                                                    <?php echo strtoupper(substr($member['full_name'], 0, 1)); ?>
                                                                </div>
                                                            </div>
                                                            <span><?php echo htmlspecialchars($member['full_name']); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="level-right">
                                                    <div class="level-item">
                                                        <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/teams/remove-member'); ?>" class="display-inline">
                                                            <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($team['id']); ?>">
                                                            <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($memberId); ?>">
                                                            <button type="submit" class="button is-ghost is-danger is-small">
                                                                <span class="icon is-small">
                                                                    <?php Icon::render('close', 14, 14); ?>
                                                                </span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <p class="card-footer-item has-text-grey-light has-text-centered">Sentiment analysis unavailable.</p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Create Team Modal -->
<div class="modal" data-create-team>
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Create New Team</p>
            <button class="delete" aria-label="close"></button>
        </header>
        <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/teams'); ?>">
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Team Name</label>
                    <div class="control">
                        <input class="input" type="text" name="name" required placeholder="e.g. Marketing Team">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button type="button" class="button">Cancel</button>
                <button type="submit" class="button is-primary">Create Team</button>
            </footer>
        </form>
    </div>
</div>

<!-- Add Alias Modal -->
<div class="modal" data-add-alias>
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Add Email Alias</p>
            <button class="delete" aria-label="close"></button>
        </header>
        <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/teams/add-alias'); ?>">
            <section class="modal-card-body">
                <input type="hidden" name="team_id">
                <p class="is-size-7">This will create an alias in your configured Mail service.</p>
                
                <div class="field" class="mt-1">
                    <label class="label">Email Alias</label>
                    <div class="control">
                        <input class="input" type="email" name="alias" required placeholder="e.g. support@example.com">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button type="button" class="button">Cancel</button>
                <button type="submit" class="button is-primary">Create Alias</button>
            </footer>
        </form>
    </div>
</div>

<!-- Edit Team Modal -->
<div class="modal" data-edit-team>
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Edit Team</p>
            <button class="delete" aria-label="close"></button>
        </header>
        <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/teams/update'); ?>">
            <section class="modal-card-body">
                <input type="hidden" name="team_id">
                <div class="field">
                    <label class="label">Team Name</label>
                    <div class="control">
                        <input class="input" type="text" name="name" required placeholder="e.g. Marketing Team">
                    </div>
                </div>
                <div class="field">
                    <label class="label">Description</label>
                    <div class="control">
                        <input class="input" type="text" name="description" placeholder="Team description">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button type="button" class="button">Cancel</button>
                <button type="submit" class="button is-primary">Save Changes</button>
            </footer>
        </form>
    </div>
</div>

