<?php $layout = 'app'; ?>

<header class="page-header">
    <h1 class="page-title">
        <?= htmlspecialchars($account['label'] ?? 'Email Inbox') ?>
    </h1>
    <a href="<?= $prefix ?>/email" class="btn btn-sm" title="Back to accounts">
        ← Back
    </a>
</header>

<section style="margin-bottom: 24px;">
    <details class="compose-toggle" open>
        <summary class="btn btn-primary">✎ Compose Email</summary>
        <form method="post" action="<?= $prefix ?>/email/compose" class="form-stack" style="margin-top: 16px;">
            <input type="hidden" name="account_id" value="<?= htmlspecialchars((string)($account['id'] ?? '')) ?>">
            
            <div class="form-group">
                <label for="email_to" class="field-label">Recipient *</label>
                <input 
                    type="email" 
                    id="email_to"
                    name="to" 
                    placeholder="recipient@example.com" 
                    class="field-input" 
                    required
                    aria-required="true"
                >
            </div>

            <div class="form-group">
                <label for="email_subject" class="field-label">Subject</label>
                <input 
                    type="text" 
                    id="email_subject"
                    name="subject" 
                    placeholder="Email subject" 
                    class="field-input"
                >
            </div>

            <div class="form-group">
                <label for="email_body" class="field-label">Message</label>
                <textarea 
                    id="email_body"
                    name="body" 
                    rows="4" 
                    class="field-input" 
                    placeholder="Type your message here…"
                ></textarea>
            </div>

            <button type="submit" class="btn btn-primary" title="Send email">
                Send Email
            </button>
        </form>
    </details>
</section>

<section>
    <h2 class="section-title">Messages</h2>
    
    <?php if (empty($emails)): ?>
    <p class="text-muted">No emails in this account yet.</p>
    <?php else: ?>
    <div style="overflow-x: auto;">
        <table class="table" role="grid">
            <thead>
                <tr>
                    <th scope="col" style="width: 40px;">Dir</th>
                    <th scope="col">From</th>
                    <th scope="col">To</th>
                    <th scope="col">Subject</th>
                    <th scope="col">Employee</th>
                    <th scope="col">Date</th>
                    <th scope="col" style="width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emails as $e): ?>
                <tr class="<?= !$e['is_read'] ? 'row-unread' : '' ?>">
                    <td title="<?= $e['direction'] === 'inbound' ? 'Received' : 'Sent' ?>">
                        <span style="font-size: 16px; color: var(--muted);">
                            <?= $e['direction'] === 'inbound' ? '↓' : '↑' ?>
                        </span>
                    </td>
                    <td>
                        <code class="text-sm">
                            <?= htmlspecialchars($e['from_address']) ?>
                        </code>
                    </td>
                    <td>
                        <code class="text-sm">
                            <?= htmlspecialchars($e['to_address']) ?>
                        </code>
                    </td>
                    <td>
                        <strong>
                            <?= htmlspecialchars($e['subject'] ?? '(No Subject)') ?>
                        </strong>
                    </td>
                    <td>
                        <?php if ($e['emp_first']): ?>
                        <?= htmlspecialchars($e['emp_first'] . ' ' . $e['emp_last']) ?>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted text-sm">
                        <?= htmlspecialchars($e['created_at']) ?>
                    </td>
                    <td>
                        <a 
                            href="<?= $prefix ?>/email/view/<?= $e['id'] ?>" 
                            class="btn btn-sm"
                            title="View email"
                        >
                            View
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
