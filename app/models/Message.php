<?php
/**
 * Message Model
 */
class Message
{
    private static array $headers = ['id', 'tenant_id', 'employee_id', 'sender', 'channel', 'text', 'subject', 'timestamp'];
    private static array $unassignedHeaders = ['id', 'tenant_id', 'channel', 'source_id', 'sender_name', 'text', 'subject', 'timestamp'];

    public static function getAll(string $tenantId): array
    {
        return ExcelStorage::readSheet("tenant_{$tenantId}.xlsx", 'messages');
    }

    public static function getByEmployee(string $tenantId, string $employeeId): array
    {
        $messages = self::getAll($tenantId);
        
        return array_filter($messages, fn($msg) => $msg['employee_id'] === $employeeId);
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
        
        ExcelStorage::appendRow("tenant_{$tenantId}.xlsx", 'messages', $message, self::$headers);
        
        return $message;
    }

    public static function getUnassigned(string $tenantId): array
    {
        return ExcelStorage::readSheet("tenant_{$tenantId}.xlsx", 'unassigned_messages');
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
        
        ExcelStorage::appendRow("tenant_{$tenantId}.xlsx", 'unassigned_messages', $message, self::$unassignedHeaders);
        
        return $message;
    }

    public static function assignToEmployee(string $tenantId, string $unassignedId, string $employeeId): bool
    {
        $unassigned = self::getUnassigned($tenantId);
        $employee = Employee::find($tenantId, $employeeId);
        
        if (!$employee) return false;
        
        foreach ($unassigned as $msg) {
            if ($msg['id'] === $unassignedId) {
                // Create a message in the conversation
                self::create($tenantId, [
                    'employee_id' => $employeeId,
                    'sender' => 'employee',
                    'channel' => $msg['channel'],
                    'text' => $msg['text'],
                    'subject' => $msg['subject']
                ]);
                
                // Update employee's telegram chat id if applicable
                if ($msg['channel'] === 'telegram' && !empty($msg['source_id'])) {
                    Employee::update($tenantId, $employeeId, ['telegram_chat_id' => $msg['source_id']]);
                }
                
                // Remove from unassigned
                ExcelStorage::deleteRow(
                    "tenant_{$tenantId}.xlsx",
                    'unassigned_messages',
                    'id',
                    $unassignedId,
                    self::$unassignedHeaders
                );
                
                return true;
            }
        }
        
        return false;
    }
}
