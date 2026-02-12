<?php $layout = 'app'; ?>
<div class="page-header">
    <h1 class="page-title"><?= htmlspecialchars($account['label'] ?? 'Inbox') ?></h1>
    <a href="<?= $prefix ?>/email" class="btn btn-sm">Back</a>
</div>

<details class="compose-toggle">
    <summary class="btn btn-primary btn-sm">Compose</summary>
    <form method="post" action="<?= $prefix ?>/email/compose" class="form-stack" style="margin-top:8px">
        <input type="hidden" name="account_id" value="<?= $account['id'] ?? '' ?>">
        <input type="email" name="to" placeholder="To" class="field-input field-sm" required>
        <input type="text" name="subject" placeholder="Subject" class="field-input field-sm">
        <textarea name="body" rows="4" class="field-input" placeholder="Message"></textarea>
        <button type="submit" class="btn btn-primary btn-sm">Send</button>
    </form>
</details>

<?php if (empty($emails)): ?>
<p class="text-muted" style="margin-top:12px">No emails.</p>
<?php else: ?>
<table class="table" style="margin-top:12px">
<thead><tr><th>Dir</th><th>From</th><th>To</th><th>Subject</th><th>Employee</th><th>Date</th><th></th></tr></thead>
<tbody>
<?php foreach ($emails as $e): ?>
<tr class="<?= $e['is_read'] ? '' : 'row-unread' ?>">
    <td class="text-sm"><?= $e['direction'] === 'inbound' ? '↓' : '↑' ?></td>
    <td class="text-sm"><?= htmlspecialchars($e['from_address']) ?></td>
    <td class="text-sm"><?= htmlspecialchars($e['to_address']) ?></td>
    <td><?= htmlspecialchars($e['subject'] ?? '(No Subject)') ?></td>
    <td><?= $e['emp_first'] ? htmlspecialchars($e['emp_first'] . ' ' . $e['emp_last']) : '<span class="text-muted">—</span>' ?></td>
    <td class="text-muted text-sm"><?= $e['created_at'] ?></td>
    <td><a href="<?= $prefix ?>/email/view/<?= $e['id'] ?>" class="btn btn-sm">View</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
