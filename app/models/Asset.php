<?php
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
        'id', 'tenant_id', 'employee_id', 'provider', 'asset_type', 
        'identifier', 'status', 'metadata', 'created_at', 'updated_at'
    ];

    /**
     * Get all assets for a tenant
     */
    public static function getAll(string $tenantId): array
    {
        $assets = ExcelStorage::readSheet("tenant_{$tenantId}.xlsx", 'assets');
        
        // Parse JSON metadata
        foreach ($assets as &$asset) {
            $asset['metadata'] = $asset['metadata'] ? json_decode($asset['metadata'], true) : [];
        }
        
        return $assets;
    }

    /**
     * Get assets by employee
     */
    public static function getByEmployee(string $tenantId, string $employeeId): array
    {
        $assets = self::getAll($tenantId);
        
        return array_filter($assets, fn($asset) => $asset['employee_id'] === $employeeId);
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
        $assets = self::getAll($tenantId);
        
        foreach ($assets as $asset) {
            if ($asset['id'] === $id) {
                return $asset;
            }
        }
        
        return null;
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
            'asset_type' => $data['asset_type'] ?? '',
            'identifier' => $data['identifier'] ?? '',
            'status' => $data['status'] ?? self::STATUS_PENDING,
            'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : json_encode([]),
            'created_at' => date('c'),
            'updated_at' => date('c')
        ];
        
        ExcelStorage::appendRow("tenant_{$tenantId}.xlsx", 'assets', $asset, self::$headers);
        
        return $asset;
    }

    /**
     * Update an asset
     */
    public static function update(string $tenantId, string $id, array $data): bool
    {
        $assets = self::getAll($tenantId);
        $found = false;
        
        foreach ($assets as &$asset) {
            if ($asset['id'] === $id) {
                if (isset($data['status'])) {
                    $asset['status'] = $data['status'];
                }
                if (isset($data['metadata'])) {
                    $asset['metadata'] = json_encode($data['metadata']);
                }
                if (isset($data['identifier'])) {
                    $asset['identifier'] = $data['identifier'];
                }
                $asset['updated_at'] = date('c');
                $found = true;
                break;
            }
        }
        
        if ($found) {
            ExcelStorage::writeSheet("tenant_{$tenantId}.xlsx", 'assets', $assets, self::$headers);
            return true;
        }
        
        return false;
    }

    /**
     * Delete an asset
     */
    public static function delete(string $tenantId, string $id): bool
    {
        $assets = self::getAll($tenantId);
        $filtered = array_filter($assets, fn($asset) => $asset['id'] !== $id);
        
        if (count($filtered) < count($assets)) {
            ExcelStorage::writeSheet("tenant_{$tenantId}.xlsx", 'assets', $filtered, self::$headers);
            return true;
        }
        
        return false;
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
