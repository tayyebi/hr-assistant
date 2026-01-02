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
        try {
            $rows = Database::fetchAll('SELECT * FROM employees WHERE tenant_id = ?', [$tenantId]);
            foreach ($rows as &$emp) {
                $emp['feelings_log'] = $emp['feelings_log'] ? json_decode($emp['feelings_log'], true) : [];
                $emp['accounts'] = $emp['accounts'] ? json_decode($emp['accounts'], true) : [];
            }
            return $rows;
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function find(string $tenantId, string $id): ?array
    {
        try {
            $row = Database::fetchOne('SELECT * FROM employees WHERE tenant_id = ? AND id = ? LIMIT 1', [$tenantId, $id]);
            if ($row) {
                $row['feelings_log'] = $row['feelings_log'] ? json_decode($row['feelings_log'], true) : [];
                $row['accounts'] = $row['accounts'] ? json_decode($row['accounts'], true) : [];
                return $row;
            }
        } catch (\Exception $e) {
            return null;
        }
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
        try {
            Database::execute('INSERT INTO employees (id, tenant_id, full_name, email, telegram_chat_id, birthday, hired_date, position, team_id, feelings_log, accounts) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $employee['id'],
                $employee['tenant_id'],
                $employee['full_name'],
                $employee['email'],
                $employee['telegram_chat_id'],
                $employee['birthday'] ?: null,
                $employee['hired_date'] ?: null,
                $employee['position'],
                $employee['team_id'] ?: null,
                $employee['feelings_log'],
                $employee['accounts']
            ]);
            return $employee;
        } catch (\Exception $e) {
            // DB error
            return $employee;
        }
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
        try {
            $setParts = [];
            $params = [];
            foreach ($data as $k => $v) {
                $setParts[] = "`$k` = ?";
                $params[] = $v;
            }
            $params[] = $tenantId;
            $params[] = $id;
            $sql = 'UPDATE employees SET ' . implode(', ', $setParts) . ' WHERE tenant_id = ? AND id = ?';
            Database::execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function delete(string $tenantId, string $id): bool
    {
        try {
            Database::execute('DELETE FROM employees WHERE tenant_id = ? AND id = ?', [$tenantId, $id]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
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
