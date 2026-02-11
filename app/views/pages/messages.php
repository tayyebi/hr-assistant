<div class="section">
    <div class="level">
        <div>
            <h2 class="title">Conversations</h2>
            <p class="subtitle">Direct messages with employees.</p>
        </div>
    </div>
</div>

<?php if (!empty($flashMessage)): ?>
    <div class="notification is-success">
        <a href="#" class="delete"></a>
        <?php echo htmlspecialchars($flashMessage); ?>
    </div>
<?php endif; ?>

<?php if (empty($messagingInstances)): ?>
    <div class="section has-text-centered">
        <div class="block">
            <?php \App\Core\Icon::render('message-circle', 64, 64, 'stroke-width: 1;'); ?>
        </div>
        <h3>No Messaging Providers Configured</h3>
        <p>Add a messaging provider (Email or Messenger like Telegram, Slack) in Settings to send direct messages to employees.</p>
        <a href="<?php echo \App\Core\UrlHelper::workspace('/settings'); ?>" class="button is-primary">
            Go to Settings
        </a>
    </div>
<?php else: ?>

<div class="columns is-gapless h-calc-100vh-minus-300">
    <!-- Sidebar -->
    <div class="column is-3-tablet is-one-quarter-desktop border-right-light display-flex flex-col">
        <div class="mb-0 rounded-0">
            <div class="tabs is-toggle">
                <ul class="mb-0">
                    <li <?php echo $view === 'chats' ? 'class="is-active"' : ''; ?>>
                        <a href="<?php echo \App\Core\UrlHelper::withQuery(\App\Core\UrlHelper::workspace('/messages'), ['view' => 'chats']); ?>">
                            <span class="icon is-small">
                                <?php Icon::render('messages', 14, 14); ?>
                            </span>
                            <span>Chats</span>
                        </a>
                    </li>
                    <li <?php echo $view === 'inbox' ? 'class="is-active"' : ''; ?>>
                        <a href="<?php echo \App\Core\UrlHelper::withQuery(\App\Core\UrlHelper::workspace('/messages'), ['view' => 'inbox']); ?>">
                            <span class="icon is-small">
                                <?php Icon::render('inbox', 14, 14); ?>
                            </span>
                            <span>Inbox (<?php echo count($unassigned); ?>)</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">
            <?php if ($view === 'chats'): ?>
                <?php foreach ($employees as $emp): ?>
                    <div class="border-bottom-light">
                        <div class="m-0 rounded-0 p-1">
                            <div class="gap-1 mb-1">
                                <div class="image is-48x48">
                                    <div class="w-100-h-100-rounded">
                                        <?php echo strtoupper(substr($emp['full_name'], 0, 1)); ?>
                                    </div>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($emp['full_name']); ?></strong>
                                    <p class="has-text-grey-light is-size-7"><?php echo htmlspecialchars($emp['position']); ?></p>
                                </div>
                            </div>
                            
                            <!-- Channel list for this employee -->
                            <div class="pl-2">
                                <?php if (!empty($emp['available_channels'])): ?>
                                    <!-- All Channels option -->
                                    <a href="<?php echo \App\Core\UrlHelper::withQuery(\App\Core\UrlHelper::workspace('/messages'), ['employee' => $emp['id'], 'channel' => 'all']); ?>" 
                                       class="gap-05 py-05 no-underline text-inherit <?php echo ($selectedEmployee && $selectedEmployee['id'] === $emp['id'] && $selectedChannel === 'all') ? 'has-text-primary has-text-weight-bold' : ''; ?>">
                                        <?php \App\Core\Icon::render('list', 12, 12); ?>
                                        <span class="is-size-7">All Channels</span>
                                    </a>
                                    
                                    <!-- Individual channels -->
                                    <?php foreach ($emp['available_channels'] as $channel): ?>
                                        <a href="<?php echo \App\Core\UrlHelper::withQuery(\App\Core\UrlHelper::workspace('/messages'), ['employee' => $emp['id'], 'channel' => $channel]); ?>" 
                                           class="gap-05 py-05 no-underline text-inherit <?php echo ($selectedEmployee && $selectedEmployee['id'] === $emp['id'] && $selectedChannel === $channel) ? 'has-text-primary has-text-weight-bold' : ''; ?>">
                                            <?php if ($channel === 'email'): ?>
                                                <?php \App\Core\Icon::render('mail', 12, 12); ?>
                                            <?php elseif ($channel === 'telegram'): ?>
                                                <?php \App\Core\Icon::render('message-circle', 12, 12); ?>
                                            <?php else: ?>
                                                <?php \App\Core\Icon::render('hash', 12, 12); ?>
                                            <?php endif; ?>
                                            <span class="is-size-7"><?php echo ucfirst($channel); ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="is-size-7 has-text-grey-light">No channels available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-1">
                    <?php if (empty($unassigned)): ?>
                        <p class="has-text-centered has-text-grey-light">Inbox is empty.</p>
                    <?php else: ?>
                        <?php foreach ($unassigned as $msg): ?>
                            <div class="box">
                                <div class="mb-05">
                                    <strong class="gap-05 is-size-7">
                                        <?php if ($msg['channel'] === 'email'): ?>
                                            <?php Icon::render('mail', 12, 12, 'stroke: #f14668;'); ?>
                                        <?php else: ?>
                                            <?php Icon::render('hash', 12, 12, 'stroke: #3273dc;'); ?>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($msg['sender_name']); ?>
                                    </strong>
                                    <small class="has-text-grey-light"><?php echo date('H:i', strtotime($msg['timestamp'])); ?></small>
                                </div>
                                <p class="message-text">
                                    <?php if (!empty($msg['subject'])): ?>
                                        <strong><?php echo htmlspecialchars($msg['subject']); ?>:</strong>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars(substr($msg['text'], 0, 100)); ?>...
                                </p>
                                <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/messages/assign/'); ?>" class="display-flex gap-05 flex-wrap">
                                    <input type="hidden" name="unassigned_id" value="<?php echo htmlspecialchars($msg['id']); ?>">
                                    <div class="control is-expanded">
                                        <span class="select is-fullwidth is-small">
                                            <select name="employee_id" required>
                                                <option value="">Select employee...</option>
                                                <?php foreach ($employees as $emp): ?>
                                                    <?php if (!empty($emp['available_channels'])): ?>
                                                        <option value="<?php echo htmlspecialchars($emp['id']); ?>">
                                                            <?php echo htmlspecialchars($emp['full_name']); ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </span>
                                    </div>
                                    <div class="control is-expanded">
                                        <span class="select is-fullwidth is-small">
                                            <select name="channel" required>
                                                <option value="">Select channel...</option>
                                                <option value="email">Email</option>
                                                <option value="telegram">Telegram</option>
                                                <option value="whatsapp">WhatsApp</option>
                                                <option value="slack">Slack</option>
                                                <option value="teams">Teams</option>
                                            </select>
                                        </span>
                                    </div>
                                    <button type="submit" class="button is-small is-primary">Assign</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="display-flex flex-col">
        <?php if ($selectedEmployee): ?>
            <div class="box" class="m-0 rounded-0 border-bottom-light p-1">
                <div class="level">
                    <div class="level-left">
                        <div class="level-item">
                            <div>
                                <h3 class="title is-5"><?php echo htmlspecialchars($selectedEmployee['full_name']); ?></h3>
                                <div class="gap-05 mt-05">
                                    <?php if ($selectedChannel === 'all'): ?>
                                        <span class="tag is-warning">All Channels</span>
                                    <?php else: ?>
                                        <span class="tag is-info">
                                            <?php echo ucfirst($selectedChannel); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Channel filter dropdown -->
                    <?php if (!empty($availableChannels) && count($availableChannels) > 1): ?>
                        <div class="level-right">
                            <div class="level-item">
                                <form method="GET" action="<?php echo \App\Core\UrlHelper::workspace('/messages'); ?>" class="display-flex gap-05 items-center">
                                    <input type="hidden" name="employee" value="<?php echo htmlspecialchars($selectedEmployee['id']); ?>">
                                    <span class="select is-small">
                                        <select name="channel">
                                            <option value="all" <?php echo $selectedChannel === 'all' ? 'selected' : ''; ?>>
                                                All Channels
                                            </option>
                                            <?php foreach ($availableChannels as $channel): ?>
                                                <option value="<?php echo htmlspecialchars($channel); ?>" <?php echo $selectedChannel === $channel ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($channel); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </span>
                                    <button type="submit" class="button is-small is-light">Filter</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-1">
                <?php foreach ($messages as $msg): ?>
                    <div class="mb-1 display-flex <?php echo $msg['sender'] === 'hr' ? 'justify-end' : 'justify-start'; ?>">
                        <div class="max-w-70">
                            <?php if ($selectedChannel === 'all'): ?>
                                <div class="gap-025 mb-025">
                                    <?php if ($msg['channel'] === 'email'): ?>
                                        <?php \App\Core\Icon::render('mail', 12, 12); ?>
                                    <?php elseif ($msg['channel'] === 'telegram'): ?>
                                        <?php \App\Core\Icon::render('message-circle', 12, 12); ?>
                                    <?php else: ?>
                                        <?php \App\Core\Icon::render('hash', 12, 12); ?>
                                    <?php endif; ?>
                                    <small class="is-size-7"><?php echo ucfirst($msg['channel']); ?></small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="box message-bubble <?php echo $msg['sender'] === 'hr' ? 'sent' : 'received'; ?>">
                                <?php if (!empty($msg['subject'])): ?>
                                    <strong class="display-block mb-05"><?php echo htmlspecialchars($msg['subject']); ?></strong>
                                <?php endif; ?>
                                <p class="m-0"><?php echo htmlspecialchars($msg['text']); ?></p>
                            </div>
                            <small class="has-text-grey-light"><?php echo date('M j, Y H:i', strtotime($msg['timestamp'])); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="m-0 rounded-0 border-top-light p-1">
                <?php if ($selectedChannel !== 'all'): ?>
                    <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/messages/send/'); ?>" class="gap-05">
                        <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($selectedEmployee['id']); ?>">
                        <input type="hidden" name="channel" value="<?php echo htmlspecialchars($selectedChannel); ?>">
                        
                        <?php if ($selectedChannel === 'email'): ?>
                            <input class="input is-small" type="text" name="subject" placeholder="Subject (optional)">
                        <?php endif; ?>
                        
                        <div class="field is-grouped">
                            <p class="control is-expanded">
                                <textarea class="textarea is-small" name="text" placeholder="Send a <?php echo $selectedChannel; ?> message..." rows="2" required></textarea>
                            </p>
                            <p class="control">
                                <button type="submit" class="button is-small is-primary">
                                    <span class="icon is-small">
                                        <?php \App\Core\Icon::render('send', 18, 18); ?>
                                    </span>
                                </button>
                            </p>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="has-text-centered has-text-grey-light">
                        <small>Select a specific channel to send messages</small>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="section has-text-centered">
                <div class="block">
                    <?php Icon::render('messages', 64, 64, 'stroke-width: 1;'); ?>
                </div>
                <h3>Select a conversation</h3>
                <p>Choose an employee from the list to start messaging.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>