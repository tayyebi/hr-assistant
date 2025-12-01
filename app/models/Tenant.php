<?php
/**
 * Tenant Model
 */
class Tenant
{
    private static array $headers = ['id', 'name'];

    public static function getAll(): array
    {
        return ExcelStorage::readSheet('system.xlsx', 'tenants');
    }

    public static function find(string $id): ?array
    {
        $tenants = self::getAll();
        
        foreach ($tenants as $tenant) {
            if ($tenant['id'] === $id) {
                return $tenant;
            }
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
        
        ExcelStorage::appendRow('system.xlsx', 'tenants', $tenant, self::$headers);
        
        // Initialize tenant data file
        ExcelStorage::initializeTenantData($id);
        
        return $tenant;
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
