<div class="section">
    <div class="level">
        <div>
            <h2 class="title">Reports & Analytics</h2>
            <p class="subtitle">Generate and view organizational reports and metrics.</p>
        </div>
    </div>
</div>

<main class="section">
    <div class="columns is-multiline">
        <div class="column is-two-thirds-desktop is-full-tablet">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">Reports & Analytics</p>
                </header>
                <div class="card-content">
                    <p class="has-text-grey-light">Comprehensive reporting and business intelligence.</p>
                    
                    <!-- Report Overview -->
                    <div class="columns is-multiline mt-1">
                        <div class="column is-half-tablet is-one-quarter-desktop">
                            <div class="box has-background-grey-light">
                                <p class="heading is-6">Employees</p>
                                <p class="title is-4"><?php echo $reportData['employees']['total_employees'] ?? 0; ?></p>
                                <small class="has-text-grey-light"><?php echo ($reportData['employees']['new_hires'] ?? 0); ?> new this month</small>
                            </div>
                        </div>
                        
                        <div class="column is-half-tablet is-one-quarter-desktop">
                            <div class="box has-background-grey-light">
                                <p class="heading is-6">Assets</p>
                                <p class="title is-4"><?php echo $reportData['assets']['total_assets'] ?? 0; ?></p>
                                <small class="has-text-grey-light"><?php echo ($reportData['assets']['active_assets'] ?? 0); ?> active</small>
                            </div>
                        </div>
                        
                        <div class="column is-half-tablet is-one-quarter-desktop">
                            <div class="box has-background-grey-light">
                                <p class="heading is-6">Messages</p>
                                <p class="title is-4"><?php echo $reportData['messages']['total_messages'] ?? 0; ?></p>
                                <small class="has-text-grey-light"><?php echo ($reportData['messages']['recent_messages'] ?? 0); ?> this week</small>
                            </div>
                        </div>
                        
                        <div class="column is-half-tablet is-one-quarter-desktop">
                            <div class="box has-background-grey-light">
                                <p class="heading is-6">Jobs</p>
                                <p class="title is-4"><?php echo $reportData['jobs']['total_jobs'] ?? 0; ?></p>
                                <small class="has-text-grey-light"><?php echo ($reportData['jobs']['completed_jobs'] ?? 0); ?> completed</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Report Generation -->
                    <div class="mt-2">
                        <h4 class="title is-5">Generate Report</h4>
                        <form method="POST" action="<?php echo \App\Core\UrlHelper::workspace('/reports/generate'); ?>">
                            <div class="columns is-multiline">
                                <div class="column is-one-third-tablet">
                                    <div class="field">
                                        <label class="label">Report Type</label>
                                        <div class="control">
                                            <span class="select is-fullwidth">
                                                <select name="report_type" required>
                                                    <option value="summary">Executive Summary</option>
                                                    <option value="employee_performance">Employee Performance</option>
                                                    <option value="asset_utilization">Asset Utilization</option>
                                                    <option value="communication_metrics">Communication Metrics</option>
                                                </select>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-one-third-tablet">
                                    <div class="field">
                                        <label class="label">Date From</label>
                                        <div class="control">
                                            <input class="input" type="date" name="date_from" value="<?php echo date('Y-m-01'); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-one-third-tablet">
                                    <div class="field">
                                        <label class="label">Date To</label>
                                        <div class="control">
                                            <input class="input" type="date" name="date_to" value="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-full">
                                    <div class="buttons">
                                        <button type="submit" class="button is-primary">Generate Report</button>
                                        <a href="<?php echo \App\Core\UrlHelper::withQuery(\App\Core\UrlHelper::workspace('/reports/export/'), ['format' => 'csv']); ?>" class="button">Export CSV</a>
                                        <a href="<?php echo \App\Core\UrlHelper::withQuery(\App\Core\UrlHelper::workspace('/reports/export/'), ['format' => 'pdf']); ?>" class="button">Export PDF</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Report Results -->
                    <div id="report-results" class="display-none mt-2">
                        <h4 class="title is-5">Report Results</h4>
                        <div id="report-content" class="box has-background-grey-light"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column is-one-third-desktop is-full-tablet">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">Quick Insights</p>
                </header>
                <div class="card-content has-text-centered has-text-grey">
                    <p><strong>Real-time Dashboard</strong></p>
                    <p>Charts and visualizations will display here</p>
                    <p>Data updates automatically</p>
                    
                    <div class="mt-1 text-left">
                        <h5 class="title is-6">Available Report Types:</h5>
                        <ul class="ml-1rem">
                            <li>Performance Analytics</li>
                            <li>Asset Utilization</li>
                            <li>Communication Metrics</li>
                            <li>Custom Reports</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

