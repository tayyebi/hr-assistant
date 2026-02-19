<header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title" style="margin: 0;">Leave Management</h1>
    <?php if ($isHR): ?>
    <nav style="display: flex; gap: 12px;">
        <a href="<?= $prefix ?>/leave/balances" class="btn btn-sm" title="View leave balances">
            Balances
        </a>
        <a href="<?= $prefix ?>/leave/settings" class="btn btn-sm" title="Manage leave settings">
            Settings
        </a>
    </nav>
    <?php endif; ?>
</header>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Leave Requests Table -->
    <section>
        <h2 class="section-title">
            <?= $isHR ? 'All Leave Requests' : 'My Leave Requests' ?>
        </h2>
        
        <?php if (empty($requests)): ?>
        <p class="text-muted">No leave requests found.</p>
        <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="table" role="grid">
                <thead>
                    <tr>
                        <?php if ($isHR): ?><th scope="col">Employee</th><?php endif; ?>
                        <th scope="col">Type</th>
                        <th scope="col">From</th>
                        <th scope="col">To</th>
                        <th scope="col">Days</th>
                        <th scope="col">Status</th>
                        <?php if ($isHR): ?><th scope="col">Action</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $r): ?>
                    <tr>
                        <?php if ($isHR): ?>
                        <td>
                            <strong>
                                <?= htmlspecialchars(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?>
                            </strong>
                        </td>
                        <?php endif; ?>
                        <td>
                            <span style="border-left: 3px solid <?= htmlspecialchars($r['color'] ?? '#3498db') ?>; padding-left: 8px; font-weight: 600;">
                                <?= htmlspecialchars($r['type_name']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($r['start_date']) ?></td>
                        <td><?= htmlspecialchars($r['end_date']) ?></td>
                        <td><?= htmlspecialchars((string)$r['days']) ?></td>
                        <td>
                            <?php 
                            $status = $r['status'];
                            $statusColor = match($status) {
                                'approved' => 'var(--success)',
                                'rejected' => 'var(--danger)',
                                'cancelled' => 'var(--muted)',
                                default => 'var(--warning)'
                            };
                            ?>
                            <span style="color: <?= $statusColor ?>; font-weight: 600;">
                                <?= htmlspecialchars(ucfirst($status)) ?>
                            </span>
                        </td>
                        <?php if ($isHR && $r['status'] === 'pending'): ?>
                        <td>
                            <form 
                                method="post" 
                                action="<?= $prefix ?>/leave/review/<?= $r['id'] ?>" 
                                class="flex gap-2"
                                style="display: flex; gap: 6px; margin: 0;"
                            >
                                <input 
                                    type="text"
                                    name="review_note" 
                                    placeholder="Note" 
                                    class="field-input"
                                    style="width: 100px; padding: 4px 8px; font-size: 12px;"
                                    title="Optional review note"
                                >
                                <button 
                                    type="submit"
                                    name="action" 
                                    value="approve" 
                                    class="btn btn-sm btn-success"
                                    title="Approve leave request"
                                >
                                    ✓
                                </button>
                                <button 
                                    type="submit"
                                    name="action" 
                                    value="reject" 
                                    class="btn btn-sm btn-danger"
                                    title="Reject leave request"
                                >
                                    ✗
                                </button>
                            </form>
                        </td>
                        <?php elseif ($isHR): ?><td></td><?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>

    <!-- Request Leave Form -->
    <section>
        <h2 class="section-title">Request Leave</h2>
        <form method="post" action="<?= $prefix ?>/leave/request" class="form-stack" aria-label="Request leave">
            <div class="form-group">
                <label for="leave_type_id" class="field-label">Leave Type *</label>
                <select 
                    id="leave_type_id"
                    name="leave_type_id" 
                    class="field-input"
                    required
                    aria-required="true"
                >
                    <option value="">Select type…</option>
                    <?php foreach ($leaveTypes as $lt): ?>
                    <option value="<?= htmlspecialchars((string)$lt['id']) ?>">
                        <?= htmlspecialchars($lt['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="start_date" class="field-label">Start Date *</label>
                <input 
                    type="date" 
                    id="start_date"
                    name="start_date" 
                    class="field-input"
                    required
                    aria-required="true"
                >
            </div>

            <div class="form-group">
                <label for="end_date" class="field-label">End Date *</label>
                <input 
                    type="date" 
                    id="end_date"
                    name="end_date" 
                    class="field-input"
                    required
                    aria-required="true"
                >
            </div>

            <div class="form-group">
                <label for="days" class="field-label">Days *</label>
                <input 
                    type="number" 
                    id="days"
                    name="days" 
                    step="0.5" 
                    min="0.5" 
                    value="1" 
                    class="field-input"
                    required
                    aria-required="true"
                >
            </div>

            <div class="form-group">
                <label for="reason" class="field-label">Reason</label>
                <textarea 
                    id="reason"
                    name="reason" 
                    rows="3"
                    class="field-input"
                    placeholder="Explain your leave request (optional)"
                ></textarea>
            </div>

            <button type="submit" class="btn btn-primary" title="Submit leave request">
                Submit Request
            </button>
        </form>
    </section>
</div>
