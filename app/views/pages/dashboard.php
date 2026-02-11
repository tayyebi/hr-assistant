<div class="section">
    <div class="level">
        <div class="level-item">
            <div>
                <h2 class="title is-3">HR Command Center</h2>
                <p class="subtitle is-6">Welcome back. Here's what's happening today.</p>
            </div>
        </div>
    </div>

    <div class="columns is-multiline">
        <!-- Sentiment Overview Card -->
        <div class="column is-two-thirds-desktop is-full-mobile">
            <div class="card">
                <div class="card-header">
                    <div class="level is-mobile" style="width: 100%;">
                        <div class="level-left">
                            <h3 class="title is-5 mb-0">
                                <span class="icon-text">
                                    <span class="icon">
                                        <?php \App\Core\Icon::render('trending-up', 20, 20); ?>
                                    </span>
                                    <span>Team Sentiment Overview</span>
                                </span>
                            </h3>
                        </div>
                        <div class="level-right">
                            <span class="tag is-light">Last 24h</span>
                        </div>
                    </div>
                </div>

                <div class="card-content">
                    <?php if (array_sum($sentimentStats) > 0): ?>
                        <div>
                            <div class="level mb-2">
                                <div class="level-left">
                                    <span class="icon-text">
                                        <span class="sentiment-indicator happy"></span>
                                        <span>Happy</span>
                                    </span>
                                </div>
                                <div class="level-right">
                                    <span class="tag is-success is-light"><?php echo $sentimentStats['happy']; ?></span>
                                </div>
                            </div>
                            <div class="level mb-2">
                                <div class="level-left">
                                    <span class="icon-text">
                                        <span class="sentiment-indicator neutral"></span>
                                        <span>Neutral</span>
                                    </span>
                                </div>
                                <div class="level-right">
                                    <span class="tag is-light"><?php echo $sentimentStats['neutral']; ?></span>
                                </div>
                            </div>
                            <div class="level">
                                <div class="level-left">
                                    <span class="icon-text">
                                        <span class="sentiment-indicator sad"></span>
                                        <span>Sad</span>
                                    </span>
                                </div>
                                <div class="level-right">
                                    <span class="tag is-danger is-light"><?php echo $sentimentStats['sad']; ?></span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="has-text-centered p-5">
                            <p class="has-text-grey-light">No sentiment data available for today.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Reminders Card -->
        <div class="column is-one-third-desktop is-full-mobile">
            <div class="card">
                <div class="card-header">
                    <h3 class="title is-5 mb-0">
                        <span class="icon-text">
                            <span class="icon">
                                <?php \App\Core\Icon::render('bell', 20, 20); ?>
                            </span>
                            <span>Reminders</span>
                        </span>
                    </h3>
                </div>

                <div class="card-content" style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($upcomingBirthdays)): ?>
                        <p class="has-text-grey-light has-text-centered">No upcoming events.</p>
                    <?php else: ?>
                        <?php foreach ($upcomingBirthdays as $emp): ?>
                            <div class="level mb-3 is-mobile">
                                <div class="level-left">
                                    <div class="level-item">
                                        <div class="avatar">
                                            <?php echo strtoupper(substr($emp['full_name'], 0, 1)); ?>
                                        </div>
                                    </div>
                                    <div class="level-item">
                                        <div>
                                            <strong><?php echo htmlspecialchars($emp['full_name']); ?></strong>
                                            <br>
                                            <small class="has-text-grey">Birthday: <?php echo htmlspecialchars($emp['birthday']); ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="columns is-multiline mt-5">
        <div class="column is-one-third-desktop is-half-tablet is-full-mobile">
            <div class="card">
                <div class="card-content has-text-centered">
                    <p class="heading">Total Employees</p>
                    <p class="title is-2 has-text-primary"><?php echo count($employees); ?></p>
                </div>
            </div>
        </div>

        <div class="column is-one-third-desktop is-half-tablet is-full-mobile">
            <div class="card">
                <div class="card-content has-text-centered">
                    <p class="heading">Upcoming Birthdays</p>
                    <p class="title is-2 has-text-warning"><?php echo count($upcomingBirthdays); ?></p>
                </div>
            </div>
        </div>

        <div class="column is-one-third-desktop is-half-tablet is-full-mobile">
            <div class="card">
                <div class="card-content has-text-centered">
                    <p class="heading">Linked Accounts</p>
                    <p class="title is-2 has-text-success">
                        <?php 
                            $totalAccounts = 0;
                            foreach ($employees as $emp) {
                                $accounts = is_array($emp['accounts']) ? $emp['accounts'] : [];
                                $totalAccounts += count(array_filter($accounts));
                            }
                            echo $totalAccounts;
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
