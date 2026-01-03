<?php

/**
 * Asset Manager
 * Handles fetching, caching, and listing assets from configured providers
 */
class AssetManager
{
    /**
     * @var string Tenant ID
     */
    private $tenantId;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Get all available assets from all configured and enabled providers
     *
     * @return array Array of assets grouped by provider type
     */
    public function getAllAvailableAssets(): array
    {
        $assets = [];
        $enabledProviders = $this->getEnabledProviders();

        foreach ($enabledProviders as $providerType => $providerConfig) {
            try {
                $provider = ProviderFactory::create($this->tenantId, $providerType, $providerConfig);
                $providerAssets = $provider->listAssets();

                if (!empty($providerAssets)) {
                    $assets[$providerType] = $providerAssets;
                }
            } catch (Exception $e) {
                error_log("Error fetching assets from $providerType: " . $e->getMessage());
                // Continue with next provider on error
            }
        }

        return $assets;
    }

    /**
     * Get assets of a specific type from all configured providers
     *
     * @param string $assetType Asset type (email, git, messenger, iam)
     * @return array Assets grouped by provider
     */
    public function getAssetsByType(string $assetType): array
    {
        $assets = [];
        $providers = ProviderType::getByType($assetType);

        foreach ($providers as $providerType) {
            try {
                $providerConfig = $this->getProviderConfig($providerType);
                if ($providerConfig) {
                    $provider = ProviderFactory::create($this->tenantId, $providerType, $providerConfig);
                    $providerAssets = $provider->listAssets();

                    if (!empty($providerAssets)) {
                        $assets[$providerType] = $providerAssets;
                    }
                }
            } catch (Exception $e) {
                error_log("Error fetching $assetType assets from $providerType: " . $e->getMessage());
            }
        }

        return $assets;
    }

