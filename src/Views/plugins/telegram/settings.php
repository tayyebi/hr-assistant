<?php $layout = 'app'; ?>
<div class="page-header">
    <h1 class="page-title">Telegram Settings</h1>
</div>
<form method="post" action="<?= $prefix ?>/telegram/settings" class="form-stack">
    <label class="field-label">Bot Token</label>
    <input type="text" name="bot_token" value="<?= htmlspecialchars($botToken ?? '') ?>" class="field-input" placeholder="123456:ABC-DEF">
    <button type="submit" class="btn btn-primary btn-sm">Save</button>
</form>
