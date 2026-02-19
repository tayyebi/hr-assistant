<?php
/**
 * Nextcloud plugin.
 * Document management via Nextcloud OCS provisioning + WebDAV.
 * Links employees to Nextcloud user accounts, lists files, manages sharing.
 */

declare(strict_types=1);

namespace Src\Plugins\Nextcloud;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

require_once __DIR__ . '/NextcloudAdapter.php';

final class Plugin implements PluginInterface
{
    public function name(): string { return 'Nextcloud'; }
    public function requires(): array { return ['Core']; }
    public function sidebarItem(): ?array { return ['label' => 'Documents', 'icon' => 'folder', 'route' => '/nextcloud']; }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        $router->get('/nextcloud', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $instances = $db->fetchAll('SELECT * FROM nextcloud_instances WHERE tenant_id = ? ORDER BY label', [$router->tenant()->id()]);
            $router->response()->html($router->view()->render('plugins/nextcloud/index', [
                'title' => 'Documents', 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'instances' => $instances,
            ]));
        });

        $router->get('/nextcloud/instance/{id}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $inst = $db->fetchOne('SELECT * FROM nextcloud_instances WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $tid]);
            if (!$inst) { $router->response()->status(404)->html('<h1>Not found</h1>'); return; }
            $adapter = new NextcloudAdapter($inst['base_url'], $inst['admin_user'], $inst['admin_password']);
            $ncUsers = $adapter->listUsers();
            $links = $db->fetchAll(
                'SELECT l.*, e.first_name, e.last_name FROM nextcloud_user_links l JOIN employees e ON e.id = l.employee_id WHERE l.tenant_id = ? AND l.instance_id = ? AND l.is_active = 1 ORDER BY l.linked_at DESC',
                [$tid, (int)$p['id']],
            );
            $employees = $db->tenantFetchAll('employees', 'is_active = 1');
            $router->response()->html($router->view()->render('plugins/nextcloud/instance', [
                'title' => $inst['label'], 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'instance' => $inst,
                'ncUsers' => $ncUsers, 'links' => $links, 'employees' => $employees,
            ]));
        });

        $router->get('/nextcloud/files/{linkId}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $link = $db->fetchOne(
                'SELECT l.*, i.base_url, i.admin_user, i.admin_password, e.first_name, e.last_name FROM nextcloud_user_links l JOIN nextcloud_instances i ON i.id = l.instance_id JOIN employees e ON e.id = l.employee_id WHERE l.id = ? AND l.tenant_id = ?',
                [(int)$p['linkId'], $tid],
            );
            if (!$link) { $router->response()->status(404)->html('<h1>Not found</h1>'); return; }
            $adapter = new NextcloudAdapter($link['base_url'], $link['admin_user'], $link['admin_password']);
            $path = trim((string)($_GET['path'] ?? '/'));
            $files = $adapter->listFolder($link['nc_user_id'], $path);
            $router->response()->html($router->view()->render('plugins/nextcloud/files', [
                'title' => 'Files – ' . $link['first_name'] . ' ' . $link['last_name'],
                'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'link' => $link, 'files' => $files, 'currentPath' => $path,
            ]));
        });

        $router->post('/nextcloud/link', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $instanceId = (int)($_POST['instance_id'] ?? 0);
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            $ncUserId = trim((string)($_POST['nc_user_id'] ?? ''));
            $displayName = trim((string)($_POST['nc_display_name'] ?? ''));
            $db->query(
                'INSERT INTO nextcloud_user_links (tenant_id, instance_id, employee_id, nc_user_id, nc_display_name) VALUES (?,?,?,?,?)',
                [$tid, $instanceId, $employeeId, $ncUserId, $displayName],
            );
            AuditLog::record('nextcloud.user.linked', 'nextcloud_user_link', $employeeId);
            $router->response()->redirect($router->tenant()->pathPrefix() . '/nextcloud/instance/' . $instanceId);
        });

        $router->post('/nextcloud/unlink/{id}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $link = $db->fetchOne('SELECT * FROM nextcloud_user_links WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $tid]);
            if ($link) {
                $db->query('UPDATE nextcloud_user_links SET is_active = 0 WHERE id = ?', [(int)$p['id']]);
                AuditLog::record('nextcloud.user.unlinked', 'nextcloud_user_link', (int)$p['id']);
                $router->response()->redirect($router->tenant()->pathPrefix() . '/nextcloud/instance/' . $link['instance_id']);
            } else {
                $router->response()->redirect($router->tenant()->pathPrefix() . '/nextcloud');
            }
        });

        $router->get('/nextcloud/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $instances = $db->fetchAll('SELECT * FROM nextcloud_instances WHERE tenant_id = ? ORDER BY label', [$router->tenant()->id()]);
            $router->response()->html($router->view()->render('plugins/nextcloud/settings', [
                'title' => 'Documents Settings', 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'instances' => $instances,
            ]));
        });

        $router->post('/nextcloud/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $tid = $router->tenant()->id();
            $db->query(
                'INSERT INTO nextcloud_instances (tenant_id, label, base_url, admin_user, admin_password) VALUES (?,?,?,?,?)',
                [$tid, trim((string)($_POST['label'] ?? '')), trim((string)($_POST['base_url'] ?? '')), trim((string)($_POST['admin_user'] ?? '')), trim((string)($_POST['admin_password'] ?? ''))],
            );
            AuditLog::record('nextcloud.instance.created');
            $router->response()->redirect($router->tenant()->pathPrefix() . '/nextcloud/settings');
        });
    }
}
