<div class="section">
    <div class="level mb-5">
        <div class="level-left">
            <div>
                <h2 class="title is-3 mb-1"><?php echo empty($employee) ? 'Add Employee' : 'Edit Employee'; ?></h2>
                <p class="subtitle is-6"><?php echo empty($employee) ? 'Create a new employee record' : 'Update employee information'; ?></p>
            </div>
        </div>
        <div class="level-right">
            <a href="<?php echo \App\Core\UrlHelper::workspace('/employees'); ?>" class="button is-light">
                <span class="icon is-small">
                    <?php Icon::render('arrow-left', 18, 18); ?>
                </span>
                <span>Back</span>
            </a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="notification is-danger is-light mb-5">
            <a href="#" class="delete"></a>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="columns">
        <div class="column is-half-desktop is-full-tablet is-full-mobile">
            <div class="card">
                <div class="card-content">
                    <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace(empty($employee) ? '/employees/create' : '/employees/update'); ?>">
                        <?php if (!empty($employee)): ?>
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($employee['id']); ?>">
                        <?php endif; ?>

                        <div class="field">
                            <label class="field-label">Full Name</label>
                            <div class="control">
                                <input class="input" type="text" name="full_name" required value="<?php echo htmlspecialchars($employee['full_name'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="field">
                            <label class="field-label">Position</label>
                            <div class="control">
                                <input class="input" type="text" name="position" required value="<?php echo htmlspecialchars($employee['position'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="field">
                            <label class="field-label">Email</label>
                            <div class="control">
                                <input class="input" type="email" name="email" required value="<?php echo htmlspecialchars($employee['email'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="field">
                            <label class="field-label">Birthday</label>
                            <div class="control">
                                <input class="input" type="date" name="birthday" value="<?php echo htmlspecialchars($employee['birthday'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="field">
                            <label class="field-label">Hired Date</label>
                            <div class="control">
                                <input class="input" type="date" name="hired_date" required value="<?php echo htmlspecialchars($employee['hired_date'] ?? date('Y-m-d')); ?>">
                            </div>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <?php echo empty($employee) ? 'Add Employee' : 'Update Employee'; ?>
                                </button>
                            </div>
                            <div class="control">
                                <a href="<?php echo \App\Core\UrlHelper::workspace('/employees'); ?>" class="button is-light">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