    /**
     * Get assets for a specific provider
     *
     * @param string $providerType Provider type (e.g., 'mailcow', 'gitlab', 'telegram')
     * @return array Array of assets from that provider
     */
    public function getAssetsByProvider(string $providerType): array
    {
        try {
            $providerConfig = $this->getProviderConfig($providerType);
            if (!$providerConfig) {
                return [];
            }

            $provider = ProviderFactory::create($this->tenantId, $providerType, $providerConfig);
            return $provider->listAssets() ?? [];
        } catch (Exception $e) {
            error_log("Error fetching assets from provider $providerType: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Test connection to a specific provider
     *
     * @return array Status with 'success' and optional 'error' message
     */
    public function testProviderConnection(string $providerType): array
    {
        try {
            $providerConfig = $this->getProviderConfig($providerType);
            if (!$providerConfig) {
                return [
                    'success' => false,
                    'error' => 'Provider not configured',
                ];
            }

            $provider = ProviderFactory::create($this->tenantId, $providerType, $providerConfig);
            $connected = $provider->testConnection();

            return [
                'success' => $connected,
                'error' => $connected ? null : 'Connection test failed. Check credentials.',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get all enabled providers with their configurations
     *
     * @return array Providers keyed by type with their configs
     */
    public function getEnabledProviders(): array
    {
        $allProviders = ProviderType::getAll();
        $enabledProviders = [];

        foreach ($allProviders as $providerType) {
            $providerConfig = $this->getProviderConfig($providerType);
            if ($providerConfig && !empty($providerConfig)) {
                $enabledProviders[$providerType] = $providerConfig;
            }
        }

        return $enabledProviders;
    }

    /**
     * Get configuration for a specific provider
     *
     * @return array|null Configuration array or null if not configured
     */
    public function getProviderConfig(string $providerType): ?array
    {
        if (!ProviderType::isValid($providerType)) {
            return null;
        }

        $tenantConfig = Config::get($this->tenantId);
        $providerConfig = [];

        // Get all required fields for this provider
        $requiredFields = ProviderSettings::getFields($providerType);

        foreach ($requiredFields as $fieldKey => $fieldDef) {
            if (isset($tenantConfig[$fieldKey])) {
                $providerConfig[$fieldKey] = $tenantConfig[$fieldKey];
            }
        }

        // Only return config if all required fields are present
        return !empty($providerConfig) ? $providerConfig : null;
    }

    /**
     * Get asset details with full information
     *
     * @return array|null Asset with all details or null
     */
    public function getAssetDetails(string $providerType, string $assetId): ?array
    {
        try {
            $providerConfig = $this->getProviderConfig($providerType);
            if (!$providerConfig) {
                return null;
            }

            $provider = ProviderFactory::create($this->tenantId, $providerType, $providerConfig);
            return $provider->getAsset($assetId);
        } catch (Exception $e) {
            error_log("Error fetching asset details: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a secure random password
     */
    private function generatePassword(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
    public function assignAssetToEmployee(
        string $employeeId,
        string $providerTypeOrInstance,
        string $assetIdentifier,
        string $assetType,
        ?string $providerInstanceId = null
    ): array {
        try {
            // Get provider instance
            $prov = null;
            $providerType = $providerTypeOrInstance;
            if ($providerInstanceId) {
                $prov = ProviderInstance::find($this->tenantId, $providerInstanceId);
                if ($prov) {
                    $providerType = $prov['provider'];
                }
            }

            if (!$prov && !$providerInstanceId) {
                // Find any instance for the provider type
                $instances = ProviderInstance::getAll($this->tenantId);
                $prov = array_filter($instances, fn($i) => $i['provider'] === $providerType);
                $prov = $prov ? reset($prov) : null;
            }

            if (!$prov) {
                return ['success' => false, 'password' => null];
            }

            // Create provider
            $provider = ProviderFactory::create($this->tenantId, $providerType, $prov['settings'] ?? [], $prov['version'] ?? '1.0');

            // Get employee details
            $employee = Employee::find($this->tenantId, $employeeId);
            if (!$employee) {
                return ['success' => false, 'password' => null];
            }

            // Call createAsset
            $createData = [
                'identifier' => $assetIdentifier,
                'asset_type' => $assetType,
                'employee' => $employee,
            ];
            $createdAsset = $provider->createAsset($createData);

            if (!$createdAsset) {
                return ['success' => false, 'password' => null];
            }

            // Store the assignment
            $assetId = Asset::create($this->tenantId, [
                'employee_id' => $employeeId,
                'provider' => $providerType,
                'provider_instance_id' => $prov['id'],
                'asset_type' => $assetType,
                'identifier' => $createdAsset['id'] ?? $assetIdentifier,
                'status' => Asset::STATUS_ACTIVE,
                'metadata' => json_encode($createdAsset['metadata'] ?? []),
            ]);

            return ['success' => true, 'password' => $createdAsset['password'] ?? null];
        } catch (Exception $e) {
            error_log("Error assigning asset to employee: " . $e->getMessage());
            return ['success' => false, 'password' => null];
        }
    }

    /**
     * Unassign an asset from an employee
     *
     * @return bool True on success
     */
    public function unassignAsset(string $assetId): bool
    {
        try {
            Asset::delete($this->tenantId, $assetId);
            return true;
        } catch (Exception $e) {
            error_log("Error unassigning asset: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all assets assigned to an employee
     *
     * @return array Assets assigned to employee
     */
    public function getEmployeeAssets(string $employeeId): array
    {
        try {
            return Asset::getByEmployee($this->tenantId, $employeeId) ?? [];
        } catch (Exception $e) {
            error_log("Error fetching employee assets: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available assets grouped by type for UI rendering
     *
     * @return array Grouped assets ready for UI
     */
    public function getAvailableAssetsGrouped(): array
    {
        $grouped = [
            ProviderType::TYPE_EMAIL => [
                'name' => 'Email Accounts',
                'providers' => [],
            ],
            ProviderType::TYPE_GIT => [
                'name' => 'Git Accounts',
                'providers' => [],
            ],
            ProviderType::TYPE_MESSENGER => [
                'name' => 'Messaging',
                'providers' => [],
            ],
            ProviderType::TYPE_IAM => [
                'name' => 'Identity & Access',
                'providers' => [],
            ],
        ];

        $allAssets = $this->getAllAvailableAssets();

        foreach ($allAssets as $providerType => $assets) {
            $assetType = ProviderType::getAssetType($providerType);
            if ($assetType && isset($grouped[$assetType])) {
                $grouped[$assetType]['providers'][$providerType] = [
                    'name' => ProviderType::getName($providerType),
                    'assets' => $assets,
                ];
            }
        }

        return $grouped;
    }
}
