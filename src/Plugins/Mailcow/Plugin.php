<?php
/**
 * Mailcow plugin.
 * Manages Mailcow instances, mailbox provisioning, employee assignment.
 */

declare(strict_types=1);

namespace Src\Plugins\Mailcow;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

require_once __DIR__ . '/MailcowAdapter.php';

final class Plugin implements PluginInterface
{
    public function name(): string { return 'Mailcow'; }
    public function requires(): array { return ['Core']; }
    public function sidebarItem(): ?array { return ['label' => 'Mailcow', 'icon' => 'mail-plus', 'route' => '/mailcow']; }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        $router->get('/mailcow', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $instances = $db->fetchAll('SELECT * FROM mailcow_instances WHERE tenant_id = ? ORDER BY label', [$tid]);
            $router->response()->html($router->view()->render('plugins/mailcow/index', [
                'title' => 'Mailcow', 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'instances' => $instances,
            ]));
        });

        $router->get('/mailcow/instance/{id}', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $inst = $db->fetchOne('SELECT * FROM mailcow_instances WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $tid]);
            if (!$inst) { $router->response()->status(404)->html('<h1>Not found</h1>'); return; }
            $adapter = new MailcowAdapter($inst['base_url'], $inst['api_key']);
            $mailboxes = $adapter->listMailboxes();
            $domains = $adapter->listDomains();
            $localBoxes = $db->fetchAll(
                'SELECT m.*, e.first_name, e.last_name FROM mailcow_mailboxes m LEFT JOIN employees e ON e.id = m.employee_id WHERE m.tenant_id = ? AND m.instance_id = ? ORDER BY m.created_at DESC',
                [$tid, (int)$p['id']],
            );
            $employees = $db->tenantFetchAll('employees', 'is_active = 1');
            $router->response()->html($router->view()->render('plugins/mailcow/instance', [
                'title' => $inst['label'], 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'instance' => $inst,
                'mailboxes' => $mailboxes, 'domains' => $domains,
                'localBoxes' => $localBoxes, 'employees' => $employees,
            ]));
        });

        $router->post('/mailcow/create-mailbox', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $instanceId = (int)($_POST['instance_id'] ?? 0);
            $localPart = trim((string)($_POST['local_part'] ?? ''));
            $domain = trim((string)($_POST['domain'] ?? ''));
            $name = trim((string)($_POST['name'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $employeeId = (int)($_POST['employee_id'] ?? 0) ?: null;

            $inst = $db->fetchOne('SELECT * FROM mailcow_instances WHERE id = ? AND tenant_id = ?', [$instanceId, $tid]);
            if ($inst && $localPart && $domain) {
                $adapter = new MailcowAdapter($inst['base_url'], $inst['api_key']);
                $adapter->createMailbox($localPart, $domain, $name, $password);
                $db->query(
                    'INSERT INTO mailcow_mailboxes (tenant_id, instance_id, employee_id, local_part, domain, mailcow_username) VALUES (?,?,?,?,?,?)',
                    [$tid, $instanceId, $employeeId, $localPart, $domain, $localPart . '@' . $domain],
                );
                AuditLog::record('mailcow.mailbox.created', 'mailcow_mailbox', null, null, $localPart . '@' . $domain);
            }
            $router->response()->redirect($router->tenant()->pathPrefix() . '/mailcow/instance/' . $instanceId);
        });

        $router->get('/mailcow/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $tid = $router->tenant()->id();
            $instances = $db->fetchAll('SELECT * FROM mailcow_instances WHERE tenant_id = ? ORDER BY label', [$tid]);
            $router->response()->html($router->view()->render('plugins/mailcow/settings', [
                'title' => 'Mailcow Settings', 'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(), 'instances' => $instances,
            ]));
        });

        $router->post('/mailcow/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $tid = $router->tenant()->id();
            $db->query(
                'INSERT INTO mailcow_instances (tenant_id, label, base_url, api_key) VALUES (?,?,?,?)',
                [$tid, trim((string)($_POST['label'] ?? '')), trim((string)($_POST['base_url'] ?? '')), trim((string)($_POST['api_key'] ?? ''))],
            );
            AuditLog::record('mailcow.instance.created');
            $router->response()->redirect($router->tenant()->pathPrefix() . '/mailcow/settings');
        });
    }
}
