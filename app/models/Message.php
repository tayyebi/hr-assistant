<?php

namespace App\Models;

use App\Core\Database;

/**
 * Message Model
 */
class Message
{
    private static array $headers = ['id', 'tenant_id', 'employee_id', 'sender', 'channel', 'text', 'subject', 'timestamp'];
    private static array $unassignedHeaders = ['id', 'tenant_id', 'channel', 'source_id', 'sender_name', 'text', 'subject', 'timestamp'];

    public static function getAll(string $tenantId): array
    {
        try {
            return Database::fetchAll('SELECT * FROM messages WHERE tenant_id = ? ORDER BY timestamp DESC', [$tenantId]);
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function getByEmployee(string $tenantId, string $employeeId): array
    {
        try {
            return Database::fetchAll('SELECT * FROM messages WHERE tenant_id = ? AND employee_id = ? ORDER BY timestamp DESC', [$tenantId, $employeeId]);
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function create(string $tenantId, array $data): array
    {
        $message = [
            'id' => 'msg_' . time() . '_' . mt_rand(1000, 9999),
            'tenant_id' => $tenantId,
            'employee_id' => $data['employee_id'] ?? '',
            'sender' => $data['sender'] ?? 'hr',
            'channel' => $data['channel'] ?? 'email',
            'text' => $data['text'] ?? '',
            'subject' => $data['subject'] ?? '',
            'timestamp' => date('c')
        ];
        try {
            Database::execute('INSERT INTO messages (id, tenant_id, employee_id, sender, channel, text, subject, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [
                $message['id'],
                $message['tenant_id'],
                $message['employee_id'] ?: null,
                $message['sender'],
                $message['channel'],
                $message['text'],
                $message['subject'],
                date('Y-m-d H:i:s')
            ]);
            return $message;
        } catch (\Exception $e) {
            return $message;
        }
    }

    public static function getUnassigned(string $tenantId): array
    {
        try {
            return Database::fetchAll('SELECT * FROM unassigned_messages WHERE tenant_id = ? ORDER BY timestamp DESC', [$tenantId]);
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function createUnassigned(string $tenantId, array $data): array
    {
        $message = [
            'id' => 'unassigned_' . time() . '_' . mt_rand(1000, 9999),
            'tenant_id' => $tenantId,
            'channel' => $data['channel'] ?? 'email',
            'source_id' => $data['source_id'] ?? '',
            'sender_name' => $data['sender_name'] ?? 'Unknown',
            'text' => $data['text'] ?? '',
            'subject' => $data['subject'] ?? '',
            'timestamp' => date('c')
        ];
        try {
            Database::execute('INSERT INTO unassigned_messages (id, tenant_id, channel, source_id, sender_name, text, subject, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [
                $message['id'],
                $message['tenant_id'],
                $message['channel'],
                $message['source_id'],
                $message['sender_name'],
                $message['text'],
                $message['subject'],
                date('Y-m-d H:i:s')
            ]);
            return $message;
        } catch (\Exception $e) {
            return $message;
        }
    }

    public static function assignToEmployee(string $tenantId, string $unassignedId, string $employeeId): bool
    {
        $employee = Employee::find($tenantId, $employeeId);
        if (!$employee) return false;

        try {
            $msg = Database::fetchOne('SELECT * FROM unassigned_messages WHERE tenant_id = ? AND id = ? LIMIT 1', [$tenantId, $unassignedId]);
            if (!$msg) return false;

            self::create($tenantId, [
                'employee_id' => $employeeId,
                'sender' => 'employee',
                'channel' => $msg['channel'],
                'text' => $msg['text'],
                'subject' => $msg['subject']
            ]);

            if ($msg['channel'] === 'telegram' && !empty($msg['source_id'])) {
                Employee::update($tenantId, $employeeId, ['telegram_chat_id' => $msg['source_id']]);
            }

            Database::execute('DELETE FROM unassigned_messages WHERE id = ? AND tenant_id = ?', [$unassignedId, $tenantId]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
