<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>HR Assistant - <?php echo ucfirst($activeTab ?? 'Dashboard'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.0/css/bulma.min.css">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <input type="checkbox" id="mobileMenuToggle" class="mobile-menu-toggle" aria-hidden="true">
    
    <div class="navbar is-fixed-top" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <div class="navbar-item">
                <strong>HR Assistant</strong>
            </div>
            <label for="mobileMenuToggle" class="navbar-burger" aria-label="Toggle navigation menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </label>
        </div>

        <div class="navbar-menu">
            <div class="navbar-end">
                <div class="navbar-item">
                    <div class="field is-grouped">
                        <p class="control">
                            <span class="icon-text">
                                <span><?php echo htmlspecialchars($user['email'] ?? ''); ?></span>
                            </span>
                        </p>
                    </div>
                </div>
                <div class="navbar-item">
                    <a href="<?php echo \App\Core\UrlHelper::url('/logout'); ?>" class="button is-light icon-text">
                        <span class="icon">
                            <?php \App\Core\Icon::render('logout', 16, 16); ?>
                        </span>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="layout-main">
        <!-- Sidebar Navigation -->
        <aside class="sidebar-container is-hidden-mobile">
            <nav class="menu sidebar-menu sidebar-nav-padding" aria-label="Main sidebar">
                <!-- User Profile -->
                <div class="sidebar-profile mb-3">
                    <div class="avatar lg mb-1 mx-auto">
                        <?php echo strtoupper($user['email'][0] ?? 'U'); ?>
                    </div>
                    <div class="has-text-centered">
                        <strong><?php echo htmlspecialchars($user['full_name'] ?? $user['email']); ?></strong><br>
                        <span class="text-small text-muted"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                </div>
                <p class="menu-label">
                    <strong>HR Assistant</strong>
                    <br>
                    <small>
                        <?php 
                            $workspaceContext = \App\Core\View::getWorkspaceContext();
                            echo htmlspecialchars($workspaceContext['isWorkspace'] ? $workspaceContext['tenantName'] : 'System Admin');
                        ?>
                    </small>
                </p>
                <!-- Quick Actions -->
                <div class="sidebar-actions mb-3">
                    <a href="<?php echo \App\Core\UrlHelper::workspace('/employees/create'); ?>" class="button is-primary is-small w-full mb-1">+ Add Employee</a>
                    <a href="<?php echo \App\Core\UrlHelper::workspace('/teams'); ?>" class="button is-light is-small w-full">Manage Teams</a>
                </div>
                <!-- Collapsible Navigation -->
                <ul class="menu-list sidebar-collapsible" id="sidebarNav">
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/dashboard'); ?>" class="<?php echo ($activeTab ?? '') === 'dashboard' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('dashboard', 18, 18); ?>
                                    </span>
                                    <span>Dashboard</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/employees'); ?>" class="<?php echo ($activeTab ?? '') === 'employees' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('employees', 18, 18); ?>
                                    </span>
                                    <span>Employees</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/teams'); ?>" class="<?php echo ($activeTab ?? '') === 'teams' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('teams', 18, 18); ?>
                                    </span>
                                    <span>Teams</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/messages'); ?>" class="<?php echo ($activeTab ?? '') === 'messages' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('messages', 18, 18); ?>
                                    </span>
                                    <span>Messages</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/repositories'); ?>" class="<?php echo ($activeTab ?? '') === 'repositories' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('git-branch', 18, 18); ?>
                                    </span>
                                    <span>Repositories</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/calendars'); ?>" class="<?php echo ($activeTab ?? '') === 'calendars' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('clock', 18, 18); ?>
                                    </span>
                                    <span>Calendars</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/secrets'); ?>" class="<?php echo ($activeTab ?? '') === 'secrets' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('key', 18, 18); ?>
                                    </span>
                                    <span>Secrets</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/identity'); ?>" class="<?php echo ($activeTab ?? '') === 'identity' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('lock', 18, 18); ?>
                                    </span>
                                    <span>Identity</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/jobs'); ?>" class="<?php echo ($activeTab ?? '') === 'jobs' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('layers', 18, 18); ?>
                                    </span>
                                    <span>System Jobs</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/settings'); ?>" class="<?php echo ($activeTab ?? '') === 'settings' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('settings', 18, 18); ?>
                                    </span>
                                    <span>Settings</span>
                                </span>
                            </a>
                        </li>
                        <?php if (\App\Models\User::isSystemAdmin()): ?>
                            <li>
                                <hr class="my-3">
                            </li>
                            <li>
                                <a href="<?php echo \App\Core\UrlHelper::url('/admin'); ?>" class="<?php echo ($activeTab ?? '') === 'admin' ? 'is-active' : ''; ?>">
                                    <span class="icon-text">
                                        <span class="icon">
                                            <?php \App\Core\Icon::render('admin', 18, 18); ?>
                                        </span>
                                        <span>System Admin</span>
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
        </aside>

        <!-- Mobile Sidebar Menu -->
        <div class="sidebar-mobile is-hidden-desktop">
            <nav class="menu sidebar-menu sidebar-nav-padding" aria-label="Mobile sidebar">
                <!-- User Profile -->
                <div class="sidebar-profile mb-3">
                    <div class="avatar lg mb-1 mx-auto">
                        <?php echo strtoupper($user['email'][0] ?? 'U'); ?>
                    </div>
                    <div class="has-text-centered">
                        <strong><?php echo htmlspecialchars($user['full_name'] ?? $user['email']); ?></strong><br>
                        <span class="text-small text-muted"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                </div>
                <p class="menu-label">Navigation</p>
                <!-- Quick Actions -->
                <div class="sidebar-actions mb-3">
                    <a href="<?php echo \App\Core\UrlHelper::workspace('/employees/create'); ?>" class="button is-primary is-small w-full mb-1">+ Add Employee</a>
                    <a href="<?php echo \App\Core\UrlHelper::workspace('/teams'); ?>" class="button is-light is-small w-full">Manage Teams</a>
                </div>
                <ul class="menu-list sidebar-collapsible" id="sidebarNavMobile">
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/dashboard'); ?>" class="<?php echo ($activeTab ?? '') === 'dashboard' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('dashboard', 18, 18); ?>
                                    </span>
                                    <span>Dashboard</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/employees'); ?>" class="<?php echo ($activeTab ?? '') === 'employees' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('employees', 18, 18); ?>
                                    </span>
                                    <span>Employees</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/teams'); ?>" class="<?php echo ($activeTab ?? '') === 'teams' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('teams', 18, 18); ?>
                                    </span>
                                    <span>Teams</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/messages'); ?>" class="<?php echo ($activeTab ?? '') === 'messages' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('messages', 18, 18); ?>
                                    </span>
                                    <span>Messages</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/repositories'); ?>" class="<?php echo ($activeTab ?? '') === 'repositories' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('git-branch', 18, 18); ?>
                                    </span>
                                    <span>Repositories</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/calendars'); ?>" class="<?php echo ($activeTab ?? '') === 'calendars' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('clock', 18, 18); ?>
                                    </span>
                                    <span>Calendars</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/secrets'); ?>" class="<?php echo ($activeTab ?? '') === 'secrets' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('key', 18, 18); ?>
                                    </span>
                                    <span>Secrets</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/identity'); ?>" class="<?php echo ($activeTab ?? '') === 'identity' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('lock', 18, 18); ?>
                                    </span>
                                    <span>Identity</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/jobs'); ?>" class="<?php echo ($activeTab ?? '') === 'jobs' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('layers', 18, 18); ?>
                                    </span>
                                    <span>System Jobs</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo \App\Core\UrlHelper::workspace('/settings'); ?>" class="<?php echo ($activeTab ?? '') === 'settings' ? 'is-active' : ''; ?>">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('settings', 18, 18); ?>
                                    </span>
                                    <span>Settings</span>
                                </span>
                            </a>
                        </li>
                        <?php if (\App\Models\User::isSystemAdmin()): ?>
                            <li>
                                <hr class="my-3">
                            </li>
                            <li>
                                <a href="<?php echo \App\Core\UrlHelper::url('/admin'); ?>" class="<?php echo ($activeTab ?? '') === 'admin' ? 'is-active' : ''; ?>">
                                    <span class="icon-text">
                                        <span class="icon">
                                            <?php \App\Core\Icon::render('admin', 18, 18); ?>
                                        </span>
                                        <span>System Admin</span>
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content-flex">
            <main class="main-content-padding">
                <?php echo $content; ?>
            </main>
        </div>
    </div>

</body>
</html>
