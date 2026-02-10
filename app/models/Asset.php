<?php

namespace HRAssistant\Models;

use HRAssistant\Core\Database;

/**
 * Asset Model
 * Manages digital assets (email accounts, git accounts, messenger accounts, etc.)
 */
class Asset
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_FAILED = 'failed';

    private static array $headers = [
        'id', 'tenant_id', 'employee_id', 'provider', 'provider_instance_id', 'asset_type', 
        'identifier', 'status', 'metadata', 'created_at', 'updated_at'
    ];

    /**
     * Get all assets for a tenant
     */
    public static function getAll(string $tenantId): array
    {
        try {
            $rows = Database::fetchAll('SELECT * FROM assets WHERE tenant_id = ?', [$tenantId]);
            foreach ($rows as &$asset) {
                // Normalize DB columns to model shape
                $asset['metadata'] = $asset['metadata'] ? json_decode($asset['metadata'], true) : [];
                $asset['identifier'] = $asset['asset_identifier'] ?? ($asset['identifier'] ?? '');
                // Keep backwards-compatible keys
                if (!isset($asset['status'])) $asset['status'] = $asset['status'] ?? null;
            }
            return $rows;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get assets by employee
     */
    public static function getByEmployee(string $tenantId, string $employeeId): array
    {
        try {
            $rows = Database::fetchAll('SELECT * FROM assets WHERE tenant_id = ? AND employee_id = ?', [$tenantId, $employeeId]);
            foreach ($rows as &$asset) {
                $asset['metadata'] = $asset['metadata'] ? json_decode($asset['metadata'], true) : [];
                $asset['identifier'] = $asset['asset_identifier'] ?? ($asset['identifier'] ?? '');
                if (!isset($asset['status'])) $asset['status'] = $asset['status'] ?? null;
            }
            return $rows;
        } catch (\Exception $e) {
            $assets = self::getAll($tenantId);
            return array_filter($assets, fn($asset) => $asset['employee_id'] === $employeeId);
        }
    }

    /**
     * Get assets by provider
     */
    public static function getByProvider(string $tenantId, string $provider): array
    {
        $assets = self::getAll($tenantId);
        
        return array_filter($assets, fn($asset) => $asset['provider'] === $provider);
    }

    /**
     * Get assets by asset type (email, git, messenger, iam)
     */
    public static function getByAssetType(string $tenantId, string $assetType): array
    {
        $assets = self::getAll($tenantId);
        
        return array_filter($assets, fn($asset) => $asset['asset_type'] === $assetType);
    }

    /**
     * Get assets by status
     */
    public static function getByStatus(string $tenantId, string $status): array
    {
        $assets = self::getAll($tenantId);
        
        return array_filter($assets, fn($asset) => $asset['status'] === $status);
    }

    /**
     * Find a specific asset
     */
    public static function find(string $tenantId, string $id): ?array
    {
        try {
            $row = Database::fetchOne('SELECT * FROM assets WHERE tenant_id = ? AND id = ? LIMIT 1', [$tenantId, $id]);
            if ($row) {
                $row['metadata'] = $row['metadata'] ? json_decode($row['metadata'], true) : [];
                $row['identifier'] = $row['asset_identifier'] ?? ($row['identifier'] ?? '');
                return $row;
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create a new asset
     */
    public static function create(string $tenantId, array $data): array
    {
        $asset = [
            'id' => $data['id'] ?? ('asset_' . time() . '_' . mt_rand(1000, 9999)),
            'tenant_id' => $tenantId,
            'employee_id' => $data['employee_id'] ?? '',
            'provider' => $data['provider'] ?? '',
            'provider_instance_id' => $data['provider_instance_id'] ?? null,
            'asset_type' => $data['asset_type'] ?? '',
            'identifier' => $data['identifier'] ?? '',
            'status' => $data['status'] ?? self::STATUS_PENDING,
            'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : json_encode([]),
            'created_at' => date('c'),
            'updated_at' => date('c')
        ];
        try {
            Database::execute('INSERT INTO assets (id, tenant_id, employee_id, provider, provider_instance_id, asset_identifier, asset_type, status, metadata, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $asset['id'],
                $asset['tenant_id'],
                $asset['employee_id'] ?: null,
                $asset['provider'],
                $asset['provider_instance_id'],
                $asset['identifier'],
                $asset['asset_type'],
                $asset['status'],
                $asset['metadata'],
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            return $asset;
        } catch (\Exception $e) {
            return $asset;
        }
    }

    /**
     * Update an asset
     */
    public static function update(string $tenantId, string $id, array $data): bool
    {
        try {
            $setParts = [];
            $params = [];
            foreach ($data as $k => $v) {
                if ($k === 'metadata' && is_array($v)) $v = json_encode($v);
                // map identifier -> asset_identifier column
                if ($k === 'identifier') {
                    $k = 'asset_identifier';
                }
                $setParts[] = "`$k` = ?";
                $params[] = $v;
            }
            // add updated_at
            $setParts[] = "`updated_at` = ?";
            $params[] = date('Y-m-d H:i:s');

            $params[] = $tenantId;
            $params[] = $id;
            $sql = 'UPDATE assets SET ' . implode(', ', $setParts) . ' WHERE tenant_id = ? AND id = ?';
            Database::execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            // fallback - not implemented for Excel update
            return false;
        }
    }

    /**
     * Delete an asset
     */
    public static function delete(string $tenantId, string $id): bool
    {
        try {
            Database::execute('DELETE FROM assets WHERE tenant_id = ? AND id = ?', [$tenantId, $id]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get unique asset types in use
     */
    public static function getAssetTypes(string $tenantId): array
    {
        $assets = self::getAll($tenantId);
        $types = [];
        
        foreach ($assets as $asset) {
            $types[$asset['asset_type']] = true;
        }
        
        return array_keys($types);
    }

    /**
     * Get unique providers in use
     */
    public static function getProviders(string $tenantId): array
    {
        $assets = self::getAll($tenantId);
        $providers = [];
        
        foreach ($assets as $asset) {
            $providers[$asset['provider']] = true;
        }
        
        return array_keys($providers);
    }
}
