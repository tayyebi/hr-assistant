<?php $layout = 'app'; ?>
<div class="page-header">
    <h1 class="page-title">Email</h1>
    <a href="<?= $prefix ?>/email/settings" class="btn btn-sm">Settings</a>
</div>
<?php if (empty($accounts)): ?>
<p class="text-muted">No email accounts configured. Add one in settings.</p>
<?php else: ?>
<table class="table">
<thead><tr><th>Account</th><th>IMAP</th><th>SMTP</th><th></th></tr></thead>
<tbody>
<?php foreach ($accounts as $a): ?>
<tr>
    <td><?= htmlspecialchars($a['label']) ?></td>
    <td class="text-sm"><?= htmlspecialchars($a['imap_host'] . ':' . $a['imap_port']) ?></td>
    <td class="text-sm"><?= htmlspecialchars($a['smtp_host'] . ':' . $a['smtp_port']) ?></td>
    <td>
        <a href="<?= $prefix ?>/email/account/<?= $a['id'] ?>" class="btn btn-sm">Inbox</a>
        <a href="<?= $prefix ?>/email/fetch/<?= $a['id'] ?>" class="btn btn-sm">Fetch</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
