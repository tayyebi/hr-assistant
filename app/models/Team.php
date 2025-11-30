<?php
/**
 * Team Model
 */
class Team
{
    private static array $headers = ['id', 'tenant_id', 'name', 'description', 'member_ids', 'email_aliases'];

    public static function getAll(string $tenantId): array
    {
        $teams = ExcelStorage::readSheet("tenant_{$tenantId}.xlsx", 'teams');
        
        // Parse JSON fields
        foreach ($teams as &$team) {
            $team['member_ids'] = $team['member_ids'] ? json_decode($team['member_ids'], true) : [];
            $team['email_aliases'] = $team['email_aliases'] ? json_decode($team['email_aliases'], true) : [];
        }
        
        return $teams;
    }

    public static function find(string $tenantId, string $id): ?array
    {
        $teams = self::getAll($tenantId);
        
        foreach ($teams as $team) {
            if ($team['id'] === $id) {
                return $team;
            }
        }
        
        return null;
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
        
        ExcelStorage::appendRow("tenant_{$tenantId}.xlsx", 'teams', $team, self::$headers);
        
        return $team;
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
        
        return ExcelStorage::updateRow(
            "tenant_{$tenantId}.xlsx", 
            'teams', 
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
            'teams', 
            'id', 
            $id, 
            self::$headers
        );
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
