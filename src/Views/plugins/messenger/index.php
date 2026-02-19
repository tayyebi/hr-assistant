<?php $layout = 'app'; ?>

<header class="page-header">
    <h1 class="page-title">Messenger</h1>
    <p class="page-subtitle">Send messages to employees via multiple channels</p>
</header>

<?php if (empty($employees)): ?>
<section>
    <p class="text-muted">No employees found in this workspace.</p>
</section>
<?php else: ?>
<section class="messenger-layout">
    <aside class="messenger-contacts" role="region" aria-label="Employee contacts">
        <?php foreach ($employees as $e): ?>
        <?php $m = $meta[$e['id']] ?? []; ?>
        <a 
            class="contact-item" 
            href="<?= $prefix ?>/messenger/employee/<?= $e['id'] ?>"
            title="Message <?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?>"
        >
            <div>
                <div class="contact-name">
                    <?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?>
                </div>
                <div class="contact-badges">
                    <?php if (!empty($m['has_telegram'])): ?>
                    <span class="channel-badge badge-telegram" title="Telegram available">Telegram</span>
                    <?php endif; ?>
                    <?php if (!empty($m['has_email'])): ?>
                    <span class="channel-badge badge-email" title="Email available">Email</span>
                    <?php endif; ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </aside>

    <div class="messenger-main">
        <article class="card">
            <div style="padding: 40px; text-align: center;">
                <h3 class="section-title" style="margin-top: 0;">No Conversation Selected</h3>
                <p class="text-muted">
                    Select an employee from the list to start messaging.<br>
                    Messages can be sent via Telegram, Email, or both channels.
                </p>
            </div>
        </article>
    </div>
</section>
<?php endif; ?>
