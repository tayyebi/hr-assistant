<?php $layout = 'minimal'; ?>
<div class="login-card">
    <h1 class="login-title">HCMS</h1>
<?php if (!empty($error)): ?>
    <p class="flash-error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
    <form method="post" action="/login">
        <label class="field-label">Email</label>
        <input type="email" name="email" class="field-input" required autofocus>
        <label class="field-label">Password</label>
        <input type="password" name="password" class="field-input" required>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
</div>
