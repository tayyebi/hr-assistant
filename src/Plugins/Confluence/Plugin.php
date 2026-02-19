<?php
/**
 * Confluence plugin.
 * Manages Confluence instances, space browsing, permission-based access grants.
 */

declare(strict_types=1);

namespace Src\Plugins\Confluence;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

require_once __DIR__ . '/ConfluenceAdapter.php';

final class Plugin implements PluginInterface
{
    public function name(): string { return 'Confluence'; }
    public function requires(): array { return ['Core']; }
    public function sidebarItem(): ?array { return ['label' => 'Confluence', 'icon' => 'book', 'route' => '/confluence']; }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        $router->get('/confluence', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $instances = $db->fetchAll('SELECT * FROM confluence_instances WHERE tenant_id = ? ORDER BY label', [$router->tenant()->id()]);
            $router->response()->html($router->view()->render('plugins/confluence/index', [
                'title' => 'Confluence', 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'instances' => $instances,
            ]));
        });

        $router->get('/confluence/instance/{id}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $inst = $db->fetchOne('SELECT * FROM confluence_instances WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $tid]);
            if (!$inst) { $router->response()->status(404)->html('<h1>Not found</h1>'); return; }
            $adapter = new ConfluenceAdapter($inst['base_url'], $inst['admin_email'], $inst['api_token']);
            $spacesResp = $adapter->listSpaces();
            $spaces = $spacesResp['results'] ?? $spacesResp;
            $grants = $db->fetchAll(
                'SELECT g.*, e.first_name, e.last_name FROM confluence_space_grants g JOIN employees e ON e.id = g.employee_id WHERE g.tenant_id = ? AND g.instance_id = ? AND g.revoked_at IS NULL ORDER BY g.granted_at DESC',
                [$tid, (int)$p['id']],
            );
            $employees = $db->tenantFetchAll('employees', 'is_active = 1');
            $router->response()->html($router->view()->render('plugins/confluence/instance', [
                'title' => $inst['label'], 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'instance' => $inst,
                'spaces' => $spaces, 'grants' => $grants, 'employees' => $employees,
            ]));
        });

        $router->post('/confluence/grant', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $instanceId = (int)($_POST['instance_id'] ?? 0);
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            $spaceKey = trim((string)($_POST['space_key'] ?? ''));
            $spaceName = trim((string)($_POST['space_name'] ?? ''));
            $accountId = trim((string)($_POST['confluence_account_id'] ?? ''));
            $permissionType = trim((string)($_POST['permission_type'] ?? 'read'));
            $db->query(
                'INSERT INTO confluence_space_grants (tenant_id, instance_id, employee_id, confluence_account_id, space_key, space_name, permission_type) VALUES (?,?,?,?,?,?,?)',
                [$tid, $instanceId, $employeeId, $accountId, $spaceKey, $spaceName, $permissionType],
            );
            AuditLog::record('confluence.access.granted', 'confluence_space_grant', $employeeId);
            $router->response()->redirect($router->tenant()->pathPrefix() . '/confluence/instance/' . $instanceId);
        });

        $router->get('/confluence/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $instances = $db->fetchAll('SELECT * FROM confluence_instances WHERE tenant_id = ? ORDER BY label', [$router->tenant()->id()]);
            $router->response()->html($router->view()->render('plugins/confluence/settings', [
                'title' => 'Confluence Settings', 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'instances' => $instances,
            ]));
        });

        $router->post('/confluence/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $tid = $router->tenant()->id();
            $db->query(
                'INSERT INTO confluence_instances (tenant_id, label, base_url, admin_email, api_token) VALUES (?,?,?,?,?)',
                [$tid, trim((string)($_POST['label'] ?? '')), trim((string)($_POST['base_url'] ?? '')), trim((string)($_POST['admin_email'] ?? '')), trim((string)($_POST['api_token'] ?? ''))],
            );
            AuditLog::record('confluence.instance.created');
            $router->response()->redirect($router->tenant()->pathPrefix() . '/confluence/settings');
        });
    }
}
