<?php
/**
 * Application layout — sidebar + topbar.
 * Variables: $title, $content, $prefix, $tenant, $auth
 */

$user = $auth->user();
$role = $auth->tenantRole();
$sidebarItems = $sidebarItems ?? [];
$tenantName = $tenant->current()['name'] ?? 'HCMS';
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="HCMS - Human Capital Management System">
    <meta name="theme-color" content="#2563eb">
    <title><?= htmlspecialchars(($title ?? '') . ' — ' . $tenantName) ?></title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="layout-app">
    <nav class="topbar" role="navigation" aria-label="Main navigation">
        <div class="topbar-left">
            <a href="<?= $prefix ?>/dashboard" class="topbar-brand" title="Back to Dashboard">
                <?= htmlspecialchars($tenantName) ?>
            </a>
        </div>
        <div class="topbar-right">
            <span class="topbar-user" role="img" aria-label="Current user">
                <?= htmlspecialchars($user['display_name'] ?? 'User') ?>
            </span>
            <a href="/logout" class="topbar-link">Logout</a>
        </div>
    </nav>
    <div class="app-body">
        <aside class="sidebar" role="navigation" aria-label="Sidebar navigation">
            <ul class="sidebar-nav">
                <li>
                    <a href="<?= $prefix ?>/dashboard" title="Dashboard">
                        Dashboard
                    </a>
                </li>
                <?php if ($auth->hasRole('workspace_admin', 'hr_specialist')): ?>
                <li>
                    <a href="<?= $prefix ?>/employees" title="Employees">
                        Employees
                    </a>
                </li>
                <?php endif; ?>
                <?php foreach ($sidebarItems as $item): ?>
                <li>
                    <a href="<?= $prefix . $item['route'] ?>" title="<?= htmlspecialchars($item['label']) ?>">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
                <?php if ($auth->hasRole('workspace_admin')): ?>
                <li class="sidebar-divider" role="separator"></li>
                <li>
                    <a href="<?= $prefix ?>/settings" title="Settings">
                        Settings
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </aside>
        <main class="main-content" role="main">
            <?= $content ?>
        </main>
    </div>
</body>
</html>
