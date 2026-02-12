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
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars(($title ?? '') . ' — ' . $tenantName) ?></title>
<link rel="stylesheet" href="/css/app.css">
</head>
<body class="layout-app">
<nav class="topbar">
    <div class="topbar-left">
        <a href="<?= $prefix ?>/dashboard" class="topbar-brand"><?= htmlspecialchars($tenantName) ?></a>
    </div>
    <div class="topbar-right">
        <span class="topbar-user"><?= htmlspecialchars($user['display_name'] ?? '') ?></span>
        <a href="/logout" class="topbar-link">Logout</a>
    </div>
</nav>
<div class="app-body">
    <aside class="sidebar">
        <ul class="sidebar-nav">
            <li><a href="<?= $prefix ?>/dashboard">Dashboard</a></li>
<?php if ($auth->hasRole('workspace_admin', 'hr_specialist')): ?>
            <li><a href="<?= $prefix ?>/employees">Employees</a></li>
<?php endif; ?>
<?php foreach ($sidebarItems as $item): ?>
            <li><a href="<?= $prefix . $item['route'] ?>"><?= htmlspecialchars($item['label']) ?></a></li>
<?php endforeach; ?>
<?php if ($auth->hasRole('workspace_admin')): ?>
            <li class="sidebar-divider"></li>
            <li><a href="<?= $prefix ?>/settings">Settings</a></li>
<?php endif; ?>
        </ul>
    </aside>
    <main class="main-content">
        <?= $content ?>
    </main>
</div>
</body>
</html>
