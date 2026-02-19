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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="HCMS Admin - System Administration">
    <meta name="theme-color" content="#2563eb">
    <title><?= htmlspecialchars(($title ?? '') . ' — HCMS Admin') ?></title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="layout-app">
    <nav class="topbar topbar-admin" role="navigation" aria-label="Main navigation">
        <div class="topbar-left">
            <a href="/" class="topbar-brand" title="System Dashboard">
                HCMS Admin
            </a>
        </div>
        <div class="topbar-right">
            <span class="topbar-user" role="img" aria-label="Current user">
                <?= htmlspecialchars($user['display_name'] ?? 'Admin') ?>
            </span>
            <a href="/logout" class="topbar-link">Logout</a>
        </div>
    </nav>
    <div class="app-body">
        <aside class="sidebar" role="navigation" aria-label="Admin navigation">
            <ul class="sidebar-nav">
                <li>
                    <a href="/" title="Dashboard">
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="/admin/tenants" title="Tenants">
                        Tenants
                    </a>
                </li>
                <li>
                    <a href="/admin/audit" title="Audit Log">
                        Audit Log
                    </a>
                </li>
            </ul>
        </aside>
        <main class="main-content" role="main">
            <?= $content ?>
        </main>
    </div>
</body>
</html>
