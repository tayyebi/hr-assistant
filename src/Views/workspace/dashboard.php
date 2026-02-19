<?php $layout = 'app'; ?>

<header class="page-header">
    <h1 class="page-title">Dashboard</h1>
</header>

<section>
    <div class="card-grid">
        <article class="card">
            <h2 class="card-label">Total Employees</h2>
            <p class="card-value">
                <?php
                $count = $auth->hasRole('workspace_admin', 'hr_specialist')
                    ? count($db ?? [])
                    : '—';
                echo htmlspecialchars((string)$count);
                ?>
            </p>
        </article>
    </div>
</section>
