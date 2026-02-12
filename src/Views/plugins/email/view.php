<?php $layout = 'app'; ?>
<div class="page-header">
    <h1 class="page-title"><?= htmlspecialchars($email['subject'] ?? '(No Subject)') ?></h1>
</div>
<div class="email-meta">
    <p><strong>From:</strong> <?= htmlspecialchars($email['from_address']) ?></p>
    <p><strong>To:</strong> <?= htmlspecialchars($email['to_address']) ?></p>
    <p class="text-muted text-sm"><?= $email['created_at'] ?></p>
</div>
<div class="email-body">
    <pre class="email-text"><?= htmlspecialchars($email['body'] ?? '') ?></pre>
</div>
<div class="chat-assign" style="margin-top:12px">
    <form method="post" action="<?= $prefix ?>/email/assign/<?= $email['id'] ?>" class="inline-form">
        <select name="employee_id" class="field-input field-sm">
            <option value="">Assign to employee…</option>
<?php foreach (($employees ?? []) as $e): ?>
            <option value="<?= $e['id'] ?>" <?= (($email['employee_id'] ?? '') == $e['id']) ? 'selected' : '' ?>><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></option>
<?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-sm">Assign</button>
    </form>
</div>
