<?php
/**
 * Jira plugin.
 * Manages Jira instances, project browsing, role-based access grants.
 */

declare(strict_types=1);

namespace Src\Plugins\Jira;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

require_once __DIR__ . '/JiraAdapter.php';

final class Plugin implements PluginInterface
{
    public function name(): string { return 'Jira'; }
    public function requires(): array { return ['Core']; }
    public function sidebarItem(): ?array { return ['label' => 'Jira', 'icon' => 'tasks', 'route' => '/jira']; }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        $router->get('/jira', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $instances = $db->fetchAll('SELECT * FROM jira_instances WHERE tenant_id = ? ORDER BY label', [$router->tenant()->id()]);
            $router->response()->html($router->view()->render('plugins/jira/index', [
                'title' => 'Jira', 'layout' => 'app', 'instances' => $instances,
            ]));
        });

        $router->get('/jira/instance/{id}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $inst = $db->fetchOne('SELECT * FROM jira_instances WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $tid]);
            if (!$inst) { $router->response()->status(404)->html('<h1>Not found</h1>'); return; }
            $adapter = new JiraAdapter($inst['base_url'], $inst['admin_email'], $inst['api_token']);
            $projects = $adapter->listProjects();
            $grants = $db->fetchAll(
                'SELECT g.*, e.first_name, e.last_name FROM jira_access_grants g JOIN employees e ON e.id = g.employee_id WHERE g.tenant_id = ? AND g.instance_id = ? AND g.revoked_at IS NULL ORDER BY g.granted_at DESC',
                [$tid, (int)$p['id']],
            );
            $employees = $db->tenantFetchAll('employees', 'is_active = 1');
            $router->response()->html($router->view()->render('plugins/jira/instance', [
                'title' => $inst['label'], 'layout' => 'app', 'instance' => $inst,
                'projects' => $projects, 'grants' => $grants, 'employees' => $employees,
            ]));
        });

        $router->post('/jira/grant', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $instanceId = (int)($_POST['instance_id'] ?? 0);
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            $projectKey = trim((string)($_POST['project_key'] ?? ''));
            $projectName = trim((string)($_POST['project_name'] ?? ''));
            $jiraAccountId = trim((string)($_POST['jira_account_id'] ?? ''));
            $roleName = trim((string)($_POST['role_name'] ?? 'Member'));
            $db->query(
                'INSERT INTO jira_access_grants (tenant_id, instance_id, employee_id, jira_account_id, project_key, project_name, role_name) VALUES (?,?,?,?,?,?,?)',
                [$tid, $instanceId, $employeeId, $jiraAccountId, $projectKey, $projectName, $roleName],
            );
            AuditLog::record('jira.access.granted', 'jira_access_grant', $employeeId);
            $router->response()->redirect($router->tenant()->pathPrefix() . '/jira/instance/' . $instanceId);
        });

        $router->get('/jira/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $instances = $db->fetchAll('SELECT * FROM jira_instances WHERE tenant_id = ? ORDER BY label', [$router->tenant()->id()]);
            $router->response()->html($router->view()->render('plugins/jira/settings', [
                'title' => 'Jira Settings', 'layout' => 'app', 'instances' => $instances,
            ]));
        });

        $router->post('/jira/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $tid = $router->tenant()->id();
            $db->query(
                'INSERT INTO jira_instances (tenant_id, label, base_url, admin_email, api_token) VALUES (?,?,?,?,?)',
                [$tid, trim((string)($_POST['label'] ?? '')), trim((string)($_POST['base_url'] ?? '')), trim((string)($_POST['admin_email'] ?? '')), trim((string)($_POST['api_token'] ?? ''))],
            );
            AuditLog::record('jira.instance.created');
            $router->response()->redirect($router->tenant()->pathPrefix() . '/jira/settings');
        });
    }
}
