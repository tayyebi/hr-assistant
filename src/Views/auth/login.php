<?php $layout = 'minimal'; ?>

<article class="login-card">
    <header>
        <h1 class="login-title">HCMS</h1>
        <p class="login-subtitle">Human Capital Management System</p>
    </header>

    <?php if (!empty($error)): ?>
    <div class="flash-error" role="alert">
        <strong>Error:</strong> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="post" action="/login" aria-label="Login form">
        <div class="form-group">
            <label for="email" class="field-label">Email Address</label>
            <input 
                type="email" 
                id="email"
                name="email" 
                class="field-input" 
                required 
                autofocus
                placeholder="admin@hcms.local"
                aria-required="true"
            >
        </div>

        <div class="form-group">
            <label for="password" class="field-label">Password</label>
            <input 
                type="password" 
                id="password"
                name="password" 
                class="field-input" 
                required
                aria-required="true"
            >
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            Sign In
        </button>
    </form>

    <p class="text-muted text-sm" style="margin-top: 20px; text-align: center;">
        Default credentials:<br>
        Email: <code>admin@hcms.local</code><br>
        Password: <code>admin</code>
    </p>
</article>
