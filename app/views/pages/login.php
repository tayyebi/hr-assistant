<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>HR Assistant - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.0/css/bulma.min.css">
    <link rel="stylesheet" href="/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="card login-card">
            <div class="card-header has-background-white">
                <div>
                    <h1 class="title is-4 mb-0 has-text-primary">HR Assistant</h1>
                    <p class="subtitle is-6 mt-1">Administration Console</p>
                </div>
            </div>
            
            <div class="card-content">
                <?php if (!empty($error)): ?>
                    <div class="notification is-danger is-light mb-5">
                        <a href="#" class="delete"></a>
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/login">
                    <div class="field">
                        <label class="label">Email Address</label>
                        <div class="control has-icons-left">
                            <input class="input" type="email" name="email" placeholder="admin@localhost" required autocomplete="email">
                            <span class="icon is-small is-left">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 3h14v10H1V3z" stroke="currentColor" stroke-width="1" fill="none"/>
                                    <path d="M1 3l7 5 7-5" stroke="currentColor" stroke-width="1" fill="none"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Password</label>
                        <div class="control has-icons-left">
                            <input class="input" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                            <span class="icon is-small is-left">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 7h14M8 7v5M4 7C2.895 7 2 6.105 2 5V3c0-1.105.895-2 2-2h8c1.105 0 2 .895 2 2v2c0 1.105-.895 2-2 2" stroke="currentColor" stroke-width="1" fill="none" stroke-linecap="round"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <button type="submit" class="button is-primary is-fullwidth">
                                <span class="icon">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 1v10M1 12h14M3 14h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span>Sign In</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-footer has-background-light">
                <div class="content is-small">
                    <p><strong>Default Credentials:</strong></p>
                    <p>System Admin: <code>admin@localhost</code> / <code>password</code></p>
                    <p class="has-text-grey is-size-7 mt-2">
                        Run <code>docker compose exec app php cli/seed.php</code> to create the default admin user.
                    </p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
