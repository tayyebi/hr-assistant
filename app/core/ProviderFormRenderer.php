<?php

namespace App\Core;

use App\Models\ProviderInstance;

/**
 * Provider Form Renderer
 * Provides reusable methods for provider form handling across controllers
 */
class ProviderFormRenderer
{
    /**
     * Get provider instances filtered by asset type
     */
    public static function getInstancesByAssetType(string $tenantId, string $assetType): array
    {
        $allInstances = ProviderInstance::getAll($tenantId);
        return array_filter($allInstances, function($instance) use ($assetType) {
            return ProviderType::getAssetType($instance['provider']) === $assetType;
        });
    }

    /**
     * Create a new provider instance
     */
    public static function createInstance(string $tenantId, array $data): array
    {
        $type = $data['type'] ?? '';
        $provider = $data['provider'] ?? '';
        $name = $data['name'] ?? '';
        $config = $data['config'] ?? [];

        // Validate required fields
        if (empty($type) || empty($provider) || empty($name)) {
            return [
                'success' => false,
                'message' => 'Type, provider and name are required for provider instance.'
            ];
        }

        // Validate provider belongs to type
        $providers = ProviderSettings::getProvidersMetadata();
        if (!isset($providers[$provider]) || ($providers[$provider]['type'] ?? '') !== $type) {
            return [
                'success' => false,
                'message' => 'Provider does not match selected type.'
            ];
        }

        // Validate and process configuration fields
        $providerFields = ProviderSettings::getFields($provider);
        $processedSettings = [];
        $validationErrors = [];

        foreach ($providerFields as $fieldName => $fieldConfig) {
            $value = $config[$fieldName] ?? '';
            
            // Check required fields
            if (($fieldConfig['required'] ?? false) && empty($value)) {
                $validationErrors[] = "Field '{$fieldConfig['label']}' is required.";
                continue;
            }
            
            // Process value based on field type
            switch ($fieldConfig['type'] ?? 'text') {
                case 'checkbox':
                    $processedSettings[$fieldName] = !empty($value) ? true : false;
                    break;
                case 'number':
                    if (!empty($value)) {
                        $processedSettings[$fieldName] = (int) $value;
                    }
                    break;
                default:
                    if (!empty($value)) {
                        $processedSettings[$fieldName] = $value;
                    }
                    break;
            }
        }

        // If there are validation errors, return with error message
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'message' => 'Validation failed: ' . implode(' ', $validationErrors)
            ];
        }

        ProviderInstance::create($tenantId, [
            'type' => $type,
            'provider' => $provider,
            'name' => $name,
            'settings' => $processedSettings
        ]);

        return [
            'success' => true,
            'message' => 'Provider instance created successfully.'
        ];
    }

    /**
     * Delete a provider instance
     */
    public static function deleteInstance(string $tenantId, string $id): array
    {
        $instance = ProviderInstance::find($tenantId, $id);
        if (!$instance) {
            return [
                'success' => false,
                'message' => 'Provider instance not found.'
            ];
        }

        ProviderInstance::delete($tenantId, $id);

        return [
            'success' => true,
            'message' => 'Provider instance removed.'
        ];
    }

    /**
     * Test provider connection
     */
    public static function testConnection(string $tenantId, string $provider, array $config): array
    {
        if (empty($provider)) {
            return [
                'success' => false,
                'message' => 'Provider is required'
            ];
        }

        try {
            // Create provider instance
            $providerInstance = ProviderFactory::create($tenantId, $provider, $config);
            
            // Test connection
            if ($providerInstance->testConnection()) {
                return [
                    'success' => true,
                    'message' => 'Connection successful! Provider is properly configured.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Connection test failed. Please verify your configuration.'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get provider instances with connection status
     */
    public static function getInstancesWithStatus(string $tenantId, array $instances): array
    {
        $withStatus = [];
        
        foreach ($instances as $instance) {
            $status = 'unknown';
            $statusMessage = '';
            
            try {
                $provider = ProviderFactory::create($tenantId, $instance['provider'], $instance['settings'] ?? []);
                if ($provider->testConnection()) {
                    $status = 'ok';
                } else {
                    $status = 'error';
                    $statusMessage = 'Connection test failed';
                }
            } catch (\Exception $e) {
                $status = 'error';
                $statusMessage = $e->getMessage();
            }
            
            $withStatus[] = array_merge($instance, [
                'status' => $status,
                'statusMessage' => $statusMessage
            ]);
        }

        return $withStatus;
    }

    /**
     * Render HTML for provider creation form (for use in views)
     */
    public static function renderCreateForm(string $workspaceUrl, string $assetType, array $assetMetadata): string
    {
        $providers = ProviderSettings::getProvidersMetadata();
        $filteredProviders = [];
        
        // Filter providers by asset type
        foreach ($providers as $key => $provider) {
            if ($provider['type'] === $assetMetadata['type']) {
                $filteredProviders[$key] = $provider;
            }
        }

        ob_start();
        ?>
        <article>
            <h4>Add <?php echo htmlspecialchars($assetMetadata['name']); ?> Provider</h4>
            <form method="POST" action="<?php echo htmlspecialchars($workspaceUrl); ?>" id="provider-form-<?php echo htmlspecialchars($assetType); ?>">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
                    <div>
                        <label>Provider</label>
                        <select name="provider" required id="provider-select-<?php echo htmlspecialchars($assetType); ?>">
                            <option value="">Choose provider</option>
                            <?php foreach ($filteredProviders as $pKey => $pMeta): ?>
                                <option value="<?php echo htmlspecialchars($pKey); ?>">
                                    <?php echo htmlspecialchars($pMeta['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Instance Name</label>
                        <input type="text" name="name" placeholder="e.g. Main <?php echo htmlspecialchars($assetMetadata['name']); ?>" required>
                    </div>
                </div>
                
                <div id="provider-config-<?php echo htmlspecialchars($assetType); ?>" style="display: none;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--spacing-md); margin-bottom: var(--spacing-md);" id="config-fields-<?php echo htmlspecialchars($assetType); ?>"></div>
                    <div style="display: flex; gap: var(--spacing-sm); align-items: center; margin-bottom: var(--spacing-md);">
                        <button type="button" id="test-connection-btn-<?php echo htmlspecialchars($assetType); ?>" style="background: var(--color-info); display: none;" 
                                data-test-url="<?php echo htmlspecialchars(\App\Core\UrlHelper::workspace('/api/test-provider-connection/')); ?>">
                            Test Connection
                        </button>
                        <div id="test-result-<?php echo htmlspecialchars($assetType); ?>" style="flex: 1; padding: 0.5rem; border-radius: 4px; display: none;"></div>
                    </div>
                </div>
                
                <button type="submit">Add Provider</button>
            </form>
        </article>
        <?php
        return ob_get_clean();
    }
}
