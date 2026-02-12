<div class="page-header">
    <h1>Leave Request</h1>
    <a href="<?= $prefix ?>/leave" class="btn">Back</a>
</div>

<section>
    <h2>Request Details</h2>
    <p><strong>Employee:</strong> <?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?></p>
    <p><strong>Leave Type:</strong> <?= htmlspecialchars($request['leave_type_name']) ?></p>
    <p><strong>Period:</strong> <?= htmlspecialchars($request['start_date']) ?> to <?= htmlspecialchars($request['end_date']) ?></p>
    <p><strong>Total Days:</strong> <?= htmlspecialchars($request['total_days']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($request['status']) ?></p>
    <?php if ($request['reason']): ?>
        <p><strong>Reason:</strong> <?= htmlspecialchars($request['reason']) ?></p>
    <?php endif; ?>
</section>

<?php if ($request['status'] === 'pending'): ?>
    <section>
        <h2>Actions</h2>
        <form method="post" action="<?= $prefix ?>/leave/approve/<?= $request['id'] ?>" style="display: inline;">
            <button type="submit" class="btn btn-primary">Approve</button>
        </form>
        <form method="post" action="<?= $prefix ?>/leave/reject/<?= $request['id'] ?>" style="display: inline; margin-left: 10px;">
            <button type="submit" class="btn">Reject</button>
        </form>
    </section>
<?php endif; ?>
