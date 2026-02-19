<?php
/**
 * Telegram plugin entry point.
 * Registers routes for inbox, webhook, chat assignment, and messaging.
 */

declare(strict_types=1);

namespace Src\Plugins\Telegram;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\Messaging\ChannelManager;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

require_once __DIR__ . '/TelegramChannel.php';

final class Plugin implements PluginInterface
{
    public function name(): string
    {
        return 'Telegram';
    }

    public function requires(): array
    {
        return ['Core'];
    }

    public function sidebarItem(): ?array
    {
        return ['label' => 'Telegram', 'icon' => 'send', 'route' => '/telegram'];
    }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        $tenantId = $tenant->id();
        if ($tenantId !== null) {
            $channel = new TelegramChannel($db, $tenantId);
            ChannelManager::getInstance()->register($channel);
        }

        $router->get('/telegram', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $chats = $db->fetchAll(
                'SELECT tc.*, e.first_name AS emp_first, e.last_name AS emp_last, '
                . '(SELECT COUNT(*) FROM telegram_messages tm WHERE tm.tenant_id = tc.tenant_id AND tm.chat_id = tc.chat_id) AS msg_count '
                . 'FROM telegram_chats tc '
                . 'LEFT JOIN employees e ON e.id = tc.employee_id '
                . 'WHERE tc.tenant_id = ? ORDER BY tc.created_at DESC',
                [$tid],
            );
            $router->response()->html($router->view()->render('plugins/telegram/index', [
                'title'  => 'Telegram',
                'layout' => 'app', 'sidebarItems' => $router->getSidebarItems(),
                'chats'  => $chats,
            ]));
        });

        $router->get('/telegram/chat/{chatId}', function (array $params) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $chatId = $params['chatId'];
            $messages = $db->fetchAll(
                'SELECT * FROM telegram_messages WHERE tenant_id = ? AND chat_id = ? ORDER BY created_at ASC',
                [$tid, $chatId],
            );
            $chat = $db->fetchOne(
                'SELECT * FROM telegram_chats WHERE tenant_id = ? AND chat_id = ?',
                [$tid, $chatId],
            );
            $employees = $db->tenantFetchAll('employees', 'is_active = 1');
            $router->response()->html($router->view()->render('plugins/telegram/chat', [
                'title'     => 'Telegram Chat',
                'layout'    => 'app', 'sidebarItems' => $router->getSidebarItems(),
                'messages'  => $messages,
                'chat'      => $chat,
                'employees' => $employees,
            ]));
        });

        $router->post('/telegram/chat/{chatId}/send', function (array $params) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $chatId = $params['chatId'];
            $body = trim((string)($_POST['body'] ?? ''));
            if ($body !== '' && $tid !== null) {
                $channel = new TelegramChannel($db, $tid);
                $channel->send($chatId, $body);
            }
            $router->response()->redirect($router->tenant()->pathPrefix() . '/telegram/chat/' . $chatId);
        });

        $router->post('/telegram/chat/{chatId}/assign', function (array $params) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $tid = $router->tenant()->id();
            $chatId = $params['chatId'];
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            if ($employeeId > 0 && $tid !== null) {
                $channel = new TelegramChannel($db, $tid);
                $channel->assignToEmployee($employeeId, $chatId);
                AuditLog::record('telegram.chat.assigned', 'telegram_chat', $employeeId, null, $chatId);
            }
            $router->response()->redirect($router->tenant()->pathPrefix() . '/telegram/chat/' . $chatId);
        });

        $router->post('/telegram/webhook', function () use ($db, $tenant): void {
            $tid = $tenant->id();
            if ($tid === null) {
                http_response_code(400);
                echo 'no tenant';
                return;
            }
            $raw = file_get_contents('php://input');
            $channel = new TelegramChannel($db, $tid);
            $channel->handleWebhook($raw);
            echo 'ok';
        });

        $router->get('/telegram/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) {
                return;
            }
            $tid = $router->tenant()->id();
            $token = $db->fetchOne(
                'SELECT `value` FROM plugin_settings WHERE tenant_id = ? AND plugin_name = ? AND `key` = ?',
                [$tid, 'Telegram', 'bot_token'],
            );
            $router->response()->html($router->view()->render('plugins/telegram/settings', [
                'title'    => 'Telegram Settings',
                'layout'   => 'app', 'sidebarItems' => $router->getSidebarItems(),
                'botToken' => $token ? $token['value'] : '',
            ]));
        });

        $router->post('/telegram/settings', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) {
                return;
            }
            $tid = $router->tenant()->id();
            $token = trim((string)($_POST['bot_token'] ?? ''));
            $db->query(
                'INSERT INTO plugin_settings (tenant_id, plugin_name, `key`, `value`) '
                . 'VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                [$tid, 'Telegram', 'bot_token', $token],
            );
            AuditLog::record('telegram.settings.updated');
            $router->response()->redirect($router->tenant()->pathPrefix() . '/telegram/settings');
        });
    }
}
