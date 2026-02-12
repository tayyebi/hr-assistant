<?php $layout = 'app'; ?>
<div class="page-header">
    <h1 class="page-title">Chat <?= htmlspecialchars($chat['chat_id'] ?? '') ?></h1>
    <span class="text-muted"><?= htmlspecialchars($chat['username'] ?? '') ?></span>
</div>

<div class="chat-assign">
    <form method="post" action="<?= $prefix ?>/telegram/chat/<?= htmlspecialchars($chat['chat_id'] ?? '') ?>/assign" class="inline-form">
        <select name="employee_id" class="field-input field-sm">
            <option value="">Assign to employee…</option>
<?php foreach (($employees ?? []) as $e): ?>
            <option value="<?= $e['id'] ?>" <?= (($chat['employee_id'] ?? '') == $e['id']) ? 'selected' : '' ?>><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></option>
<?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-sm">Assign</button>
    </form>
</div>

<div class="chat-messages">
<?php foreach (($messages ?? []) as $m): ?>
    <div class="chat-msg chat-msg-<?= $m['direction'] ?>">
        <div class="chat-msg-body"><?= htmlspecialchars($m['body']) ?></div>
        <div class="chat-msg-time"><?= $m['created_at'] ?></div>
    </div>
<?php endforeach; ?>
</div>

<form method="post" action="<?= $prefix ?>/telegram/chat/<?= htmlspecialchars($chat['chat_id'] ?? '') ?>/send" class="chat-compose">
    <input type="text" name="body" class="field-input" placeholder="Type a message…" required autofocus>
    <button type="submit" class="btn btn-primary btn-sm">Send</button>
</form>
