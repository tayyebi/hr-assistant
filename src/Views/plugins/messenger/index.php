<?php $layout = 'app'; ?>
<div class="page-header">
    <h1 class="page-title">Messenger</h1>
</div>

<?php if (empty($employees)): ?>
<p class="text-muted">No employees found.</p>
<?php else: ?>
<div class="messenger-layout">
    <aside class="messenger-contacts">
        <?php foreach ($employees as $e): ?>
        <?php $m = $meta[$e['id']] ?? []; ?>
        <a class="contact-item" href="<?= $prefix ?>/messenger/employee/<?= $e['id'] ?>">
            <div class="contact-name"><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></div>
            <div class="contact-badges">
                <?php if (!empty($m['has_telegram'])): ?><span class="channel-badge badge-telegram">Telegram</span><?php endif; ?>
                <?php if (!empty($m['has_email'])): ?><span class="channel-badge badge-email">Email</span><?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </aside>

    <div class="messenger-main card">
        <div class="card-body">
            <h3 class="section-title">Select a contact to view conversation</h3>
            <p class="text-muted">Messages are delivered via the channel badges shown on each contact.</p>
        </div>
    </div>
</div>
<?php endif; ?>
