<?php $layout = 'app'; ?>
<div class="page-header">
    <h1 class="page-title">Telegram</h1>
    <a href="<?= $prefix ?>/telegram/settings" class="btn btn-sm">Settings</a>
</div>
<?php if (empty($chats)): ?>
<p class="text-muted">No chats yet. Configure the bot token in settings and set up the webhook.</p>
<?php else: ?>
<table class="table">
<thead><tr><th>Chat ID</th><th>Username</th><th>Name</th><th>Employee</th><th>Messages</th><th></th></tr></thead>
<tbody>
<?php foreach ($chats as $c): ?>
<tr>
    <td><code><?= htmlspecialchars($c['chat_id']) ?></code></td>
    <td><?= htmlspecialchars($c['username'] ?? '') ?></td>
    <td><?= htmlspecialchars($c['first_name'] ?? '') ?></td>
    <td><?= $c['emp_first'] ? htmlspecialchars($c['emp_first'] . ' ' . $c['emp_last']) : '<span class="text-muted">Unassigned</span>' ?></td>
    <td><?= $c['msg_count'] ?></td>
    <td><a href="<?= $prefix ?>/telegram/chat/<?= htmlspecialchars($c['chat_id']) ?>" class="btn btn-sm">Open</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
