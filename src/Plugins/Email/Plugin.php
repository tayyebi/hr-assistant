<?php
/**
 * Email plugin entry point.
 * Manages IMAP/SMTP accounts, inbox views, sending, and employee assignment.
 */

declare(strict_types=1);

namespace Src\Plugins\Email;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\Messaging\ChannelManager;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

require_once __DIR__ . '/EmailChannel.php';

final class Plugin implements PluginInterface
{
    public function name(): string
    {
        return 'Email';
    }

    public function requires(): array
    {
        return ['Core'];
    }

    public function sidebarItem(): ?array
    {
        return ['label' => 'Email', 'icon' => 'mail', 'route' => '/email'];
    }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        $tenantId = $tenant->id();

        if ($tenantId !== null) {
            $accounts = $db->fetchAll(
                'SELECT * FROM email_accounts WHERE tenant_id = ? AND is_active = 1',
                [$tenantId],
            );
            foreach ($accounts as $acc) {
                $channel = new EmailChannel($db, $tenantId, (int)$acc['id']);
                ChannelManager::getInstance()->register($channel);
            }
        }

        $router->get('/email', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $accounts = $db->fetchAll(
                'SELECT * FROM email_accounts WHERE tenant_id = ? ORDER BY label',
                [$tid],
            );
            $router->response()->html($router->view()->render('plugins/email/index', [
                'title'    => 'Email',
                'layout'   => 'app', 'sidebarItems' => $router->getSidebarItems(),
                'accounts' => $accounts,
            ]));
        });

        $router->get('/email/account/{accountId}', function (array $params) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $accountId = (int)$params['accountId'];
            $emails = $db->fetchAll(
                'SELECT em.*, e.first_name AS emp_first, e.last_name AS emp_last '
                . 'FROM emails em '
                . 'LEFT JOIN employees e ON e.id = em.employee_id '
                . 'WHERE em.tenant_id = ? AND em.account_id = ? '
                . 'ORDER BY em.created_at DESC LIMIT 100',
                [$tid, $accountId],
            );
            $account = $db->fetchOne(
                'SELECT * FROM email_accounts WHERE id = ? AND tenant_id = ?',
                [$accountId, $tid],
            );
            $router->response()->html($router->view()->render('plugins/email/inbox', [
                'title'   => 'Inbox — ' . ($account['label'] ?? ''),
                'layout'  => 'app', 'sidebarItems' => $router->getSidebarItems(),
                'emails'  => $emails,
                'account' => $account,
            ]));
        });

        $router->get('/email/view/{emailId}', function (array $params) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $emailId = (int)$params['emailId'];
            $email = $db->fetchOne(
                'SELECT * FROM emails WHERE id = ? AND tenant_id = ?',
                [$emailId, $tid],
            );
            if (!$email) {
                $router->response()->status(404)->html('<h1>Not found</h1>');
                return;
            }
            $db->query('UPDATE emails SET is_read = 1 WHERE id = ?', [$emailId]);
            $employees = $db->tenantFetchAll('employees', 'is_active = 1');
            $router->response()->html($router->view()->render('plugins/email/view', [
                'title'     => $email['subject'] ?: '(No Subject)',
                'layout'    => 'app', 'sidebarItems' => $router->getSidebarItems(),
                'email'     => $email,
                'employees' => $employees,
            ]));
        });

        $router->post('/email/compose', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $accountId = (int)($_POST['account_id'] ?? 0);
            $to = trim((string)($_POST['to'] ?? ''));
            $subject = trim((string)($_POST['subject'] ?? ''));
            $body = trim((string)($_POST['body'] ?? ''));

            if ($tid !== null && $accountId > 0 && $to !== '') {
                $channel = new EmailChannel($db, $tid, $accountId);
                $channel->send($to, $body, ['subject' => $subject]);
            }
            $router->response()->redirect($router->tenant()->pathPrefix() . '/email/account/' . $accountId);
        });

        $router->post('/email/assign/{emailId}', function (array $params) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $emailId = (int)$params['emailId'];
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            $tid = $router->tenant()->id();
            if ($employeeId > 0) {
                $db->query(
                    'UPDATE emails SET employee_id = ? WHERE id = ? AND tenant_id = ?',
                    [$employeeId, $emailId, $tid],
                );
                AuditLog::record('email.assigned', 'email', $emailId);
            }
            $router->response()->redirect($router->tenant()->pathPrefix() . '/email/view/' . $emailId);
        });

        $router->get('/email/fetch/{accountId}', function (array $params) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $accountId = (int)$params['accountId'];
            if ($tid !== null) {
                $channel = new EmailChannel($db, $tid, $accountId);
                $channel->receive();
            }
            $router->response()->redirect($router->tenant()->pathPrefix() . '/email/account/' . $accountId);
        });

        $router->get('/email/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) {
                return;
            }
            $tid = $router->tenant()->id();
            $accounts = $db->fetchAll(
                'SELECT * FROM email_accounts WHERE tenant_id = ? ORDER BY label',
                [$tid],
            );
            $router->response()->html($router->view()->render('plugins/email/settings', [
                'title'    => 'Email Settings',
                'layout'   => 'app', 'sidebarItems' => $router->getSidebarItems(),
                'accounts' => $accounts,
            ]));
        });

        $router->post('/email/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) {
                return;
            }
            $tid = $router->tenant()->id();
            $data = [
                'tenant_id'    => $tid,
                'label'        => trim((string)($_POST['label'] ?? '')),
                'imap_host'    => trim((string)($_POST['imap_host'] ?? '')),
                'imap_port'    => (int)($_POST['imap_port'] ?? 993),
                'smtp_host'    => trim((string)($_POST['smtp_host'] ?? '')),
                'smtp_port'    => (int)($_POST['smtp_port'] ?? 587),
                'username'     => trim((string)($_POST['username'] ?? '')),
                'password'     => trim((string)($_POST['password'] ?? '')),
                'from_name'    => trim((string)($_POST['from_name'] ?? '')),
                'from_address' => trim((string)($_POST['from_address'] ?? '')),
            ];
            $cols = implode(', ', array_keys($data));
            $ph = implode(', ', array_fill(0, count($data), '?'));
            $db->query("INSERT INTO email_accounts ({$cols}) VALUES ({$ph})", array_values($data));
            AuditLog::record('email.account.created');
            $router->response()->redirect($router->tenant()->pathPrefix() . '/email/settings');
        });
    }
}
