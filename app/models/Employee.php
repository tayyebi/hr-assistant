<?php
/**
 * Employee Model
 */
class Employee
{
    const FEELING_SAD = 'sad';
    const FEELING_NEUTRAL = 'neutral';
    const FEELING_HAPPY = 'happy';

    private static array $headers = [
        'id', 'tenant_id', 'full_name', 'email', 'telegram_chat_id', 
        'birthday', 'hired_date', 'position', 'team_id', 'feelings_log', 'accounts'
    ];

    public static function getAll(string $tenantId): array
    {
        $employees = ExcelStorage::readSheet("tenant_{$tenantId}.xlsx", 'employees');
        
        // Parse JSON fields
        foreach ($employees as &$emp) {
            $emp['feelings_log'] = $emp['feelings_log'] ? json_decode($emp['feelings_log'], true) : [];
            $emp['accounts'] = $emp['accounts'] ? json_decode($emp['accounts'], true) : [];
        }
        
        return $employees;
    }

    public static function find(string $tenantId, string $id): ?array
    {
        $employees = self::getAll($tenantId);
        
        foreach ($employees as $emp) {
            if ($emp['id'] === $id) {
                return $emp;
            }
        }
        
        return null;
    }

    public static function create(string $tenantId, array $data): array
    {
        $employee = [
            'id' => 'emp_' . time(),
            'tenant_id' => $tenantId,
            'full_name' => $data['full_name'] ?? '',
            'email' => $data['email'] ?? '',
            'telegram_chat_id' => $data['telegram_chat_id'] ?? '',
            'birthday' => $data['birthday'] ?? '',
            'hired_date' => $data['hired_date'] ?? date('Y-m-d'),
            'position' => $data['position'] ?? '',
            'team_id' => $data['team_id'] ?? '',
            'feelings_log' => json_encode([]),
            'accounts' => json_encode([])
        ];
        
        ExcelStorage::appendRow("tenant_{$tenantId}.xlsx", 'employees', $employee, self::$headers);
        
        return $employee;
    }

    public static function update(string $tenantId, string $id, array $data): bool
    {
        // Handle JSON fields
        if (isset($data['feelings_log']) && is_array($data['feelings_log'])) {
            $data['feelings_log'] = json_encode($data['feelings_log']);
        }
        if (isset($data['accounts']) && is_array($data['accounts'])) {
            $data['accounts'] = json_encode($data['accounts']);
        }
        
        return ExcelStorage::updateRow(
            "tenant_{$tenantId}.xlsx", 
            'employees', 
            'id', 
            $id, 
            $data, 
            self::$headers
        );
    }

    public static function delete(string $tenantId, string $id): bool
    {
        return ExcelStorage::deleteRow(
            "tenant_{$tenantId}.xlsx", 
            'employees', 
            'id', 
            $id, 
            self::$headers
        );
    }

    public static function getUpcomingBirthdays(string $tenantId, int $days = 30): array
    {
        $employees = self::getAll($tenantId);
        $upcoming = [];
        $today = new DateTime();
        
        foreach ($employees as $emp) {
            if (empty($emp['birthday'])) continue;
            
            $birthday = new DateTime($emp['birthday']);
            $birthday->setDate((int)$today->format('Y'), (int)$birthday->format('m'), (int)$birthday->format('d'));
            
            $diff = $today->diff($birthday)->days;
            
            if ($diff >= 0 && $diff <= $days) {
                $upcoming[] = $emp;
            }
        }
        
        return $upcoming;
    }

    public static function getSentimentStats(string $tenantId): array
    {
        $employees = self::getAll($tenantId);
        $stats = [
            self::FEELING_HAPPY => 0,
            self::FEELING_NEUTRAL => 0,
            self::FEELING_SAD => 0
        ];
        
        foreach ($employees as $emp) {
            if (!empty($emp['feelings_log'])) {
                $lastFeeling = end($emp['feelings_log']);
                if ($lastFeeling && isset($lastFeeling['feeling'])) {
                    $stats[$lastFeeling['feeling']]++;
                }
            }
        }
        
        return $stats;
    }
}
