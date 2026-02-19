<?php $layout = 'app'; ?>

<header class="page-header">
    <div>
        <h1 class="page-title">
            Messenger — <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
        </h1>
    </div>
    <a href="<?= $prefix ?>/messenger" class="btn btn-sm" title="Back to messenger">
        ← Back
    </a>
</header>

<section class="messenger-layout">
    <aside class="messenger-contacts" role="region" aria-label="Employee list">
        <nav aria-label="Employee navigation">
            <ul class="sidebar-nav" style="margin: 0; padding: 0;">
                <?php foreach (($employees ?? []) as $e): ?>
                <li style="padding: 0; margin: 0;">
                    <a 
                        class="contact-item <?= ($e['id'] === $employee['id']) ? 'active' : '' ?>" 
                        href="<?= $prefix ?>/messenger/employee/<?= $e['id'] ?>"
                        title="Message <?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?>"
                        <?= ($e['id'] === $employee['id']) ? 'aria-current="page"' : '' ?>
                    >
                        <span class="contact-name">
                            <?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?>
                        </span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </aside>

    <main class="messenger-main">
        <article class="card" style="height: 100%; display: flex; flex-direction: column;">
            <div class="chat-messages" role="region" aria-label="Chat messages" aria-live="polite">
                <?php if (empty($messages)): ?>
                <div style="text-align: center; padding: 40px; color: var(--muted);">
                    <p>No messages yet. Start a conversation below.</p>
                </div>
                <?php else: ?>
                <?php foreach ($messages as $m): ?>
                <div class="chat-msg chat-msg-<?= htmlspecialchars($m['direction']) ?>" role="article">
                    <div class="chat-msg-body">
                        <span class="msg-channel-tag msg-<?= htmlspecialchars($m['channel']) ?>" title="Sent via <?= htmlspecialchars(ucfirst($m['channel'])) ?>">
                            <?= htmlspecialchars(ucfirst($m['channel'])) ?>
                        </span>
                        <?= nl2br(htmlspecialchars($m['body'])) ?>
                    </div>
                    <time class="chat-msg-time"><?= htmlspecialchars($m['created_at']) ?></time>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form 
                method="post" 
                action="<?= $prefix ?>/messenger/employee/<?= $employee['id'] ?>/send" 
                class="chat-compose"
                style="margin-top: auto; padding-top: 12px; border-top: 1px solid var(--border);"
                aria-label="Send message"
            >
                <select 
                    name="channel" 
                    class="field-input" 
                    required
                    aria-label="Message channel"
                    title="Choose how to send the message"
                >
                    <option value="">Select channel…</option>
                    <?php foreach ($available as $ch): ?>
                    <option value="<?= htmlspecialchars($ch) ?>">
                        <?= htmlspecialchars(ucfirst($ch)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <input 
                    type="text" 
                    name="body" 
                    class="field-input" 
                    placeholder="Type your message…" 
                    required
                    aria-label="Message content"
                >
                <button type="submit" class="btn btn-primary" title="Send message">
                    Send
                </button>
            </form>
        </article>
    </main>
</section>

<script>
// Auto-refresh chat messages every 10 seconds
(function() {
    const employeeId = <?= (int)$employee['id'] ?>;
    const container = document.querySelector('.chat-messages');
    let lastTime = '';

    function pollMessages() {
        const url = '<?= $prefix ?>/messenger/api/messages/' + employeeId + (lastTime ? '?since=' + encodeURIComponent(lastTime) : '');
        
        fetch(url, { credentials: 'same-origin' })
            .then(response => {
                if (!response.ok) throw new Error('Network response failed');
                return response.json();
            })
            .then(messages => {
                if (!Array.isArray(messages) || messages.length === 0) return;
                
                messages.forEach(msg => {
                    const div = document.createElement('div');
                    div.className = 'chat-msg chat-msg-' + msg.direction;
                    div.setAttribute('role', 'article');
                    
                    const body = document.createElement('div');
                    body.className = 'chat-msg-body';
                    body.innerHTML = '<span class="msg-channel-tag msg-' + msg.channel + '">' + 
                        (msg.channel.charAt(0).toUpperCase() + msg.channel.slice(1)) + 
                        '</span> ' + 
                        msg.body.replace(/\n/g, '<br>');
                    
                    const time = document.createElement('time');
                    time.className = 'chat-msg-time';
                    time.textContent = msg.created_at;
                    
                    div.appendChild(body);
                    div.appendChild(time);
                    container.appendChild(div);
                    lastTime = msg.created_at;
                });
                
                container.scrollTop = container.scrollHeight;
            })
            .catch(error => console.warn('Message polling error:', error));
    }

    setInterval(pollMessages, 10000);
})();
</script>
