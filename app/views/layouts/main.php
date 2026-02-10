<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>HR Assistant - <?php echo ucfirst($activeTab ?? 'Dashboard'); ?></title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <!-- Mobile Header -->
    <header>
        <h1>HR Assistant</h1>
        <button data-menu onclick="toggleSidebar()">
            <?php \App\Core\Icon::render('menu', 24, 24); ?>
        </button>
    </header>

    <!-- Sidebar -->
    <aside>
        <header>
            <h1>HR Assistant</h1>
            <p><?php 
                $workspaceContext = \App\Core\View::getWorkspaceContext();
                echo $workspaceContext['isWorkspace'] ? 'Workspace: ' . htmlspecialchars($workspaceContext['tenantName']) : 'Administration Console';
            ?></p>
        </header>

        <nav>
            <a href="<?php echo \App\Core\View::workspaceUrl('/dashboard'); ?>" <?php echo ($activeTab ?? '') === 'dashboard' ? 'data-active="true"' : ''; ?>>
                <?php \App\Core\Icon::render('dashboard', 20, 20); ?>
                Dashboard
            </a>
            <a href="<?php echo \App\Core\View::workspaceUrl('/employees'); ?>" <?php echo ($activeTab ?? '') === 'employees' ? 'data-active="true"' : ''; ?>>
                <?php \App\Core\Icon::render('employees', 20, 20); ?>
                Employees
            </a>
            <a href="<?php echo \App\Core\View::workspaceUrl('/teams'); ?>" <?php echo ($activeTab ?? '') === 'teams' ? 'data-active="true"' : ''; ?>>
                <?php \App\Core\Icon::render('teams', 20, 20); ?>
                Teams
            </a>
            <a href="<?php echo \App\Core\View::workspaceUrl('/messages'); ?>" <?php echo ($activeTab ?? '') === 'messages' ? 'data-active="true"' : ''; ?>>
                <?php \App\Core\Icon::render('messages', 20, 20); ?>
                Direct Messages
            </a>
            <a href="<?php echo \App\Core\View::workspaceUrl('/assets'); ?>" <?php echo ($activeTab ?? '') === 'assets' ? 'data-active="true"' : ''; ?>>
                <?php \App\Core\Icon::render('server', 20, 20); ?>
                Digital Assets
            </a>
            <a href="<?php echo \App\Core\View::workspaceUrl('/jobs'); ?>" <?php echo ($activeTab ?? '') === 'jobs' ? 'data-active="true"' : ''; ?>>
                <?php \App\Core\Icon::render('layers', 20, 20); ?>
                System Jobs
            </a>
            <a href="<?php echo \App\Core\View::workspaceUrl('/settings'); ?>" <?php echo ($activeTab ?? '') === 'settings' ? 'data-active="true"' : ''; ?>>
                <?php \App\Core\Icon::render('settings', 20, 20); ?>
                Settings
            </a>
            <?php if (\App\Models\User::isSystemAdmin()): ?>
                <a href="/admin" style="border-top: 1px solid var(--border-color); margin-top: 0.5rem; padding-top: 0.5rem;">
                    <?php \App\Core\Icon::render('admin', 20, 20); ?>
                    System Admin
                </a>
            <?php endif; ?>
        </nav>

        <footer>
            <article>
                <p><strong>System Status</strong></p>
                <p>● Backend Sync Active</p>
                <p>● Services Connected</p>
                <small>
                    <?php 
                        $workspaceContext = \App\Core\View::getWorkspaceContext();
                        echo htmlspecialchars($workspaceContext['isWorkspace'] ? $workspaceContext['tenantName'] : ($tenant['name'] ?? 'System Admin'));
                    ?>
                </small>
            </article>
            <article>
                <p><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                <a href="/logout">
                    <?php \App\Core\Icon::render('logout', 16, 16); ?>
                    Logout
                </a>
            </article>
        </footer>
    </aside>

    <!-- Main Content -->
    <main>
        <?php echo $content; ?>
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('aside');
            const isOpen = sidebar.getAttribute('data-open') === 'true';
            sidebar.setAttribute('data-open', isOpen ? 'false' : 'true');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('body > aside');
            const menuBtn = document.querySelector('button[data-menu]');
            
            if (window.innerWidth < 768 && 
                sidebar.getAttribute('data-open') === 'true' &&
                !sidebar.contains(e.target) && 
                !menuBtn.contains(e.target)) {
                sidebar.setAttribute('data-open', 'false');
            }
        });
    </script>
</body>
</html>
