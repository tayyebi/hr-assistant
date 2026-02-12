<?php $layout = 'admin'; ?>
<h1 class="page-title">Audit Detail</h1>
<div class="card">
    <div class="card-label">Action</div>
    <div style="margin-bottom:8px;"><code><?= htmlspecialchars($log['action']) ?></code></div>
    <div class="card-label">By</div>
    <div><?= htmlspecialchars($log['display_name'] ?? $log['email'] ?? 'System') ?> — <span class="text-muted"><?= htmlspecialchars($log['created_at']) ?></span></div>
    <div class="card-label" style="margin-top:12px;">Entity</div>
    <div><?= htmlspecialchars(($log['entity_type'] ?? '') . ($log['entity_id'] ? '#' . $log['entity_id'] : '')) ?></div>
    <div class="card-label" style="margin-top:12px;">IP</div>
    <div class="text-muted"><?= htmlspecialchars($log['ip_address'] ?? '') ?></div>
    <div class="card-label" style="margin-top:12px;">Metadata</div>
    <pre style="background:#f6f8fa;padding:12px;border-radius:6px;white-space:pre-wrap;"><?= htmlspecialchars($log['meta'] ?? '') ?></pre>
</div>