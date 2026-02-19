<?php
/**
 * Messenger plugin — unified chat UI that aggregates Telegram + Email per employee.
 */

declare(strict_types=1);

namespace Src\Plugins\Messenger;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\Messaging\ChannelManager;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

require_once __DIR__ . '/../Email/EmailChannel.php';

final class Plugin implements PluginInterface
{
    public function name(): string
    {
        return 'Messenger';
    }

    public function requires(): array
    {
        return ['Core'];
    }

    public function sidebarItem(): ?array
    {
        return ['label' => 'Messenger', 'icon' => 'message-circle', 'route' => '/messenger'];
    }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        // Contact list
        $router->get('/messenger', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }

            $tid = $router->tenant()->id();
            $employees = $db->tenantFetchAll('employees', 'is_active = 1');

            // preload telegram and email metadata for employees
            $ids = array_map(fn($e) => (int)$e['id'], $employees ?: []);
            $meta = [];
            if (!empty($ids)) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $params = array_merge([$tid], $ids);
                // telegram chats grouped by employee
                $trows = $db->fetchAll(
                    "SELECT employee_id, chat_id, username, first_name FROM telegram_chats WHERE tenant_id = ? AND employee_id IN ($in)",
                    $params,
                );
                foreach ($trows as $r) {
                    $meta[(int)$r['employee_id']]['has_telegram'] = true;
                }

                // emails grouped by employee
                $erows = $db->fetchAll(
                    "SELECT employee_id, to_address, from_address FROM emails WHERE tenant_id = ? AND employee_id IN ($in)",
                    $params,
                );
                foreach ($erows as $r) {
                    $meta[(int)$r['employee_id']]['has_email'] = true;
                }

                // message counts (telegram_messages + emails) per employee
                $tmsgs = $db->fetchAll(
                    "SELECT tc.employee_id, COUNT(tm.id) AS cnt, MAX(tm.created_at) AS last_at FROM telegram_messages tm JOIN telegram_chats tc ON tm.chat_id = tc.chat_id AND tc.tenant_id = tm.tenant_id WHERE tm.tenant_id = ? AND tc.employee_id IN ($in) GROUP BY tc.employee_id",
                    $params,
                );
                foreach ($tmsgs as $r) {
                    $meta[(int)$r['employee_id']]['msg_count'] = (int)$r['cnt'];
                    $meta[(int)$r['employee_id']]['last_at'] = $r['last_at'];
                }

