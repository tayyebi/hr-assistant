<?php

namespace HRAssistant\Models;

use HRAssistant\Core\Database;

/**
 * Tenant Model
 */
class Tenant
{
    private static array $headers = ['id', 'name'];

    public static function getAll(): array
    {
        try {
            return Database::fetchAll('SELECT * FROM tenants');
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function find(string $id): ?array
    {
        try {
            $row = Database::fetchOne('SELECT * FROM tenants WHERE id = ? LIMIT 1', [$id]);
            if ($row) return $row;
        } catch (\Exception $e) {
            // fallback
        }

            return null;
    }

    public static function create(string $name): array
    {
        $id = 'tenant_' . time();
        $tenant = [
            'id' => $id,
            'name' => $name
        ];

        try {
            Database::execute('INSERT INTO tenants (id, name) VALUES (?, ?)', [$id, $name]);
            return $tenant;
        } catch (\Exception $e) {
            // DB error
            return $tenant;
        }
    }

    public static function getCurrentTenant(): ?array
    {
        $tenantId = User::getTenantId();
        
        if (!$tenantId) {
            return null;
        }
        
        return self::find($tenantId);
    }
}
