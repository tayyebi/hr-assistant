<div class="section">
    <div class="level mb-5 is-mobile">
        <div class="level-left">
            <div>
                <h2 class="title is-3 mb-1">Employees</h2>
                <p class="subtitle is-6">Manage your organization's workforce.</p>
            </div>
        </div>
        <div class="level-right">
            <a href="<?php echo \App\Core\UrlHelper::workspace('/employees/create'); ?>" class="button is-primary">
                <span class="icon is-small">
                    <?php Icon::render('plus', 18, 18); ?>
                </span>
                <span>Add Employee</span>
            </a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="notification is-success is-light mb-5">
            <a href="#" class="delete"></a>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Search -->
    <form method="GET" action="<?php echo \App\Core\UrlHelper::workspace('/employees/'); ?>" class="mb-5">
        <div class="field">
            <div class="control has-icons-left">
                <input class="input" type="search" name="search" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
                <span class="icon is-left">
                    <?php Icon::render('search', 20, 20); ?>
                </span>
            </div>
        </div>
    </form>

    <!-- Employee Table -->
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Role</th>
                    <th>Accounts</th>
                    <th>Hired Date</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($employees)): ?>
                    <tr>
                        <td colspan="5" class="has-text-centered has-text-grey-light p-5">
                            No employees found matching your search.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td>
                                <div class="level is-mobile m-0">
                                    <div class="level-left">
                                        <div class="level-item">
                                            <div class="avatar">
                                                <?php echo strtoupper(substr($emp['full_name'], 0, 1)); ?>
                                            </div>
                                        </div>
                                        <div class="level-item">
                                            <strong><?php echo htmlspecialchars($emp['full_name']); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($emp['position']); ?></td>
                            <td>
                                <?php 
                                $accountCount = is_array($emp['accounts']) ? count($emp['accounts']) : 0;
                                if ($accountCount > 0): ?>
                                    <span class="tag is-success is-light"><?php echo $accountCount; ?> linked</span>
                                <?php else: ?>
                                    <span class="has-text-grey-light is-size-7">No accounts</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($emp['hired_date']); ?></td>
                            <td class="text-right">
                                <a href="<?php echo \App\Core\UrlHelper::workspace('/employees/edit/' . htmlspecialchars($emp['id'])); ?>" class="button is-ghost is-small">
                                    <span class="icon is-small">
                                        <?php Icon::render('edit', 16, 16); ?>
                                    </span>
                                </a>
                                <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/employees/delete/'); ?>" class="display-inline">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($emp['id']); ?>">
                                    <button type="submit" class="button is-ghost is-small">
                                        <span class="icon is-small has-text-danger">
                                            <?php Icon::render('trash', 16, 16); ?>
                                        </span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

