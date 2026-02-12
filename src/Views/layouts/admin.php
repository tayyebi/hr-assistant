<?php
/**
 * Admin layout — system administrator pages.
 * Variables: $title, $content, $auth
 */

$user = $auth->user();
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars(($title ?? '') . ' — HCMS Admin') ?></title>
<link rel="stylesheet" href="/css/app.css">
</head>
<body class="layout-app">
<nav class="topbar topbar-admin">
    <div class="topbar-left">
        <a href="/" class="topbar-brand">HCMS Admin</a>
    </div>
    <div class="topbar-right">
        <span class="topbar-user"><?= htmlspecialchars($user['display_name'] ?? '') ?></span>
        <a href="/logout" class="topbar-link">Logout</a>
    </div>
</nav>
<div class="app-body">
    <aside class="sidebar">
        <ul class="sidebar-nav">
            <li><a href="/">Dashboard</a></li>
            <li><a href="/admin/tenants">Tenants</a></li>
            <li><a href="/admin/audit">Audit Log</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <?= $content ?>
    </main>
</div>
</body>
</html>
