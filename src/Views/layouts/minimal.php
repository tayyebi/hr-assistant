<?php
/**
 * Minimal layout — login, workspace selection.
 * Variables: $title, $content
 */
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($title ?? 'HCMS') ?></title>
<link rel="stylesheet" href="/css/app.css">
</head>
<body class="layout-minimal">
<main class="minimal-wrap">
<?= $content ?>
</main>
</body>
</html>
