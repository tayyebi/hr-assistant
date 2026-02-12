<?php $layout = 'app'; ?>
<div class="page-header">
    <h1 class="page-title">Messenger — <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></h1>
    <a href="<?= $prefix ?>/messenger" class="btn btn-sm">Back</a>
</div>

<div class="messenger-layout">
    <aside class="messenger-contacts">
        <?php foreach (($employees ?? []) as $e): ?>
        <a class="contact-item <?= ($e['id'] === $employee['id']) ? 'active' : '' ?>" href="<?= $prefix ?>/messenger/employee/<?= $e['id'] ?>">
            <div class="contact-name"><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></div>
        </a>
        <?php endforeach; ?>
    </aside>

    <div class="messenger-main">
        <div class="chat-messages">
            <?php foreach (($messages ?? []) as $m): ?>
            <div class="chat-msg chat-msg-<?= $m['direction'] ?>">
                <div class="chat-msg-body">
                    <span class="msg-channel-tag <?= 'msg-' . $m['channel'] ?>"><?= htmlspecialchars(ucfirst($m['channel'])) ?></span>
                    <?= nl2br(htmlspecialchars($m['body'])) ?>
                </div>
                <div class="chat-msg-time"><?= $m['created_at'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <form method="post" action="<?= $prefix ?>/messenger/employee/<?= $employee['id'] ?>/send" class="chat-compose">
            <select name="channel" class="field-input field-sm" required>
                <?php foreach ($available as $ch): ?>
                <option value="<?= $ch ?>"><?= htmlspecialchars(ucfirst($ch)) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="body" class="field-input" placeholder="Type a message…" required>
            <button type="submit" class="btn btn-primary btn-sm">Send</button>
        </form>
    </div>
</div>

<script>
// simple poller to refresh new messages
(() => {
    const employeeId = <?= (int)$employee['id'] ?>;
    let last = '';
    setInterval(async () => {
        try {
            const url = '<?= $prefix ?>/messenger/api/messages/' + employeeId + (last ? '?since=' + encodeURIComponent(last) : '');
            const r = await fetch(url, { credentials: 'same-origin' });
            if (!r.ok) return;
            const msgs = await r.json();
            if (!msgs.length) return;
            const container = document.querySelector('.chat-messages');
            for (const m of msgs) {
                const div = document.createElement('div');
                div.className = 'chat-msg chat-msg-' + m.direction;
                div.innerHTML = '<div class="chat-msg-body"><span class="msg-channel-tag msg-' + m.channel + '">' + (m.channel.charAt(0).toUpperCase() + m.channel.slice(1)) + '</span> ' + m.body.replace(/\n/g, '<br>') + '</div><div class="chat-msg-time">' + m.created_at + '</div>';
                container.appendChild(div);
                last = m.created_at;
            }
            container.scrollTop = container.scrollHeight;
        } catch (e) { /* ignore */ }
    }, 10000);
})();
</script>
