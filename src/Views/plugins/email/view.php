<?php $layout = 'app'; ?>

<header class="page-header">
    <h1 class="page-title">
        <?= htmlspecialchars($email['subject'] ?? '(No Subject)') ?>
    </h1>
    <a href="<?= $prefix ?>/email" class="btn btn-sm" title="Back to email">
        ← Back
    </a>
</header>

<article class="email-meta">
    <div>
        <strong>From:</strong> 
        <code><?= htmlspecialchars($email['from_address']) ?></code>
    </div>
    <div style="margin-top: 8px;">
        <strong>To:</strong> 
        <code><?= htmlspecialchars($email['to_address']) ?></code>
    </div>
    <time class="text-muted text-sm" style="display: block; margin-top: 12px;">
        <?= htmlspecialchars($email['created_at']) ?>
    </time>
</article>

<section class="email-body">
    <pre class="email-text"><?= htmlspecialchars($email['body'] ?? '') ?></pre>
</section>

<section style="margin-top: 24px;">
    <h2 class="section-title">Assign to Employee</h2>
    <form method="post" action="<?= $prefix ?>/email/assign/<?= $email['id'] ?>" class="form-row" aria-label="Assign email">
        <div class="form-col">
            <label for="employee_id" class="field-label">Employee</label>
            <select 
                id="employee_id"
                name="employee_id" 
                class="field-input"
                title="Assign this email to an employee"
            >
                <option value="">Select an employee…</option>
                <?php foreach (($employees ?? []) as $e): ?>
                <option 
                    value="<?= htmlspecialchars((string)$e['id']) ?>" 
                    <?= (($email['employee_id'] ?? '') == $e['id']) ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-col" style="display: flex; align-items: flex-end;">
            <button type="submit" class="btn btn-primary" title="Assign email">
                Assign
            </button>
        </div>
    </form>
</section>
