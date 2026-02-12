<?php
/**
 * Payroll plugin.
 * Salary structures with configurable earning/deduction components,
 * employee assignments, payroll runs, payslip generation.
 */

declare(strict_types=1);

namespace Src\Plugins\Payroll;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

final class Plugin implements PluginInterface
{
    public function name(): string { return 'Payroll'; }
    public function requires(): array { return ['Core']; }
    public function sidebarItem(): ?array { return ['label' => 'Payroll', 'icon' => 'dollar', 'route' => '/payroll']; }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        /* ---- Payroll runs list ---- */
        $router->get('/payroll', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $runs = $db->fetchAll('SELECT * FROM payroll_runs WHERE tenant_id = ? ORDER BY period_start DESC', [$router->tenant()->id()]);
            $router->response()->html($router->view()->render('plugins/payroll/index', [
                'title' => 'Payroll', 'layout' => 'app', 'runs' => $runs,
            ]));
        });

        /* ---- View payroll run ---- */
        $router->get('/payroll/run/{id}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $run = $db->fetchOne('SELECT * FROM payroll_runs WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $tid]);
            if (!$run) { $router->response()->status(404)->html('<h1>Not found</h1>'); return; }
            $payslips = $db->fetchAll(
                'SELECT ps.*, e.first_name, e.last_name FROM payroll_payslips ps JOIN employees e ON e.id = ps.employee_id WHERE ps.run_id = ? ORDER BY e.last_name',
                [(int)$p['id']],
            );
            $router->response()->html($router->view()->render('plugins/payroll/run', [
                'title' => 'Payroll Run', 'layout' => 'app', 'run' => $run, 'payslips' => $payslips,
            ]));
        });

        /* ---- Create + process payroll run ---- */
        $router->post('/payroll/run', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $tid = $router->tenant()->id();
            $periodStart = trim((string)($_POST['period_start'] ?? ''));
            $periodEnd = trim((string)($_POST['period_end'] ?? ''));
            $db->query('INSERT INTO payroll_runs (tenant_id, period_start, period_end, created_by, status) VALUES (?,?,?,?,?)', [
                $tid, $periodStart, $periodEnd, $router->auth()->userId(), 'processing',
            ]);
            $runId = (int)$db->pdo()->lastInsertId();
            /* generate payslips */
            $assignments = $db->fetchAll(
                'SELECT a.*, s.base_amount, s.currency FROM payroll_employee_assignments a JOIN payroll_salary_structures s ON s.id = a.structure_id WHERE a.tenant_id = ? AND a.effective_from <= ? AND (a.effective_to IS NULL OR a.effective_to >= ?)',
                [$tid, $periodEnd, $periodStart],
            );
            foreach ($assignments as $asg) {
                $base = $asg['custom_base'] ? (float)$asg['custom_base'] : (float)$asg['base_amount'];
                $components = $db->fetchAll('SELECT * FROM payroll_components WHERE structure_id = ? ORDER BY sort_order', [$asg['structure_id']]);
                $earnings = 0; $deductions = 0; $breakdown = [];
                foreach ($components as $c) {
                    $amt = $c['calc_type'] === 'percentage' ? round($base * (float)$c['amount'] / 100, 2) : (float)$c['amount'];
                    $breakdown[] = ['name' => $c['name'], 'type' => $c['type'], 'amount' => $amt];
                    if ($c['type'] === 'earning') { $earnings += $amt; } else { $deductions += $amt; }
                }
                $net = $base + $earnings - $deductions;
                $db->query(
                    'INSERT INTO payroll_payslips (run_id, tenant_id, employee_id, base_salary, total_earnings, total_deductions, net_pay, breakdown_json) VALUES (?,?,?,?,?,?,?,?)',
                    [$runId, $tid, $asg['employee_id'], $base, $earnings, $deductions, $net, json_encode($breakdown)],
                );
            }
            $db->query('UPDATE payroll_runs SET status = "completed", completed_at = NOW() WHERE id = ?', [$runId]);
            AuditLog::record('payroll.run.completed', 'payroll_run', $runId);
            $router->response()->redirect($router->tenant()->pathPrefix() . '/payroll/run/' . $runId);
        });

        /* ---- Salary structures ---- */
        $router->get('/payroll/structures', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $structures = $db->fetchAll('SELECT * FROM payroll_salary_structures WHERE tenant_id = ? ORDER BY name', [$router->tenant()->id()]);
            $router->response()->html($router->view()->render('plugins/payroll/structures', [
                'title' => 'Salary Structures', 'layout' => 'app', 'structures' => $structures,
            ]));
        });

        $router->post('/payroll/structures', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $tid = $router->tenant()->id();
            $db->query('INSERT INTO payroll_salary_structures (tenant_id, name, base_amount, currency, pay_frequency) VALUES (?,?,?,?,?)', [
                $tid, trim((string)($_POST['name'] ?? '')), (float)($_POST['base_amount'] ?? 0),
                trim((string)($_POST['currency'] ?? 'USD')), trim((string)($_POST['pay_frequency'] ?? 'monthly')),
            ]);
            AuditLog::record('payroll.structure.created');
            $router->response()->redirect($router->tenant()->pathPrefix() . '/payroll/structures');
        });

        /* ---- Structure detail with components ---- */
        $router->get('/payroll/structure/{id}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $tid = $router->tenant()->id();
            $structure = $db->fetchOne('SELECT * FROM payroll_salary_structures WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $tid]);
            if (!$structure) { $router->response()->status(404)->html('<h1>Not found</h1>'); return; }
            $components = $db->fetchAll('SELECT * FROM payroll_components WHERE structure_id = ? ORDER BY sort_order', [(int)$p['id']]);
            $router->response()->html($router->view()->render('plugins/payroll/structure', [
                'title' => $structure['name'], 'layout' => 'app', 'structure' => $structure, 'components' => $components,
            ]));
        });

        $router->post('/payroll/structure/{id}/component', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $tid = $router->tenant()->id();
            $db->query('INSERT INTO payroll_components (tenant_id, structure_id, name, type, calc_type, amount, is_taxable, sort_order) VALUES (?,?,?,?,?,?,?,?)', [
                $tid, (int)$p['id'], trim((string)($_POST['name'] ?? '')),
                trim((string)($_POST['type'] ?? 'earning')), trim((string)($_POST['calc_type'] ?? 'fixed')),
                (float)($_POST['amount'] ?? 0), isset($_POST['is_taxable']) ? 1 : 0,
                (int)($_POST['sort_order'] ?? 0),
            ]);
            $router->response()->redirect($router->tenant()->pathPrefix() . '/payroll/structure/' . $p['id']);
        });

        /* ---- Employee assignments ---- */
        $router->get('/payroll/assignments', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $assignments = $db->fetchAll(
                'SELECT a.*, e.first_name, e.last_name, s.name AS structure_name FROM payroll_employee_assignments a JOIN employees e ON e.id = a.employee_id JOIN payroll_salary_structures s ON s.id = a.structure_id WHERE a.tenant_id = ? ORDER BY e.last_name',
                [$tid],
            );
            $employees = $db->tenantFetchAll('employees', 'is_active = 1');
            $structures = $db->fetchAll('SELECT * FROM payroll_salary_structures WHERE tenant_id = ? AND is_active = 1', [$tid]);
            $router->response()->html($router->view()->render('plugins/payroll/assignments', [
                'title' => 'Payroll Assignments', 'layout' => 'app',
                'assignments' => $assignments, 'employees' => $employees, 'structures' => $structures,
            ]));
        });

        $router->post('/payroll/assignments', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $db->query('INSERT INTO payroll_employee_assignments (tenant_id, employee_id, structure_id, custom_base, effective_from) VALUES (?,?,?,?,?)', [
                $tid, (int)($_POST['employee_id'] ?? 0), (int)($_POST['structure_id'] ?? 0),
                ($_POST['custom_base'] ?? '') !== '' ? (float)$_POST['custom_base'] : null,
                trim((string)($_POST['effective_from'] ?? date('Y-m-d'))),
            ]);
            AuditLog::record('payroll.assignment.created');
            $router->response()->redirect($router->tenant()->pathPrefix() . '/payroll/assignments');
        });

        /* ---- Settings redirect ---- */
        $router->get('/payroll/settings', function () use ($router): void {
            $router->response()->redirect($router->tenant()->pathPrefix() . '/payroll/structures');
        });
    }
}
