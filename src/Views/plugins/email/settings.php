<?php $layout = 'app'; ?>
<div class="page-header">
    <h1 class="page-title">Email Settings</h1>
</div>
<h2 class="section-title">Add Account</h2>
<form method="post" action="<?= $prefix ?>/email/settings" class="form-stack">
    <div class="form-row">
        <div class="form-col">
            <label class="field-label">Label</label>
            <input type="text" name="label" class="field-input" required placeholder="Office Mail">
        </div>
    </div>
    <div class="form-row">
        <div class="form-col">
            <label class="field-label">IMAP Host</label>
            <input type="text" name="imap_host" class="field-input" required>
        </div>
        <div class="form-col">
            <label class="field-label">IMAP Port</label>
            <input type="number" name="imap_port" class="field-input" value="993">
        </div>
    </div>
    <div class="form-row">
        <div class="form-col">
            <label class="field-label">SMTP Host</label>
            <input type="text" name="smtp_host" class="field-input" required>
        </div>
        <div class="form-col">
            <label class="field-label">SMTP Port</label>
            <input type="number" name="smtp_port" class="field-input" value="587">
        </div>
    </div>
    <div class="form-row">
        <div class="form-col">
            <label class="field-label">Username</label>
            <input type="text" name="username" class="field-input" required>
        </div>
        <div class="form-col">
            <label class="field-label">Password</label>
            <input type="password" name="password" class="field-input" required>
        </div>
    </div>
    <div class="form-row">
        <div class="form-col">
            <label class="field-label">From Name</label>
            <input type="text" name="from_name" class="field-input" placeholder="HR Department">
        </div>
        <div class="form-col">
            <label class="field-label">From Address</label>
            <input type="email" name="from_address" class="field-input" placeholder="hr@company.com">
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Add Account</button>
</form>

<?php if (!empty($accounts)): ?>
<h2 class="section-title" style="margin-top:24px">Existing Accounts</h2>
<table class="table table-compact">
<thead><tr><th>Label</th><th>IMAP</th><th>SMTP</th><th>Username</th></tr></thead>
<tbody>
<?php foreach ($accounts as $a): ?>
<tr>
    <td><?= htmlspecialchars($a['label']) ?></td>
    <td class="text-sm"><?= htmlspecialchars($a['imap_host'] . ':' . $a['imap_port']) ?></td>
    <td class="text-sm"><?= htmlspecialchars($a['smtp_host'] . ':' . $a['smtp_port']) ?></td>
    <td class="text-sm"><?= htmlspecialchars($a['username']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