                $emsgs = $db->fetchAll(
                    "SELECT employee_id, COUNT(id) AS cnt, MAX(created_at) AS last_at FROM emails WHERE tenant_id = ? AND employee_id IN ($in) GROUP BY employee_id",
                    $params,
                );
                foreach ($emsgs as $r) {
                    $meta[(int)$r['employee_id']]['msg_count'] = (($meta[(int)$r['employee_id']]['msg_count'] ?? 0) + (int)$r['cnt']);
                    $existing = $meta[(int)$r['employee_id']]['last_at'] ?? null;
                    if ($existing === null || $r['last_at'] > $existing) {
                        $meta[(int)$r['employee_id']]['last_at'] = $r['last_at'];
                    }
                }
            }

            $router->response()->html($router->view()->render('plugins/messenger/index', [
                'title'     => 'Messenger',
                'layout'    => 'app', 'sidebarItems' => $router->getSidebarItems(),
                'employees' => $employees,
                'meta'      => $meta,
            ]));
        });

        // Conversation view
        $router->get('/messenger/employee/{employeeId}', function (array $params) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $employeeId = (int)$params['employeeId'];
            $employee = $db->fetchOne('SELECT * FROM employees WHERE tenant_id = ? AND id = ?', [$tid, $employeeId]);
            if (!$employee) {
                $router->response()->status(404)->html('<h1>Not found</h1>');
                return;
            }

            // fetch telegram chat for employee (if any)
            $tg = $db->fetchOne('SELECT * FROM telegram_chats WHERE tenant_id = ? AND employee_id = ? LIMIT 1', [$tid, $employeeId]);
            $tgChatId = $tg ? $tg['chat_id'] : null;

            // fetch messages from telegram_messages and emails, normalize
            $messages = [];
            if ($tgChatId !== null) {
                $tmsgs = $db->fetchAll('SELECT direction, body, created_at FROM telegram_messages WHERE tenant_id = ? AND chat_id = ? ORDER BY created_at ASC', [$tid, $tgChatId]);
                foreach ($tmsgs as $m) {
                    $messages[] = ['channel' => 'telegram', 'direction' => $m['direction'], 'body' => $m['body'], 'created_at' => $m['created_at']];
                }
            }

            $emsgs = $db->fetchAll('SELECT direction, subject, body, created_at FROM emails WHERE tenant_id = ? AND employee_id = ? ORDER BY created_at ASC', [$tid, $employeeId]);
            foreach ($emsgs as $m) {
                $body = trim((string)($m['subject'] ?? '')) !== '' ? ($m['subject'] . "\n\n" . $m['body']) : $m['body'];
                $messages[] = ['channel' => 'email', 'direction' => $m['direction'], 'body' => $body, 'created_at' => $m['created_at']];
            }

            // sort by created_at
            usort($messages, fn($a, $b) => strcmp($a['created_at'], $b['created_at']));

            $available = [];
            if ($tgChatId !== null) { $available[] = 'telegram'; }
            $hasEmail = (bool)$db->fetchOne('SELECT 1 FROM emails WHERE tenant_id = ? AND employee_id = ? LIMIT 1', [$tid, $employeeId]);
            if ($hasEmail) { $available[] = 'email'; }

            // contacts for left column
            $employees = $db->tenantFetchAll('employees', 'is_active = 1');

            $router->response()->html($router->view()->render('plugins/messenger/chat', [
                'title'     => 'Messenger',
                'layout'    => 'app', 'sidebarItems' => $router->getSidebarItems(),
                'employee'  => $employee,
                'messages'  => $messages,
                'available' => $available,
                'employees' => $employees,
            ]));
        });

        // Send message (via selected channel)
        $router->post('/messenger/employee/{employeeId}/send', function (array $params) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $employeeId = (int)$params['employeeId'];
            $channel = trim((string)($_POST['channel'] ?? ''));
            $body = trim((string)($_POST['body'] ?? ''));
            if ($body === '' || $channel === '' || $tid === null) {
                $router->response()->redirect($router->tenant()->pathPrefix() . '/messenger/employee/' . $employeeId);
                return;
            }

            if ($channel === 'telegram') {
                $tc = $db->fetchOne('SELECT chat_id FROM telegram_chats WHERE tenant_id = ? AND employee_id = ? LIMIT 1', [$tid, $employeeId]);
                if ($tc) {
                    $mgr = ChannelManager::getInstance();
                    $chan = $mgr->get('telegram');
                    if ($chan) {
                        $chan->send($tc['chat_id'], $body);
                        AuditLog::record('messenger.sent', 'telegram_chat', $employeeId);
                    }
                }
            } elseif ($channel === 'email') {
                // resolve recipient address: prefer linked user email, fallback to last known email in emails table
                $emp = $db->fetchOne('SELECT * FROM employees WHERE tenant_id = ? AND id = ?', [$tid, $employeeId]);
                $to = null;
                if (!empty($emp['user_id'])) {
                    $u = $db->fetchOne('SELECT email FROM users WHERE id = ?', [(int)$emp['user_id']]);
                    if ($u) { $to = $u['email']; }
                }
                if ($to === null) {
                    $row = $db->fetchOne('SELECT to_address AS addr FROM emails WHERE tenant_id = ? AND employee_id = ? AND to_address <> "" ORDER BY created_at DESC LIMIT 1', [$tid, $employeeId]);
                    if ($row) { $to = $row['addr']; }
                }

                if ($to !== null) {
                    // pick an active email account to send from
                    $acc = $db->fetchOne('SELECT id FROM email_accounts WHERE tenant_id = ? AND is_active = 1 ORDER BY id DESC LIMIT 1', [$tid]);
                    if ($acc) {
                        $emailChannelClass = '\\Src\\Plugins\\Email\\EmailChannel';
                        $chan = new $emailChannelClass($db, $tid, (int)$acc['id']);
                        $chan->send($to, $body, ['subject' => 'Message from HR Assistant']);
                        AuditLog::record('messenger.sent', 'employee', $employeeId);
                    }
                }
            }

            $router->response()->redirect($router->tenant()->pathPrefix() . '/messenger/employee/' . $employeeId);
        });

        // Simple polling API returning messages since timestamp (ISO string)
        $router->get('/messenger/api/messages/{employeeId}', function (array $params) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $employeeId = (int)$params['employeeId'];
            $since = (string)($_GET['since'] ?? '');

            $out = [];
            $tg = $db->fetchOne('SELECT chat_id FROM telegram_chats WHERE tenant_id = ? AND employee_id = ? LIMIT 1', [$tid, $employeeId]);
            if ($tg) {
                $q = 'SELECT direction, body, created_at FROM telegram_messages WHERE tenant_id = ? AND chat_id = ?' . ($since ? ' AND created_at > ?' : '') . ' ORDER BY created_at ASC';
                $params = $since ? [$tid, $tg['chat_id'], $since] : [$tid, $tg['chat_id']];
                $tmsgs = $db->fetchAll($q, $params);
                foreach ($tmsgs as $m) {
                    if ($since && $m['created_at'] <= $since) { continue; }
                    $out[] = ['channel' => 'telegram', 'direction' => $m['direction'], 'body' => $m['body'], 'created_at' => $m['created_at']];
                }
            }

            $q2 = 'SELECT direction, subject, body, created_at FROM emails WHERE tenant_id = ? AND employee_id = ?' . ($since ? ' AND created_at > ?' : '') . ' ORDER BY created_at ASC';
            $params2 = $since ? [$tid, $employeeId, $since] : [$tid, $employeeId];
            $emsgs = $db->fetchAll($q2, $params2);
            foreach ($emsgs as $m) {
                if ($since && $m['created_at'] <= $since) { continue; }
                $body = trim((string)($m['subject'] ?? '')) !== '' ? ($m['subject'] . "\n\n" . $m['body']) : $m['body'];
                $out[] = ['channel' => 'email', 'direction' => $m['direction'], 'body' => $body, 'created_at' => $m['created_at']];
            }

            usort($out, fn($a, $b) => strcmp($a['created_at'], $b['created_at']));
            header('Content-Type: application/json');
            echo json_encode($out);
        });
    }
}
