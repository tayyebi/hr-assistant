<h2>Announcements</h2>

<?php if ($isHR): ?>
<details style="margin-bottom:20px;border:1px solid #ddd;padding:12px;border-radius:4px;">
    <summary style="cursor:pointer;font-weight:bold;">New Announcement</summary>
    <form method="post" action="<?= $prefix ?>/announcements" style="margin-top:12px;">
        <div class="form-group"><label>Title</label><input name="title" required></div>
        <div class="form-group"><label>Body</label><textarea name="body" rows="4" required></textarea></div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
            <div class="form-group"><label>Priority</label>
                <select name="priority"><option value="low">Low</option><option value="normal" selected>Normal</option><option value="high">High</option><option value="urgent">Urgent</option></select>
            </div>
            <div class="form-group"><label>Publish At</label><input type="datetime-local" name="published_at"></div>
            <div class="form-group"><label>Expires At</label><input type="datetime-local" name="expires_at"></div>
        </div>
        <div class="form-group"><label><input type="checkbox" name="is_pinned"> Pin to top</label></div>
        <button type="submit" class="btn btn-primary">Publish</button>
    </form>
</details>
<?php endif; ?>

<?php if (empty($announcements)): ?>
    <p style="color:#666;">No announcements.</p>
<?php else: ?>
    <?php foreach ($announcements as $a): ?>
    <div style="border:1px solid <?= $a['priority'] === 'urgent' ? '#e74c3c' : ($a['priority'] === 'high' ? '#e67e22' : '#ddd') ?>;padding:12px 16px;margin-bottom:12px;border-radius:4px;<?= $a['is_pinned'] ? 'background:#fffef0;' : '' ?>">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <?php if ($a['is_pinned']): ?><span style="color:#e67e22;">📌</span><?php endif; ?>
                <a href="<?= $prefix ?>/announcements/<?= $a['id'] ?>" style="font-weight:bold;font-size:16px;"><?= htmlspecialchars($a['title']) ?></a>
                <?php if ($a['priority'] === 'urgent'): ?><span style="color:#e74c3c;font-size:11px;font-weight:bold;"> URGENT</span>
                <?php elseif ($a['priority'] === 'high'): ?><span style="color:#e67e22;font-size:11px;"> HIGH</span><?php endif; ?>
                <?php if (!$a['is_read']): ?><span style="background:#3498db;color:#fff;font-size:10px;padding:1px 6px;border-radius:8px;margin-left:6px;">NEW</span><?php endif; ?>
            </div>
            <small style="color:#999;"><?= $a['created_at'] ?> by <?= htmlspecialchars($a['author'] ?? 'System') ?> · <?= (int)$a['read_count'] ?> read</small>
        </div>
        <p style="margin:8px 0 0;color:#555;"><?= htmlspecialchars(mb_substr($a['body'], 0, 200)) ?><?= mb_strlen($a['body']) > 200 ? '…' : '' ?></p>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
