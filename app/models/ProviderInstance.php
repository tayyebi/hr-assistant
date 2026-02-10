<?php

namespace HRAssistant\Models;

use HRAssistant\Core\Database;

/**
 * ProviderInstance Model
 * Stores tenant-scoped provider configurations (e.g., GitLab for tenant X)
 */
class ProviderInstance
{
    private static array $headers = ['id', 'tenant_id', 'type', 'provider', 'name', 'settings', 'created_at', 'updated_at'];

    public static function getAll(string $tenantId): array
    {
        try {
            $rows = Database::fetchAll('SELECT * FROM provider_instances WHERE tenant_id = ? ORDER BY name ASC', [$tenantId]);
            foreach ($rows as &$r) {
                $r['settings'] = $r['settings'] ? json_decode($r['settings'], true) : [];
            }
            return $rows;
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function find(string $tenantId, string $id): ?array
    {
        try {
            $row = Database::fetchOne('SELECT * FROM provider_instances WHERE tenant_id = ? AND id = ? LIMIT 1', [$tenantId, $id]);
            if ($row) {
                $row['settings'] = $row['settings'] ? json_decode($row['settings'], true) : [];
                return $row;
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }

    public static function create(string $tenantId, array $data): array
    {
        $id = $data['id'] ?? ('prov_' . time() . '_' . mt_rand(1000, 9999));
        $settings = isset($data['settings']) ? json_encode($data['settings']) : json_encode([]);

        $row = [
            'id' => $id,
            'tenant_id' => $tenantId,
            'type' => $data['type'] ?? '',
            'provider' => $data['provider'] ?? '',
            'name' => $data['name'] ?? '',
            'settings' => $settings,
            'created_at' => date('c'),
            'updated_at' => date('c'),
        ];

        try {
            Database::execute('INSERT INTO provider_instances (id, tenant_id, type, provider, name, settings, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [
                $row['id'], $row['tenant_id'], $row['type'], $row['provider'], $row['name'], $row['settings'], date('Y-m-d H:i:s'), date('Y-m-d H:i:s')
            ]);
            $row['settings'] = json_decode($row['settings'], true);
            return $row;
        } catch (\Exception $e) {
            // Return the attempted row for fallback
            $row['settings'] = json_decode($row['settings'], true);
            return $row;
        }
    }

    public static function update(string $tenantId, string $id, array $data): bool
    {
        try {
            $set = [];
            $params = [];
            if (isset($data['name'])) { $set[] = '`name` = ?'; $params[] = $data['name']; }
            if (isset($data['settings'])) { $set[] = '`settings` = ?'; $params[] = json_encode($data['settings']); }
            if (isset($data['provider'])) { $set[] = '`provider` = ?'; $params[] = $data['provider']; }
            if (isset($data['type'])) { $set[] = '`type` = ?'; $params[] = $data['type']; }
            if (empty($set)) return false;
            $set[] = '`updated_at` = ?'; $params[] = date('Y-m-d H:i:s');
            $params[] = $tenantId; $params[] = $id;
            $sql = 'UPDATE provider_instances SET ' . implode(', ', $set) . ' WHERE tenant_id = ? AND id = ?';
            Database::execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function delete(string $tenantId, string $id): bool
    {
        try {
            Database::execute('DELETE FROM provider_instances WHERE tenant_id = ? AND id = ?', [$tenantId, $id]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function getByType(string $tenantId, string $type): array
    {
        $all = self::getAll($tenantId);
        return array_values(array_filter($all, fn($p) => $p['type'] === $type));
    }
}
