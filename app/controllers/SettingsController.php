<?php
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
        $providers = ProviderSettings::getProvidersMetadata();
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
        $allFields = ProviderSettings::getAllFields();
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
        header('Location: /settings');
        exit();
    }

    /**
     * Get configuration for enabled providers
     */
    private function getEnabledProvidersConfig(string $tenantId, array $config): array
    {
        $enabled = [];
        $allFields = ProviderSettings::getAllFields();

        foreach (ProviderSettings::getProvidersMetadata() as $provider => $metadata) {
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
