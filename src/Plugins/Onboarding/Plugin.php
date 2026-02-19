<?php
/**
 * Onboarding plugin.
 * Templates with task checklists, process tracking per new hire.
 */

declare(strict_types=1);

namespace Src\Plugins\Onboarding;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

final class Plugin implements PluginInterface
{
    public function name(): string { return 'Onboarding'; }
    public function requires(): array { return ['Core']; }
    public function sidebarItem(): ?array { return ['label' => 'Onboarding', 'icon' => 'user-plus', 'route' => '/onboarding']; }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        /* ---- Active processes list ---- */
        $router->get('/onboarding', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $processes = $db->fetchAll(
                'SELECT p.*, e.first_name, e.last_name, t.name AS template_name FROM onboarding_processes p JOIN employees e ON e.id = p.employee_id JOIN onboarding_templates t ON t.id = p.template_id WHERE p.tenant_id = ? ORDER BY p.started_at DESC',
                [$tid],
            );
            $router->response()->html($router->view()->render('plugins/onboarding/index', [
                'title' => 'Onboarding', 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'processes' => $processes,
            ]));
        });

        /* ---- View single process ---- */
        $router->get('/onboarding/process/{id}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $proc = $db->fetchOne(
                'SELECT p.*, e.first_name, e.last_name, t.name AS template_name FROM onboarding_processes p JOIN employees e ON e.id = p.employee_id JOIN onboarding_templates t ON t.id = p.template_id WHERE p.id = ? AND p.tenant_id = ?',
                [(int)$p['id'], $tid],
            );
            if (!$proc) { $router->response()->status(404)->html('<h1>Not found</h1>'); return; }
            $tasks = $db->fetchAll('SELECT * FROM onboarding_tasks WHERE process_id = ? ORDER BY id', [(int)$p['id']]);
            $router->response()->html($router->view()->render('plugins/onboarding/process', [
                'title' => 'Onboarding – ' . $proc['first_name'], 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(),
                'process' => $proc, 'tasks' => $tasks,
            ]));
        });

        /* ---- Mark task status ---- */
        $router->post('/onboarding/task/{id}/status', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $task = $db->fetchOne('SELECT * FROM onboarding_tasks WHERE id = ?', [(int)$p['id']]);
            if (!$task) { $router->response()->status(404)->html('<h1>Not found</h1>'); return; }
            $status = $_POST['status'] ?? 'pending';
            $completedAt = $status === 'completed' ? date('Y-m-d H:i:s') : null;
            $db->query('UPDATE onboarding_tasks SET status = ?, completed_at = ?, notes = ? WHERE id = ?', [
                $status, $completedAt, trim((string)($_POST['notes'] ?? '')), (int)$p['id'],
            ]);
            /* auto-complete process if all tasks done */
            $remaining = $db->fetchOne('SELECT COUNT(*) AS cnt FROM onboarding_tasks WHERE process_id = ? AND status NOT IN ("completed","skipped")', [$task['process_id']]);
            if ((int)($remaining['cnt'] ?? 1) === 0) {
                $db->query('UPDATE onboarding_processes SET status = "completed", completed_at = NOW() WHERE id = ?', [$task['process_id']]);
            }
            AuditLog::record('onboarding.task.updated', 'onboarding_task', (int)$p['id']);
            $router->response()->redirect($router->tenant()->pathPrefix() . '/onboarding/process/' . $task['process_id']);
        });

        /* ---- Start new process ---- */
        $router->post('/onboarding/start', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            $templateId = (int)($_POST['template_id'] ?? 0);
            $db->query('INSERT INTO onboarding_processes (tenant_id, employee_id, template_id) VALUES (?,?,?)', [$tid, $employeeId, $templateId]);
            $processId = (int)$db->pdo()->lastInsertId();
            /* copy template tasks */
            $tplTasks = $db->fetchAll('SELECT * FROM onboarding_template_tasks WHERE template_id = ? ORDER BY sort_order', [$templateId]);
            foreach ($tplTasks as $tt) {
                $due = $tt['due_days'] ? date('Y-m-d', strtotime('+' . (int)$tt['due_days'] . ' days')) : null;
                $db->query('INSERT INTO onboarding_tasks (process_id, template_task_id, title, description, due_date) VALUES (?,?,?,?,?)', [
                    $processId, $tt['id'], $tt['title'], $tt['description'], $due,
                ]);
            }
            AuditLog::record('onboarding.process.started', 'onboarding_process', $processId);
            $router->response()->redirect($router->tenant()->pathPrefix() . '/onboarding/process/' . $processId);
        });

        /* ---- Templates management ---- */
        $router->get('/onboarding/templates', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $templates = $db->fetchAll('SELECT * FROM onboarding_templates WHERE tenant_id = ? ORDER BY name', [$router->tenant()->id()]);
            $employees = $db->tenantFetchAll('employees', 'is_active = 1');
            $router->response()->html($router->view()->render('plugins/onboarding/templates', [
                'title' => 'Onboarding Templates', 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'templates' => $templates, 'employees' => $employees,
            ]));
        });

        $router->post('/onboarding/templates', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $tid = $router->tenant()->id();
            $db->query('INSERT INTO onboarding_templates (tenant_id, name, description) VALUES (?,?,?)', [
                $tid, trim((string)($_POST['name'] ?? '')), trim((string)($_POST['description'] ?? '')),
            ]);
            $templateId = (int)$db->pdo()->lastInsertId();
            /* add tasks from multi-field */
            $titles = $_POST['task_title'] ?? [];
            foreach ($titles as $i => $title) {
                if (!trim($title)) { continue; }
                $db->query('INSERT INTO onboarding_template_tasks (template_id, sort_order, title, due_days) VALUES (?,?,?,?)', [
                    $templateId, $i, trim($title), (int)($_POST['task_due_days'][$i] ?? 0),
                ]);
            }
            AuditLog::record('onboarding.template.created', 'onboarding_template', $templateId);
            $router->response()->redirect($router->tenant()->pathPrefix() . '/onboarding/templates');
        });

        $router->get('/onboarding/settings', function () use ($router): void {
            $router->response()->redirect($router->tenant()->pathPrefix() . '/onboarding/templates');
        });
    }
}
