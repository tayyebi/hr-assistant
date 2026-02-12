<div class="page-header">
    <h1>New Payroll Run</h1>
    <a href="<?= $prefix ?>/payroll" class="btn">Back</a>
</div>

<section>
    <h2>Configure Payroll Run</h2>
    <form method="post">
        <div class="form-group">
            <label>Period Start</label>
            <input type="date" name="period_start" required>
        </div>
        <div class="form-group">
            <label>Period End</label>
            <input type="date" name="period_end" required>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="include_all" value="1" checked>
                Include All Active Employees
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Run Payroll</button>
    </form>
</section>
