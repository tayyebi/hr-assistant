<?php
/**
 * Leave management plugin.
 * Leave types, balances, request/approval workflow.
 */

declare(strict_types=1);

namespace Src\Plugins\Leave;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

final class Plugin implements PluginInterface
{
    public function name(): string { return 'Leave'; }
    public function requires(): array { return ['Core']; }
    public function sidebarItem(): ?array { return ['label' => 'Leave', 'icon' => 'calendar-minus', 'route' => '/leave']; }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        /* ---- Dashboard: pending requests (HR) or own requests (employee) ---- */
        $router->get('/leave', function () use ($router, $db): void {
            $router->auth()->requireLogin($router->response());
            $tid = $router->tenant()->id();
            $userId = $router->auth()->userId();
            $isHR = $router->auth()->hasRole('workspace_admin') || $router->auth()->hasRole('hr_specialist');
            if ($isHR) {
                $requests = $db->fetchAll(
                    'SELECT r.*, e.first_name, e.last_name, lt.name AS type_name, lt.color FROM leave_requests r JOIN employees e ON e.id = r.employee_id JOIN leave_types lt ON lt.id = r.leave_type_id WHERE r.tenant_id = ? ORDER BY r.created_at DESC LIMIT 100',
                    [$tid],
                );
            } else {
                $emp = $db->fetchOne('SELECT id FROM employees WHERE tenant_id = ? AND user_id = ?', [$tid, $userId]);
                $requests = $emp ? $db->fetchAll(
                    'SELECT r.*, lt.name AS type_name, lt.color FROM leave_requests r JOIN leave_types lt ON lt.id = r.leave_type_id WHERE r.tenant_id = ? AND r.employee_id = ? ORDER BY r.created_at DESC',
                    [$tid, $emp['id']],
                ) : [];
            }
            $leaveTypes = $db->fetchAll('SELECT * FROM leave_types WHERE tenant_id = ? AND is_active = 1', [$tid]);
            $router->response()->html($router->view()->render('plugins/leave/index', [
                'title' => 'Leave', 'layout' => 'app', 'requests' => $requests,
                'isHR' => $isHR, 'leaveTypes' => $leaveTypes,
            ]));
        });

        /* ---- Submit request ---- */
        $router->post('/leave/request', function () use ($router, $db): void {
            $router->auth()->requireLogin($router->response());
            $tid = $router->tenant()->id();
            $userId = $router->auth()->userId();
            $emp = $db->fetchOne('SELECT id FROM employees WHERE tenant_id = ? AND user_id = ?', [$tid, $userId]);
            if (!$emp) { $router->response()->redirect($router->tenant()->pathPrefix() . '/leave'); return; }
            $startDate = trim((string)($_POST['start_date'] ?? ''));
            $endDate = trim((string)($_POST['end_date'] ?? ''));
            $days = max(0.5, (float)($_POST['days'] ?? 1));
            $typeId = (int)($_POST['leave_type_id'] ?? 0);
            $reason = trim((string)($_POST['reason'] ?? ''));
            $db->query(
                'INSERT INTO leave_requests (tenant_id, employee_id, leave_type_id, start_date, end_date, days, reason) VALUES (?,?,?,?,?,?,?)',
                [$tid, $emp['id'], $typeId, $startDate, $endDate, $days, $reason],
            );
            AuditLog::record('leave.request.created', 'leave_request', (int)$db->pdo()->lastInsertId());
            $router->response()->redirect($router->tenant()->pathPrefix() . '/leave');
        });

        /* ---- Review request (approve / reject) ---- */
        $router->post('/leave/review/{id}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $req = $db->fetchOne('SELECT * FROM leave_requests WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $tid]);
            if (!$req) { $router->response()->status(404)->html('<h1>Not found</h1>'); return; }
            $action = $_POST['action'] ?? '';
            $status = $action === 'approve' ? 'approved' : ($action === 'reject' ? 'rejected' : $req['status']);
            $db->query('UPDATE leave_requests SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_note = ? WHERE id = ?', [
                $status, $router->auth()->userId(), trim((string)($_POST['review_note'] ?? '')), (int)$p['id'],
            ]);
            if ($status === 'approved') {
                $year = date('Y', strtotime($req['start_date']));
                $existing = $db->fetchOne('SELECT id FROM leave_balances WHERE tenant_id = ? AND employee_id = ? AND leave_type_id = ? AND year = ?', [
                    $tid, $req['employee_id'], $req['leave_type_id'], $year,
                ]);
                if ($existing) {
                    $db->query('UPDATE leave_balances SET used_days = used_days + ? WHERE id = ?', [$req['days'], $existing['id']]);
                } else {
                    $lt = $db->fetchOne('SELECT default_days_per_year FROM leave_types WHERE id = ?', [$req['leave_type_id']]);
                    $db->query('INSERT INTO leave_balances (tenant_id, employee_id, leave_type_id, year, total_days, used_days) VALUES (?,?,?,?,?,?)', [
                        $tid, $req['employee_id'], $req['leave_type_id'], $year, $lt['default_days_per_year'] ?? 0, $req['days'],
                    ]);
                }
            }
            AuditLog::record('leave.request.' . $status, 'leave_request', (int)$p['id']);
            $router->response()->redirect($router->tenant()->pathPrefix() . '/leave');
        });

        /* ---- Balances ---- */
        $router->get('/leave/balances', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $year = (int)($_GET['year'] ?? date('Y'));
            $balances = $db->fetchAll(
                'SELECT b.*, e.first_name, e.last_name, lt.name AS type_name FROM leave_balances b JOIN employees e ON e.id = b.employee_id JOIN leave_types lt ON lt.id = b.leave_type_id WHERE b.tenant_id = ? AND b.year = ? ORDER BY e.last_name, lt.name',
                [$tid, $year],
            );
            $router->response()->html($router->view()->render('plugins/leave/balances', [
                'title' => 'Leave Balances', 'layout' => 'app', 'balances' => $balances, 'year' => $year,
            ]));
        });

        /* ---- Settings: leave types ---- */
        $router->get('/leave/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $types = $db->fetchAll('SELECT * FROM leave_types WHERE tenant_id = ? ORDER BY name', [$router->tenant()->id()]);
            $router->response()->html($router->view()->render('plugins/leave/settings', [
                'title' => 'Leave Settings', 'layout' => 'app', 'types' => $types,
            ]));
        });

        $router->post('/leave/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $tid = $router->tenant()->id();
            $db->query('INSERT INTO leave_types (tenant_id, name, color, default_days_per_year, is_paid, requires_approval) VALUES (?,?,?,?,?,?)', [
                $tid, trim((string)($_POST['name'] ?? '')), trim((string)($_POST['color'] ?? '#3498db')),
                (float)($_POST['default_days_per_year'] ?? 0), isset($_POST['is_paid']) ? 1 : 0,
                isset($_POST['requires_approval']) ? 1 : 0,
            ]);
            AuditLog::record('leave.type.created');
            $router->response()->redirect($router->tenant()->pathPrefix() . '/leave/settings');
        });
    }
}
