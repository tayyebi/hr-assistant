<?php

namespace HRAssistant\Models;

use HRAssistant\Core\Database;

/**
 * Team Model
 */
class Team
{
    private static array $headers = ['id', 'tenant_id', 'name', 'description', 'member_ids', 'email_aliases'];

    public static function getAll(string $tenantId): array
    {
        try {
            $rows = Database::fetchAll('SELECT * FROM teams WHERE tenant_id = ?', [$tenantId]);
            foreach ($rows as &$team) {
                $team['member_ids'] = $team['member_ids'] ? json_decode($team['member_ids'], true) : [];
                $team['email_aliases'] = $team['email_aliases'] ? json_decode($team['email_aliases'], true) : [];
            }
            return $rows;
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function find(string $tenantId, string $id): ?array
    {
        try {
            $row = Database::fetchOne('SELECT * FROM teams WHERE tenant_id = ? AND id = ? LIMIT 1', [$tenantId, $id]);
            if ($row) {
                $row['member_ids'] = $row['member_ids'] ? json_decode($row['member_ids'], true) : [];
                $row['email_aliases'] = $row['email_aliases'] ? json_decode($row['email_aliases'], true) : [];
                return $row;
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function create(string $tenantId, array $data): array
    {
        $team = [
            'id' => 'team_' . time(),
            'tenant_id' => $tenantId,
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? 'New team description',
            'member_ids' => json_encode([]),
            'email_aliases' => json_encode([])
        ];

        try {
            Database::execute('INSERT INTO teams (id, tenant_id, name, description, member_ids, email_aliases) VALUES (?, ?, ?, ?, ?, ?)', [
                $team['id'],
                $team['tenant_id'],
                $team['name'],
                $team['description'],
                $team['member_ids'],
                $team['email_aliases']
            ]);

            return $team;
        } catch (\Exception $e) {
            // DB error
            return $team;
        }
    }

    public static function update(string $tenantId, string $id, array $data): bool
    {
        // Handle JSON fields
        if (isset($data['member_ids']) && is_array($data['member_ids'])) {
            $data['member_ids'] = json_encode($data['member_ids']);
        }
        if (isset($data['email_aliases']) && is_array($data['email_aliases'])) {
            $data['email_aliases'] = json_encode($data['email_aliases']);
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
            $sql = 'UPDATE teams SET ' . implode(', ', $setParts) . ' WHERE tenant_id = ? AND id = ?';
            Database::execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function delete(string $tenantId, string $id): bool
    {
        try {
            Database::execute('DELETE FROM teams WHERE tenant_id = ? AND id = ?', [$tenantId, $id]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function addMember(string $tenantId, string $teamId, string $employeeId): bool
    {
        $team = self::find($tenantId, $teamId);
        
        if (!$team) return false;
        
        if (!in_array($employeeId, $team['member_ids'])) {
            $team['member_ids'][] = $employeeId;
            self::update($tenantId, $teamId, ['member_ids' => $team['member_ids']]);
            
            // Update employee's team_id
            Employee::update($tenantId, $employeeId, ['team_id' => $teamId]);
        }
        
        return true;
    }

    public static function removeMember(string $tenantId, string $teamId, string $employeeId): bool
    {
        $team = self::find($tenantId, $teamId);
        
        if (!$team) return false;
        
        $team['member_ids'] = array_values(array_filter($team['member_ids'], fn($id) => $id !== $employeeId));
        self::update($tenantId, $teamId, ['member_ids' => $team['member_ids']]);
        
        // Clear employee's team_id
        Employee::update($tenantId, $employeeId, ['team_id' => '']);
        
        return true;
    }

    public static function addAlias(string $tenantId, string $teamId, string $alias): bool
    {
        $team = self::find($tenantId, $teamId);
        
        if (!$team) return false;
        
        if (!in_array($alias, $team['email_aliases'])) {
            $team['email_aliases'][] = $alias;
            self::update($tenantId, $teamId, ['email_aliases' => $team['email_aliases']]);
        }
        
        return true;
    }

    public static function removeAlias(string $tenantId, string $teamId, string $alias): bool
    {
        $team = self::find($tenantId, $teamId);
        
        if (!$team) return false;
        
        $team['email_aliases'] = array_values(array_filter($team['email_aliases'], fn($a) => $a !== $alias));
        self::update($tenantId, $teamId, ['email_aliases' => $team['email_aliases']]);
        
        return true;
    }
}
