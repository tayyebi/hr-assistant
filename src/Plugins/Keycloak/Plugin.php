<?php
/**
 * Keycloak plugin.
 * Manages Keycloak realm users, groups, roles linked to employees.
 */

declare(strict_types=1);

namespace Src\Plugins\Keycloak;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

require_once __DIR__ . '/KeycloakAdapter.php';

final class Plugin implements PluginInterface
{
    public function name(): string { return 'Keycloak'; }
    public function requires(): array { return ['Core']; }
    public function sidebarItem(): ?array { return ['label' => 'Keycloak', 'icon' => 'key', 'route' => '/keycloak']; }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        $router->get('/keycloak', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $instances = $db->fetchAll('SELECT * FROM keycloak_instances WHERE tenant_id = ? ORDER BY label', [$router->tenant()->id()]);
            $router->response()->html($router->view()->render('plugins/keycloak/index', [
                'title' => 'Keycloak', 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'instances' => $instances,
            ]));
        });

        $router->get('/keycloak/instance/{id}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $inst = $db->fetchOne('SELECT * FROM keycloak_instances WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $tid]);
            if (!$inst) { $router->response()->status(404)->html('<h1>Not found</h1>'); return; }
            $adapter = new KeycloakAdapter($inst['base_url'], $inst['realm'], $inst['client_id'], $inst['client_secret']);
            $kcUsers = $adapter->listUsers();
            $links = $db->fetchAll(
                'SELECT l.*, e.first_name, e.last_name FROM keycloak_user_links l JOIN employees e ON e.id = l.employee_id WHERE l.tenant_id = ? AND l.instance_id = ? AND l.is_active = 1 ORDER BY l.linked_at DESC',
                [$tid, (int)$p['id']],
            );
            $employees = $db->tenantFetchAll('employees', 'is_active = 1');
            $router->response()->html($router->view()->render('plugins/keycloak/instance', [
                'title' => $inst['label'], 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'instance' => $inst,
                'kcUsers' => $kcUsers, 'links' => $links, 'employees' => $employees,
            ]));
        });

        $router->post('/keycloak/link', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $instanceId = (int)($_POST['instance_id'] ?? 0);
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            $kcUserId = trim((string)($_POST['keycloak_user_id'] ?? ''));
            $username = trim((string)($_POST['username'] ?? ''));
            $db->query(
                'INSERT INTO keycloak_user_links (tenant_id, instance_id, employee_id, keycloak_user_id, username) VALUES (?,?,?,?,?)',
                [$tid, $instanceId, $employeeId, $kcUserId, $username],
            );
            AuditLog::record('keycloak.user.linked', 'keycloak_user_link', $employeeId);
            $router->response()->redirect($router->tenant()->pathPrefix() . '/keycloak/instance/' . $instanceId);
        });

        $router->post('/keycloak/unlink/{id}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $link = $db->fetchOne('SELECT * FROM keycloak_user_links WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $tid]);
            if ($link) {
                $db->query('UPDATE keycloak_user_links SET is_active = 0 WHERE id = ?', [(int)$p['id']]);
                AuditLog::record('keycloak.user.unlinked', 'keycloak_user_link', (int)$p['id']);
                $router->response()->redirect($router->tenant()->pathPrefix() . '/keycloak/instance/' . $link['instance_id']);
            } else {
                $router->response()->redirect($router->tenant()->pathPrefix() . '/keycloak');
            }
        });

        $router->get('/keycloak/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $instances = $db->fetchAll('SELECT * FROM keycloak_instances WHERE tenant_id = ? ORDER BY label', [$router->tenant()->id()]);
            $router->response()->html($router->view()->render('plugins/keycloak/settings', [
                'title' => 'Keycloak Settings', 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'instances' => $instances,
            ]));
        });

        $router->post('/keycloak/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $tid = $router->tenant()->id();
            $db->query(
                'INSERT INTO keycloak_instances (tenant_id, label, base_url, realm, client_id, client_secret) VALUES (?,?,?,?,?,?)',
                [$tid, trim((string)($_POST['label'] ?? '')), trim((string)($_POST['base_url'] ?? '')), trim((string)($_POST['realm'] ?? 'master')), trim((string)($_POST['client_id'] ?? '')), trim((string)($_POST['client_secret'] ?? ''))],
            );
            AuditLog::record('keycloak.instance.created');
            $router->response()->redirect($router->tenant()->pathPrefix() . '/keycloak/settings');
        });
    }
}
