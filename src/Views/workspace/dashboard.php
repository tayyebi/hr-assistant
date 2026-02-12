<?php $layout = 'app'; ?>
<h1 class="page-title">Dashboard</h1>
<div class="card-grid">
    <div class="card">
        <div class="card-label">Employees</div>
        <div class="card-value">
<?php
$count = $auth->hasRole('workspace_admin', 'hr_specialist')
    ? count($db ?? [])
    : '—';
echo $count;
?>
        </div>
    </div>
</div>
