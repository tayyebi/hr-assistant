<?php
/**
 * Passbolt plugin.
 * Manages Passbolt instances, user provisioning, group membership.
 */

declare(strict_types=1);

namespace Src\Plugins\Passbolt;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

require_once __DIR__ . '/PassboltAdapter.php';

final class Plugin implements PluginInterface
{
    public function name(): string { return 'Passbolt'; }
    public function requires(): array { return ['Core']; }
    public function sidebarItem(): ?array { return ['label' => 'Passbolt', 'icon' => 'lock', 'route' => '/passbolt']; }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        $router->get('/passbolt', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $instances = $db->fetchAll('SELECT * FROM passbolt_instances WHERE tenant_id = ? ORDER BY label', [$router->tenant()->id()]);
            $router->response()->html($router->view()->render('plugins/passbolt/index', [
                'title' => 'Passbolt', 'layout' => 'app', 'instances' => $instances,
            ]));
        });

        $router->get('/passbolt/instance/{id}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $inst = $db->fetchOne('SELECT * FROM passbolt_instances WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $tid]);
            if (!$inst) { $router->response()->status(404)->html('<h1>Not found</h1>'); return; }
            $adapter = new PassboltAdapter($inst['base_url'], $inst['admin_api_key']);
            $pbUsers = $adapter->listUsers();
            $links = $db->fetchAll(
                'SELECT l.*, e.first_name, e.last_name FROM passbolt_user_links l JOIN employees e ON e.id = l.employee_id WHERE l.tenant_id = ? AND l.instance_id = ? AND l.is_active = 1 ORDER BY l.linked_at DESC',
                [$tid, (int)$p['id']],
            );
            $employees = $db->tenantFetchAll('employees', 'is_active = 1');
            $router->response()->html($router->view()->render('plugins/passbolt/instance', [
                'title' => $inst['label'], 'layout' => 'app', 'instance' => $inst,
                'pbUsers' => $pbUsers, 'links' => $links, 'employees' => $employees,
            ]));
        });

        $router->post('/passbolt/link', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $instanceId = (int)($_POST['instance_id'] ?? 0);
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            $pbUserId = trim((string)($_POST['passbolt_user_id'] ?? ''));
            $username = trim((string)($_POST['username'] ?? ''));
            $db->query(
                'INSERT INTO passbolt_user_links (tenant_id, instance_id, employee_id, passbolt_user_id, username) VALUES (?,?,?,?,?)',
                [$tid, $instanceId, $employeeId, $pbUserId, $username],
            );
            AuditLog::record('passbolt.user.linked', 'passbolt_user_link', $employeeId);
            $router->response()->redirect($router->tenant()->pathPrefix() . '/passbolt/instance/' . $instanceId);
        });

        $router->post('/passbolt/unlink/{id}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $link = $db->fetchOne('SELECT * FROM passbolt_user_links WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $tid]);
            if ($link) {
                $db->query('UPDATE passbolt_user_links SET is_active = 0 WHERE id = ?', [(int)$p['id']]);
                AuditLog::record('passbolt.user.unlinked', 'passbolt_user_link', (int)$p['id']);
                $router->response()->redirect($router->tenant()->pathPrefix() . '/passbolt/instance/' . $link['instance_id']);
            } else {
                $router->response()->redirect($router->tenant()->pathPrefix() . '/passbolt');
            }
        });

        $router->get('/passbolt/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $instances = $db->fetchAll('SELECT * FROM passbolt_instances WHERE tenant_id = ? ORDER BY label', [$router->tenant()->id()]);
            $router->response()->html($router->view()->render('plugins/passbolt/settings', [
                'title' => 'Passbolt Settings', 'layout' => 'app', 'instances' => $instances,
            ]));
        });

        $router->post('/passbolt/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $tid = $router->tenant()->id();
            $db->query(
                'INSERT INTO passbolt_instances (tenant_id, label, base_url, admin_api_key, server_key_fingerprint) VALUES (?,?,?,?,?)',
                [$tid, trim((string)($_POST['label'] ?? '')), trim((string)($_POST['base_url'] ?? '')), trim((string)($_POST['admin_api_key'] ?? '')), trim((string)($_POST['server_key_fingerprint'] ?? ''))],
            );
            AuditLog::record('passbolt.instance.created');
            $router->response()->redirect($router->tenant()->pathPrefix() . '/passbolt/settings');
        });
    }
}
