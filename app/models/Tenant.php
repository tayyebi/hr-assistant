<?php

namespace App\Models;

use App\Core\Database;

/**
 * Tenant Model
 */
class Tenant
{
    private static array $headers = ['id', 'name', 'status', 'created_at', 'updated_at'];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static function getAll(): array
    {
        try {
            // Try with status column first
            return Database::fetchAll('SELECT * FROM tenants ORDER BY created_at DESC');
        } catch (\Exception $e) {
            // Fallback for databases without status/created_at columns
            try {
                return Database::fetchAll('SELECT * FROM tenants ORDER BY id DESC');
            } catch (\Exception $e2) {
                return [];
            }
        }
    }

    public static function getActive(): array
    {
        try {
            return Database::fetchAll('SELECT * FROM tenants WHERE status = ? ORDER BY created_at DESC', [self::STATUS_ACTIVE]);
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
            'name' => $name,
            'status' => self::STATUS_ACTIVE
        ];

        try {
            // Try with status column
            Database::execute(
                'INSERT INTO tenants (id, name, status) VALUES (?, ?, ?)',
                [$id, $name, self::STATUS_ACTIVE]
            );
        } catch (\Exception $e) {
            // Fallback for databases without status column
            try {
                Database::execute('INSERT INTO tenants (id, name) VALUES (?, ?)', [$id, $name]);
            } catch (\Exception $e2) {
                // DB error
            }
        }
        
        return $tenant;
    }

    public static function update(string $id, array $data): bool
    {
        $fields = [];
        $values = [];

        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $values[] = $data['name'];
        }

        if (isset($data['status'])) {
            $fields[] = 'status = ?';
            $values[] = $data['status'];
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;

        $sql = 'UPDATE tenants SET ' . implode(', ', $fields) . ' WHERE id = ?';

        try {
            Database::execute($sql, $values);
            return true;
        } catch (\Exception $e) {
            // Retry without updated_at for backward compatibility
            return false;
        }
    }

    public static function deactivate(string $id): bool
    {
        return self::update($id, ['status' => self::STATUS_INACTIVE]);
    }

    public static function activate(string $id): bool
    {
        return self::update($id, ['status' => self::STATUS_ACTIVE]);
    }

    public static function delete(string $id): bool
    {
        try {
            // Note: This will cascade delete all related data due to foreign key constraints
            Database::execute('DELETE FROM tenants WHERE id = ?', [$id]);
            return true;
        } catch (\Exception $e) {
            return false;
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
