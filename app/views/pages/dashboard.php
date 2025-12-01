<header>
    <div>
        <h2>HR Command Center</h2>
        <p>Welcome back. Here's what's happening today.</p>
    </div>
</header>

<section data-grid="2-1">
    <!-- Sentiment Overview Card -->
    <article>
        <header>
            <h3>
                <?php Icon::render('trending-up', 20, 20, 'display: inline; vertical-align: middle; margin-right: 0.5rem; color: var(--color-primary);'); ?>
                Team Sentiment Overview
            </h3>
            <mark>Last 24h</mark>
        </header>

        <?php if (array_sum($sentimentStats) > 0): ?>
            <section data-stats>
                <div>
                    <span data-color="happy"></span>
                    <span>Happy: <?php echo $sentimentStats['happy']; ?></span>
                </div>
                <div>
                    <span data-color="neutral"></span>
                    <span>Neutral: <?php echo $sentimentStats['neutral']; ?></span>
                </div>
                <div>
                    <span data-color="sad"></span>
                    <span>Sad: <?php echo $sentimentStats['sad']; ?></span>
                </div>
            </section>
        <?php else: ?>
            <section data-empty>
                <p>No sentiment data available for today.</p>
            </section>
        <?php endif; ?>
    </article>

    <!-- Reminders Card -->
    <article>
        <header>
            <h3>
                <?php Icon::render('bell', 20, 20, 'display: inline; vertical-align: middle; margin-right: 0.5rem; color: var(--color-warning);'); ?>
                Reminders
            </h3>
        </header>

        <?php if (empty($upcomingBirthdays)): ?>
            <p>No upcoming events.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($upcomingBirthdays as $emp): ?>
                    <li>
                        <figure data-avatar>
                            <?php echo strtoupper(substr($emp['full_name'], 0, 1)); ?>
                        </figure>
                        <div>
                            <strong><?php echo htmlspecialchars($emp['full_name']); ?></strong>
                            <p style="margin: 0; font-size: 0.75rem;">
                                Birthday: <?php echo htmlspecialchars($emp['birthday']); ?>
                            </p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>
</section>

<!-- Quick Stats -->
<section data-grid="3" style="margin-top: var(--spacing-lg);">
    <article>
        <h4>Total Employees</h4>
        <p style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">
            <?php echo count($employees); ?>
        </p>
    </article>

    <article>
        <h4>Upcoming Birthdays</h4>
        <p style="font-size: 2rem; font-weight: 700; color: var(--color-warning); margin: 0;">
            <?php echo count($upcomingBirthdays); ?>
        </p>
    </article>

    <article>
        <h4>Active Channels</h4>
        <p style="font-size: 2rem; font-weight: 700; color: var(--color-success); margin: 0;">
            <?php 
                $withTelegram = count(array_filter($employees, fn($e) => !empty($e['telegram_chat_id'])));
                $withEmail = count(array_filter($employees, fn($e) => !empty($e['email'])));
                echo $withTelegram + $withEmail;
            ?>
        </p>
    </article>
</section>
