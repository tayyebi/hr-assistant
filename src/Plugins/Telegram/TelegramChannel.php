<?php
/**
 * Telegram Bot API adapter.
 * Uses plugin_settings for bot_token per tenant.
 * Webhook: POST /telegram/webhook
 */

declare(strict_types=1);

namespace Src\Plugins\Telegram;

use Src\Core\Database;
use Src\Core\Messaging\ChannelInterface;
use Src\Core\Messaging\Message;

final class TelegramChannel implements ChannelInterface
{
    public function __construct(
        private readonly Database $db,
        private readonly int $tenantId,
    ) {
    }

    public function identifier(): string
    {
        return 'telegram';
    }

    public function send(string $to, string $body, array $meta = []): bool
    {
        $token = $this->botToken();
        if ($token === '') {
            return false;
        }

        $url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
        $payload = json_encode([
            'chat_id' => $to,
            'text'    => $body,
            'parse_mode' => 'HTML',
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $this->storeMessage($to, 'outbound', $body);
            return true;
        }
        return false;
    }

    public function receive(): array
    {
        return [];
    }

    public function assignToEmployee(int $employeeId, string $channelAddress): void
    {
        $existing = $this->db->fetchOne(
            'SELECT id FROM telegram_chats WHERE tenant_id = ? AND chat_id = ?',
            [$this->tenantId, $channelAddress],
        );
        if ($existing) {
            $this->db->query(
                'UPDATE telegram_chats SET employee_id = ? WHERE id = ?',
                [$employeeId, $existing['id']],
            );
        } else {
            $this->db->query(
                'INSERT INTO telegram_chats (tenant_id, employee_id, chat_id) VALUES (?, ?, ?)',
                [$this->tenantId, $employeeId, $channelAddress],
            );
        }
    }

    public function handleWebhook(string $rawBody): void
    {
        $data = json_decode($rawBody, true);
        if (!is_array($data) || !isset($data['message'])) {
            return;
        }

        $msg = $data['message'];
        $chatId = (string)($msg['chat']['id'] ?? '');
        $text = (string)($msg['text'] ?? '');
        $username = (string)($msg['from']['username'] ?? '');
        $firstName = (string)($msg['from']['first_name'] ?? '');

        if ($chatId === '') {
            return;
        }

        $existing = $this->db->fetchOne(
            'SELECT id FROM telegram_chats WHERE tenant_id = ? AND chat_id = ?',
            [$this->tenantId, $chatId],
        );
        if (!$existing) {
            $this->db->query(
                'INSERT INTO telegram_chats (tenant_id, chat_id, username, first_name) VALUES (?, ?, ?, ?)',
                [$this->tenantId, $chatId, $username, $firstName],
            );
        }

        $this->storeMessage($chatId, 'inbound', $text, (string)($msg['message_id'] ?? ''));
    }

    private function storeMessage(string $chatId, string $direction, string $body, string $telegramMsgId = ''): void
    {
        $this->db->query(
            'INSERT INTO telegram_messages (tenant_id, chat_id, direction, body, telegram_message_id) '
            . 'VALUES (?, ?, ?, ?, ?)',
            [$this->tenantId, $chatId, $direction, $body, $telegramMsgId ?: null],
        );
    }

    private function botToken(): string
    {
        $row = $this->db->fetchOne(
            'SELECT `value` FROM plugin_settings WHERE tenant_id = ? AND plugin_name = ? AND `key` = ?',
            [$this->tenantId, 'Telegram', 'bot_token'],
        );
        return $row ? (string)$row['value'] : '';
    }
}
