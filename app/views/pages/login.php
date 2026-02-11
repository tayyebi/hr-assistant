<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>HR Assistant - Login</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body data-page="login">
    <main>
        <header>
            <h1>HR Assistant</h1>
            <p>Administration Console</p>
        </header>

        <article>
            <?php if (!empty($error)): ?>
                <output data-type="error"><?php echo htmlspecialchars($error); ?></output>
            <?php endif; ?>

            <form method="POST" action="/login">
                <div>
                    <label>Email Address</label>
                    <input type="email" name="email" required autocomplete="email">
                </div>

                <div>
                    <label>Password</label>
                    <input type="password" name="password" required autocomplete="current-password">
                </div>

                <button type="submit">Sign In</button>
            </form>
        </article>

        <aside>
            <p><strong>Default Credentials:</strong></p>
            <p>System Admin: admin@localhost / password</p>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">
                Run <code style="background: var(--bg-secondary); padding: 0.1rem 0.3rem; border-radius: 3px;">php cli/seed.php</code> to create the default admin user.
            </p>
        </aside>
    </main>
</body>
</html>
