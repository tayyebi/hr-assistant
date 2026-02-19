<?php
/**
 * GitLab plugin.
 * Manages GitLab instances, browsing projects/groups, granting/revoking access.
 */

declare(strict_types=1);

namespace Src\Plugins\GitLab;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

require_once __DIR__ . '/GitLabAdapter.php';

final class Plugin implements PluginInterface
{
    public function name(): string
    {
        return 'GitLab';
    }

    public function requires(): array
    {
        return ['Core'];
    }

    public function sidebarItem(): ?array
    {
        return ['label' => 'GitLab', 'icon' => 'git', 'route' => '/gitlab'];
    }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        $router->get('/gitlab', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $instances = $db->fetchAll('SELECT * FROM gitlab_instances WHERE tenant_id = ? ORDER BY label', [$tid]);
            $router->response()->html($router->view()->render('plugins/gitlab/index', [
                'title' => 'GitLab', 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'instances' => $instances,
            ]));
        });

        $router->get('/gitlab/instance/{id}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $inst = $db->fetchOne('SELECT * FROM gitlab_instances WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $tid]);
            if (!$inst) { $router->response()->status(404)->html('<h1>Not found</h1>'); return; }
            $adapter = new GitLabAdapter($inst['base_url'], $inst['api_token']);
            $projects = $adapter->listProjects();
            $groups = $adapter->listGroups();
            $grants = $db->fetchAll(
                'SELECT g.*, e.first_name, e.last_name FROM gitlab_access_grants g '
                . 'JOIN employees e ON e.id = g.employee_id '
                . 'WHERE g.tenant_id = ? AND g.instance_id = ? AND g.revoked_at IS NULL ORDER BY g.granted_at DESC',
                [$tid, (int)$p['id']],
            );
            $employees = $db->tenantFetchAll('employees', 'is_active = 1');
            $router->response()->html($router->view()->render('plugins/gitlab/instance', [
                'title' => $inst['label'], 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(),
                'instance' => $inst, 'projects' => $projects, 'groups' => $groups,
                'grants' => $grants, 'employees' => $employees,
            ]));
        });

        $router->post('/gitlab/grant', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $instanceId = (int)($_POST['instance_id'] ?? 0);
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            $resourceType = ($_POST['resource_type'] ?? 'project');
            $resourceId = (int)($_POST['resource_id'] ?? 0);
            $resourceName = trim((string)($_POST['resource_name'] ?? ''));
            $accessLevel = (int)($_POST['access_level'] ?? 30);
            $gitlabUserId = (int)($_POST['gitlab_user_id'] ?? 0);

            $inst = $db->fetchOne('SELECT * FROM gitlab_instances WHERE id = ? AND tenant_id = ?', [$instanceId, $tid]);
            if ($inst && $gitlabUserId > 0) {
                $adapter = new GitLabAdapter($inst['base_url'], $inst['api_token']);
                if ($resourceType === 'group') {
                    $adapter->addGroupMember($resourceId, $gitlabUserId, $accessLevel);
                } else {
                    $adapter->addProjectMember($resourceId, $gitlabUserId, $accessLevel);
                }
                $db->query(
                    'INSERT INTO gitlab_access_grants (tenant_id, instance_id, employee_id, gitlab_user_id, resource_type, resource_id, resource_name, access_level) VALUES (?,?,?,?,?,?,?,?)',
                    [$tid, $instanceId, $employeeId, $gitlabUserId, $resourceType, $resourceId, $resourceName, $accessLevel],
                );
                AuditLog::record('gitlab.access.granted', 'gitlab_access_grant', $employeeId);
            }
            $router->response()->redirect($router->tenant()->pathPrefix() . '/gitlab/instance/' . $instanceId);
        });

        $router->post('/gitlab/revoke/{grantId}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $grant = $db->fetchOne('SELECT g.*, gi.base_url, gi.api_token FROM gitlab_access_grants g JOIN gitlab_instances gi ON gi.id = g.instance_id WHERE g.id = ? AND g.tenant_id = ?', [(int)$p['grantId'], $tid]);
            if ($grant) {
                $adapter = new GitLabAdapter($grant['base_url'], $grant['api_token']);
                if ($grant['resource_type'] === 'group') {
                    $adapter->removeGroupMember((int)$grant['resource_id'], (int)$grant['gitlab_user_id']);
                } else {
                    $adapter->removeProjectMember((int)$grant['resource_id'], (int)$grant['gitlab_user_id']);
                }
                $db->query('UPDATE gitlab_access_grants SET revoked_at = NOW() WHERE id = ?', [(int)$p['grantId']]);
                AuditLog::record('gitlab.access.revoked', 'gitlab_access_grant', (int)$p['grantId']);
            }
            $router->response()->redirect($router->tenant()->pathPrefix() . '/gitlab/instance/' . ($grant['instance_id'] ?? ''));
        });

        $router->get('/gitlab/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) {
                return;
            }
            $tid = $router->tenant()->id();
            $instances = $db->fetchAll('SELECT * FROM gitlab_instances WHERE tenant_id = ? ORDER BY label', [$tid]);
            $router->response()->html($router->view()->render('plugins/gitlab/settings', [
                'title' => 'GitLab Settings', 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'instances' => $instances,
            ]));
        });

        $router->post('/gitlab/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) {
                return;
            }
            $tid = $router->tenant()->id();
            $db->query(
                'INSERT INTO gitlab_instances (tenant_id, label, base_url, api_token) VALUES (?, ?, ?, ?)',
                [$tid, trim((string)($_POST['label'] ?? '')), trim((string)($_POST['base_url'] ?? '')), trim((string)($_POST['api_token'] ?? ''))],
            );
            AuditLog::record('gitlab.instance.created');
            $router->response()->redirect($router->tenant()->pathPrefix() . '/gitlab/settings');
        });
    }
}
