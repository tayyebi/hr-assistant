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
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
    </header>

    <!-- Sidebar -->
    <aside>
        <header>
            <h1>HR Assistant</h1>
            <p>Administration Console</p>
        </header>

        <nav>
            <a href="/dashboard" <?php echo ($activeTab ?? '') === 'dashboard' ? 'data-active="true"' : ''; ?>>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                Dashboard
            </a>
            <a href="/employees" <?php echo ($activeTab ?? '') === 'employees' ? 'data-active="true"' : ''; ?>>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Employees
            </a>
            <a href="/teams" <?php echo ($activeTab ?? '') === 'teams' ? 'data-active="true"' : ''; ?>>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <circle cx="19" cy="11" r="2"></circle>
                    <path d="M19 8v1"></path>
                    <path d="M19 13v1"></path>
                </svg>
                Teams
            </a>
            <a href="/messages" <?php echo ($activeTab ?? '') === 'messages' ? 'data-active="true"' : ''; ?>>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                Direct Messages
            </a>
            <a href="/assets" <?php echo ($activeTab ?? '') === 'assets' ? 'data-active="true"' : ''; ?>>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                    <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                    <line x1="6" y1="6" x2="6.01" y2="6"></line>
                    <line x1="6" y1="18" x2="6.01" y2="18"></line>
                </svg>
                Digital Assets
            </a>
            <a href="/jobs" <?php echo ($activeTab ?? '') === 'jobs' ? 'data-active="true"' : ''; ?>>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                    <polyline points="2 17 12 22 22 17"></polyline>
                    <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
                System Jobs
            </a>
            <a href="/settings" <?php echo ($activeTab ?? '') === 'settings' ? 'data-active="true"' : ''; ?>>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                Settings
            </a>
        </nav>

        <footer>
            <article>
                <p><strong>System Status</strong></p>
                <p>● Backend Sync Active</p>
                <p>● Services Connected</p>
                <small>
                    <?php echo htmlspecialchars($tenant['name'] ?? 'Unknown Tenant'); ?>
                </small>
            </article>
            <article>
                <p><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                <a href="/logout">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
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
