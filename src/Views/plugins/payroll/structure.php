<h2><?= htmlspecialchars($structure['name']) ?></h2>
<p>Base: <?= number_format((float)$structure['base_amount'], 2) ?> <?= htmlspecialchars($structure['currency']) ?> | <?= htmlspecialchars($structure['pay_frequency']) ?></p>

<h3>Components</h3>
<?php if (!empty($components)): ?>
<table><thead><tr><th>Name</th><th>Type</th><th>Calculation</th><th>Amount</th><th>Taxable</th></tr></thead><tbody>
<?php foreach ($components as $c): ?>
    <tr>
        <td><?= htmlspecialchars($c['name']) ?></td>
        <td><?= $c['type'] === 'earning' ? '<span style="color:green;">Earning</span>' : '<span style="color:red;">Deduction</span>' ?></td>
        <td><?= htmlspecialchars($c['calc_type']) ?></td>
        <td><?= $c['calc_type'] === 'percentage' ? $c['amount'] . '%' : number_format((float)$c['amount'], 2) ?></td>
        <td><?= $c['is_taxable'] ? 'Yes' : 'No' ?></td>
    </tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>

<h3>Add Component</h3>
<form method="post" action="<?= $prefix ?>/payroll/structure/<?= $structure['id'] ?>/component">
    <div class="form-group"><label>Name</label><input name="name" required></div>
    <div class="form-group"><label>Type</label>
        <select name="type"><option value="earning">Earning</option><option value="deduction">Deduction</option></select>
    </div>
    <div class="form-group"><label>Calculation</label>
        <select name="calc_type"><option value="fixed">Fixed Amount</option><option value="percentage">% of Base</option></select>
    </div>
    <div class="form-group"><label>Amount</label><input type="number" name="amount" step="0.01" value="0" required></div>
    <div class="form-group"><label><input type="checkbox" name="is_taxable" checked> Taxable</label></div>
    <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="0"></div>
    <button type="submit" class="btn btn-primary">Add</button>
</form>
