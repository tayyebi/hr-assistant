<h2>Calendar – <?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></h2>
<p>
    <a href="<?= $prefix ?>/calendar?year=<?= $month === 1 ? $year - 1 : $year ?>&month=<?= $month === 1 ? 12 : $month - 1 ?>" class="btn btn-sm">← Prev</a>
    <strong><?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></strong>
    <a href="<?= $prefix ?>/calendar?year=<?= $month === 12 ? $year + 1 : $year ?>&month=<?= $month === 12 ? 1 : $month + 1 ?>" class="btn btn-sm">Next →</a>
</p>

<?php
$phpFirstOfMonth = sprintf('%04d-%02d-01', $year, $month);
// prefer cal_days_in_month when available; otherwise fall back to date('t') so views render
// even when the PHP calendar extension is not installed in the container.
if (function_exists('cal_days_in_month')) {
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
} else {
    $daysInMonth = (int)date('t', strtotime($phpFirstOfMonth));
}
$firstDow = (int)date('w', mktime(0, 0, 0, $month, 1, $year));
$eventsByDay = [];
foreach ($events as $ev) {
    $s = max(1, (int)date('j', strtotime($ev['start_at'])));
    $e = min($daysInMonth, (int)date('j', strtotime($ev['end_at'])));
    for ($d = $s; $d <= $e; $d++) { $eventsByDay[$d][] = $ev; }
}
?>

<table style="table-layout:fixed;">
<thead><tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr></thead>
<tbody>
<tr>
<?php for ($i = 0; $i < $firstDow; $i++): ?><td style="background:#f5f5f5;"></td><?php endfor; ?>
<?php for ($day = 1; $day <= $daysInMonth; $day++):
    $dow = ($firstDow + $day - 1) % 7;
    if ($dow === 0 && $day > 1): ?></tr><tr><?php endif; ?>
    <td style="vertical-align:top;height:80px;padding:4px;<?= date('j') == $day && date('n') == $month && date('Y') == $year ? 'background:#e8f4fd;' : '' ?>">
        <strong><?= $day ?></strong>
        <?php foreach ($eventsByDay[$day] ?? [] as $ev): ?>
            <div style="font-size:11px;background:<?= htmlspecialchars($ev['color']) ?>;color:#fff;padding:1px 4px;margin:1px 0;border-radius:2px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;" title="<?= htmlspecialchars($ev['title']) ?>">
                <?= htmlspecialchars($ev['title']) ?>
            </div>
        <?php endforeach; ?>
    </td>
<?php endfor; ?>
<?php $remaining = 7 - (($firstDow + $daysInMonth) % 7); if ($remaining < 7): for ($i = 0; $i < $remaining; $i++): ?><td style="background:#f5f5f5;"></td><?php endfor; endif; ?>
</tr>
</tbody>
</table>

<?php if ($isHR): ?>
<h3>Add Event</h3>
<form method="post" action="<?= $prefix ?>/calendar/event">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group"><label>Title</label><input name="title" required></div>
        <div class="form-group"><label>Type</label>
            <select name="type"><option value="event">Event</option><option value="meeting">Meeting</option><option value="holiday">Holiday</option><option value="birthday">Birthday</option><option value="reminder">Reminder</option></select>
        </div>
        <div class="form-group"><label>Start</label><input type="datetime-local" name="start_at" required></div>
        <div class="form-group"><label>End</label><input type="datetime-local" name="end_at" required></div>
        <div class="form-group"><label>Location</label><input name="location"></div>
        <div class="form-group"><label>Color</label><input type="color" name="color" value="#3498db"></div>
        <div class="form-group"><label>Description</label><textarea name="description" rows="2"></textarea></div>
        <div class="form-group" style="display:flex;gap:12px;align-items:center;padding-top:20px;">
            <label><input type="checkbox" name="all_day"> All day</label>
            <label><input type="checkbox" name="is_public" checked> Public</label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Create Event</button>
</form>
<?php endif; ?>
