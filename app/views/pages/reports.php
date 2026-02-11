<header>
    <div>
        <h2>Reports & Analytics</h2>
        <p>Generate and view organizational reports and metrics.</p>
    </div>
</header>

<main>
    <section data-grid="2-1">
        <article>
            <h3>📊 Reports & Analytics</h3>
            <p>Comprehensive reporting and business intelligence.</p>
            
            <!-- Report Overview -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-md); margin-bottom: var(--spacing-lg);">
                <div style="padding: var(--spacing-md); background: var(--surface-variant); border-radius: 8px;">
                    <h4 style="margin: 0 0 var(--spacing-xs) 0;">👥 Employees</h4>
                    <p style="margin: 0; font-size: 1.5em; font-weight: bold;"><?php echo $reportData['employees']['total_employees'] ?? 0; ?></p>
                    <small style="color: var(--text-muted);"><?php echo ($reportData['employees']['new_hires'] ?? 0); ?> new this month</small>
                </div>
                
                <div style="padding: var(--spacing-md); background: var(--surface-variant); border-radius: 8px;">
                    <h4 style="margin: 0 0 var(--spacing-xs) 0;">💻 Assets</h4>
                    <p style="margin: 0; font-size: 1.5em; font-weight: bold;"><?php echo $reportData['assets']['total_assets'] ?? 0; ?></p>
                    <small style="color: var(--text-muted);"><?php echo ($reportData['assets']['active_assets'] ?? 0); ?> active</small>
                </div>
                
                <div style="padding: var(--spacing-md); background: var(--surface-variant); border-radius: 8px;">
                    <h4 style="margin: 0 0 var(--spacing-xs) 0;">💬 Messages</h4>
                    <p style="margin: 0; font-size: 1.5em; font-weight: bold;"><?php echo $reportData['messages']['total_messages'] ?? 0; ?></p>
                    <small style="color: var(--text-muted);"><?php echo ($reportData['messages']['recent_messages'] ?? 0); ?> this week</small>
                </div>
                
                <div style="padding: var(--spacing-md); background: var(--surface-variant); border-radius: 8px;">
                    <h4 style="margin: 0 0 var(--spacing-xs) 0;">⚡ Jobs</h4>
                    <p style="margin: 0; font-size: 1.5em; font-weight: bold;"><?php echo $reportData['jobs']['total_jobs'] ?? 0; ?></p>
                    <small style="color: var(--text-muted);"><?php echo ($reportData['jobs']['completed_jobs'] ?? 0); ?> completed</small>
                </div>
            </div>
            
            <!-- Report Generation -->
            <div>
                <h4>📈 Generate Report</h4>
                <form onsubmit="generateReport(event)">
                    <div data-grid="3">
                        <div>
                            <label>Report Type</label>
                            <select name="report_type" required>
                                <option value="summary">Executive Summary</option>
                                <option value="employee_performance">Employee Performance</option>
                                <option value="asset_utilization">Asset Utilization</option>
                                <option value="communication_metrics">Communication Metrics</option>
                            </select>
                        </div>
                        <div>
                            <label>Date From</label>
                            <input type="date" name="date_from" value="<?php echo date('Y-m-01'); ?>">
                        </div>
                        <div>
                            <label>Date To</label>
                            <input type="date" name="date_to" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <footer style="margin-top: var(--spacing-md);">
                        <button type="submit">Generate Report</button>
                        <a href="<?php echo \App\Core\UrlHelper::withQuery(\App\Core\UrlHelper::workspace('/reports/export/'), ['format' => 'csv']); ?>" style="margin-left: var(--spacing-sm);">Export CSV</a>
                        <a href="<?php echo \App\Core\UrlHelper::withQuery(\App\Core\UrlHelper::workspace('/reports/export/'), ['format' => 'pdf']); ?>" style="margin-left: var(--spacing-sm);">Export PDF</a>
                    </footer>
                </form>
            </div>
            
            <!-- Report Results -->
            <div id="report-results" style="display: none;">
                <h4>📋 Report Results</h4>
                <div id="report-content"></div>
            </div>
        </article>
        
        <article>
            <h3>📈 Quick Insights</h3>
            <div style="background: #f3f4f6; padding: 2rem; border-radius: 8px; text-align: center; color: #6b7280;">
                <p><strong>Real-time Dashboard</strong></p>
                <p>Charts and visualizations will display here</p>
                <p>Data updates automatically</p>
                
                <div style="margin-top: 1rem; text-align: left;">
                    <h5 style="margin-bottom: 0.5rem;">Available Report Types:</h5>
                    <ul style="margin: 0; padding-left: 1rem;">
                        <li>Performance Analytics</li>
                        <li>Asset Utilization</li>
                        <li>Communication Metrics</li>
                        <li>Custom Reports</li>
                    </ul>
                </div>
            </div>
        </article>
    </section>
</main>

<script>
function generateReport(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('<?php echo \App\Core\UrlHelper::workspace("/reports/generate/"); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('report-results').style.display = 'block';
        document.getElementById('report-content').innerHTML = formatReportData(data);
    })
    .catch(error => {
        console.error('Error generating report:', error);
        alert('Failed to generate report. Please try again.');
    });
}

function formatReportData(data) {
    let html = '<div>';
    html += '<h5>Report: ' + (data.type || 'Unknown') + '</h5>';
    html += '<p>Generated: ' + (data.generated_at || new Date().toLocaleString()) + '</p>';
    
    if (data.data) {
        html += '<pre style="background: var(--surface-variant); padding: var(--spacing-md); border-radius: 8px; overflow: auto;">';
        html += JSON.stringify(data.data, null, 2);
        html += '</pre>';
    }
    
    html += '</div>';
    return html;
}
</script>