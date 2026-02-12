<h2>Files – <?= htmlspecialchars($link['first_name'] . ' ' . $link['last_name']) ?></h2>
<p>User: <?= htmlspecialchars($link['nc_user_id']) ?> | Path: <code><?= htmlspecialchars($currentPath) ?></code></p>

<?php if ($currentPath !== '/'): ?>
    <?php $parent = dirname($currentPath); if ($parent === '.') { $parent = '/'; } ?>
    <a href="<?= $prefix ?>/nextcloud/files/<?= $link['id'] ?>?path=<?= urlencode($parent) ?>" class="btn btn-sm">⬆ Up</a>
<?php endif; ?>

<table><thead><tr><th>Name</th><th>Type</th><th>Size</th></tr></thead><tbody>
<?php foreach ($files as $f): if (!$f['name']) { continue; } ?>
    <tr>
        <td>
            <?php if ($f['is_dir']): ?>
                <a href="<?= $prefix ?>/nextcloud/files/<?= $link['id'] ?>?path=<?= urlencode(rtrim($currentPath, '/') . '/' . $f['name']) ?>">📁 <?= htmlspecialchars($f['name']) ?></a>
            <?php else: ?>
                📄 <?= htmlspecialchars($f['name']) ?>
            <?php endif; ?>
        </td>
        <td><?= $f['is_dir'] ? 'Folder' : 'File' ?></td>
        <td><?= $f['is_dir'] ? '-' : number_format($f['size'] / 1024, 1) . ' KB' ?></td>
    </tr>
<?php endforeach; ?>
<?php if (empty($files)): ?><tr><td colspan="3">Empty or API error</td></tr><?php endif; ?>
</tbody></table>
