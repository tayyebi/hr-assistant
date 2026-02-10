<?php

namespace App\Controllers;

use App\Models\{User, Config, Tenant};
use App\Core\View;

/**
 * Settings Controller
 * Manages provider configuration
 */
class SettingsController
{
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        $config = Config::get($tenantId);
        $providers = \App\Core\ProviderSettings::getProvidersMetadata();
        $providersConfig = $this->getEnabledProvidersConfig($tenantId, $config);
        
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        
        View::render('settings', [
            'tenant' => $tenant,
            'user' => $user,
            'config' => $config,
            'providers' => $providers,
            'providersConfig' => $providersConfig,
            'message' => $message,
            'activeTab' => 'settings'
        ]);
    }

    public function save(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        
        // Get all allowed configuration fields
        $allFields = \App\Core\ProviderSettings::getAllFields();
        $config = Config::get($tenantId);
        
        // Process POST data for all known provider fields
        foreach ($allFields as $provider => $fields) {
            foreach ($fields as $fieldKey => $field) {
                if (isset($_POST[$fieldKey])) {
                    if ($field['type'] === 'checkbox') {
                        $config[$fieldKey] = isset($_POST[$fieldKey]) ? '1' : '0';
                    } else {
                        $config[$fieldKey] = $_POST[$fieldKey];
                    }
                }
            }
        }
        
        // Save updated configuration
        Config::save($tenantId, $config);
        
        // Set success message
        $_SESSION['flash_message'] = 'Configuration saved successfully!';
        header('Location: ' . View::workspaceUrl('/settings/'));
        exit();
    }

    /**
     * Create a provider instance for this tenant
     */
    public function createProviderInstance(): void
    {
        AuthController::requireTenantAdmin();

        $tenantId = User::getTenantId();
        $type = $_POST['type'] ?? '';
        $provider = $_POST['provider'] ?? '';
        $name = $_POST['name'] ?? '';
        $config = $_POST['config'] ?? [];

        if (empty($type) || empty($provider) || empty($name)) {
            $_SESSION['flash_message'] = 'Type, provider and name are required for provider instance.';
            View::redirect(View::workspaceUrl('/settings/'));
            return;
        }

        // Validate provider belongs to type
        $providers = \App\Core\ProviderSettings::getProvidersMetadata();
        if (!isset($providers[$provider]) || ($providers[$provider]['type'] ?? '') !== $type) {
            $_SESSION['flash_message'] = 'Provider does not match selected type.';
            View::redirect(View::workspaceUrl('/settings/'));
            return;
        }

        // Validate and process configuration fields
        $providerFields = \App\Core\ProviderSettings::getFields($provider);
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
            $_SESSION['flash_message'] = 'Validation failed: ' . implode(' ', $validationErrors);
            View::redirect(View::workspaceUrl('/settings'));
            return;
        }

        \App\Models\ProviderInstance::create($tenantId, [
            'type' => $type,
            'provider' => $provider,
            'name' => $name,
            'settings' => $processedSettings
        ]);

        $_SESSION['flash_message'] = 'Provider instance created successfully.';
        View::redirect(View::workspaceUrl('/settings'));
    }

    public function deleteProviderInstance(): void
    {
        AuthController::requireTenantAdmin();

        $tenantId = User::getTenantId();
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            \App\Models\ProviderInstance::delete($tenantId, $id);
            $_SESSION['flash_message'] = 'Provider instance removed.';
        }
        View::redirect(View::workspaceUrl('/settings'));
    }

    /**
     * Get configuration for enabled providers
     */
    private function getEnabledProvidersConfig(string $tenantId, array $config): array
    {
        $enabled = [];
        $allFields = \App\Core\ProviderSettings::getAllFields();

        foreach (\App\Core\ProviderSettings::getProvidersMetadata() as $provider => $metadata) {
            $fields = $allFields[$provider] ?? [];
            $hasConfig = false;

            // Check if any field for this provider has a value
            foreach ($fields as $fieldKey => $field) {
                if (!empty($config[$fieldKey])) {
                    $hasConfig = true;
                    break;
                }
            }

            if ($hasConfig) {
                $enabled[$provider] = [
                    'metadata' => $metadata,
                    'fields' => $fields,
                    'values' => $this->getProviderValues($provider, $config, $fields)
                ];
            }
        }

        return $enabled;
    }

    /**
     * Get current values for provider fields
     */
    private function getProviderValues(string $provider, array $config, array $fields): array
    {
        $values = [];
        foreach ($fields as $fieldKey => $field) {
            $values[$fieldKey] = $config[$fieldKey] ?? ($field['value'] ?? '');
        }
        return $values;
    }
}
