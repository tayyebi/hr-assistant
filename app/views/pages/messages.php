<header>
    <div>
        <h2>Conversations</h2>
        <p>Direct messages with employees.</p>
    </div>
</header>

<?php if (!empty($flashMessage)): ?>
    <output data-type="success"><?php echo htmlspecialchars($flashMessage); ?></output>
<?php endif; ?>

<section data-chat>
    <!-- Sidebar -->
    <aside>
        <div style="padding: var(--spacing-md); border-bottom: 1px solid var(--border-color);">
            <menu role="tablist" style="margin: 0; border: none;">
                <li>
                    <a href="/messages?view=chats" <?php echo $view === 'chats' ? 'data-active="true"' : ''; ?>>
                        <?php Icon::render('messages', 14, 14); ?>
                        Chats
                    </a>
                </li>
                <li>
                    <a href="/messages?view=inbox" <?php echo $view === 'inbox' ? 'data-active="true"' : ''; ?>>
                        <?php Icon::render('inbox', 14, 14); ?>
                        Inbox (<?php echo count($unassigned); ?>)
                    </a>
                </li>
            </menu>
        </div>

        <?php if ($view === 'chats'): ?>
            <div style="overflow-y: auto;">
                <?php foreach ($reachableEmployees as $emp): ?>
                    <a href="/messages?employee=<?php echo urlencode($emp['id']); ?>" 
                       style="display: flex; align-items: center; gap: var(--spacing-md); padding: var(--spacing-md); text-decoration: none; color: inherit; <?php echo ($selectedEmployee && $selectedEmployee['id'] === $emp['id']) ? 'background-color: var(--color-primary-light);' : ''; ?>">
                        <figure data-avatar>
                            <?php echo strtoupper(substr($emp['full_name'], 0, 1)); ?>
                        </figure>
                        <div>
                            <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($emp['full_name']); ?></strong>
                            <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($emp['position']); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="padding: var(--spacing-md); overflow-y: auto; background-color: var(--bg-tertiary);">
                <?php if (empty($unassigned)): ?>
                    <p style="text-align: center; color: var(--text-muted); padding: var(--spacing-xl);">Inbox is empty.</p>
                <?php else: ?>
                    <?php foreach ($unassigned as $msg): ?>
                        <article style="margin-bottom: var(--spacing-md);">
                            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-sm); padding: 0; border: none;">
                                <strong style="display: flex; align-items: center; gap: var(--spacing-sm); font-size: 0.875rem;">
                                    <?php if ($msg['channel'] === 'email'): ?>
                                        <?php Icon::render('mail', 12, 12, 'stroke: var(--color-warning);'); ?>
                                    <?php else: ?>
                                        <?php Icon::render('hash', 12, 12, 'stroke: var(--color-primary);'); ?>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($msg['sender_name']); ?>
                                </strong>
                                <small style="color: var(--text-muted);"><?php echo date('H:i', strtotime($msg['timestamp'])); ?></small>
                            </header>
                            <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: var(--spacing-md);">
                                <?php if (!empty($msg['subject'])): ?>
                                    <strong><?php echo htmlspecialchars($msg['subject']); ?>:</strong>
                                <?php endif; ?>
                                <?php echo htmlspecialchars(substr($msg['text'], 0, 100)); ?>...
                            </p>
                            <form method="POST" action="<?php echo View::workspaceUrl('/messages/assign/'); ?>" style="display: flex; gap: var(--spacing-sm);">
                                <input type="hidden" name="message_id" value="<?php echo htmlspecialchars($msg['id']); ?>">
                                <select name="employee_id" style="flex: 1; font-size: 0.75rem;">
                                    <option value="">Assign to...</option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?php echo htmlspecialchars($emp['id']); ?>">
                                            <?php echo htmlspecialchars($emp['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" data-size="sm">Assign</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </aside>

    <!-- Chat Area -->
    <article>
        <?php if ($selectedEmployee): ?>
            <header>
                <div>
                    <h3 style="margin: 0;"><?php echo htmlspecialchars($selectedEmployee['full_name']); ?></h3>
                    <div style="display: flex; align-items: center; gap: var(--spacing-sm); margin-top: var(--spacing-xs);">
                        <?php 
                            $channel = !empty($selectedEmployee['telegram_chat_id']) ? 'telegram' : 'email';
                        ?>
                        <mark data-status="<?php echo $channel === 'telegram' ? 'processing' : 'pending'; ?>">
                            <?php echo ucfirst($channel); ?>
                        </mark>
                        <small style="color: var(--text-muted);">
                            <?php echo $channel === 'telegram' ? '@' . $selectedEmployee['telegram_chat_id'] : $selectedEmployee['email']; ?>
                        </small>
                    </div>
                </div>
            </header>

            <main>
                <?php foreach ($messages as $msg): ?>
                    <div data-bubble="<?php echo $msg['sender'] === 'hr' ? 'sent' : 'received'; ?>">
                        <?php if (!empty($msg['subject'])): ?>
                            <strong style="display: block; margin-bottom: var(--spacing-xs);"><?php echo htmlspecialchars($msg['subject']); ?></strong>
                        <?php endif; ?>
                        <p style="margin: 0;"><?php echo htmlspecialchars($msg['text']); ?></p>
                        <time><?php echo date('M j, Y H:i', strtotime($msg['timestamp'])); ?></time>
                    </div>
                <?php endforeach; ?>
            </main>

            <footer>
                <form method="POST" action="<?php echo View::workspaceUrl('/messages/send/'); ?>">
                    <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($selectedEmployee['id']); ?>">
                    <input type="hidden" name="channel" value="<?php echo $channel; ?>">
                    
                    <?php if ($channel === 'email'): ?>
                        <input type="text" name="subject" placeholder="Subject (optional)" style="margin-bottom: var(--spacing-sm);">
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: var(--spacing-sm);">
                        <textarea name="text" placeholder="Send a <?php echo $channel; ?> message..." rows="2" style="flex: 1; resize: none;" required></textarea>
                        <button type="submit" style="align-self: flex-end;">
                            <?php Icon::render('send', 18, 18); ?>
                        </button>
                    </div>
                </form>
            </footer>
        <?php else: ?>
            <section data-empty style="flex: 1;">
                <?php Icon::render('messages', 64, 64, 'stroke-width: 1;'); ?>
                <h3>Select a conversation</h3>
                <p>Choose an employee from the list to start messaging.</p>
            </section>
        <?php endif; ?>
    </article>
</section>
