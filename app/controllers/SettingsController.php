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
     * Create a provider instance for this tenant
     */
    public function createProviderInstance(): void
    {
        AuthController::requireTenantAdmin();

        $tenantId = User::getTenantId();
        $type = $_POST['type'] ?? '';
        $provider = $_POST['provider'] ?? '';
        $name = $_POST['name'] ?? '';
        $settings = $_POST['settings'] ?? '';

        if (empty($type) || empty($provider) || empty($name)) {
            $_SESSION['flash_message'] = 'Type, provider and name are required for provider instance.';
            View::redirect(View::workspaceUrl('/settings'));
            return;
        }

        // Validate provider belongs to type
        $providers = ProviderSettings::getProvidersMetadata();
        if (!isset($providers[$provider]) || ($providers[$provider]['type'] ?? '') !== $type) {
            $_SESSION['flash_message'] = 'Provider does not match selected type.';
            View::redirect(View::workspaceUrl('/settings'));
            return;
        }

        $parsedSettings = [];
        if (!empty($settings)) {
            // Expect JSON string in settings
            $decoded = json_decode($settings, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $_SESSION['flash_message'] = 'Settings must be valid JSON.';
                View::redirect(View::workspaceUrl('/settings'));
                return;
            }
            $parsedSettings = is_array($decoded) ? $decoded : [];
        }

        ProviderInstance::create($tenantId, [
            'type' => $type,
            'provider' => $provider,
            'name' => $name,
            'settings' => $parsedSettings
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
            ProviderInstance::delete($tenantId, $id);
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
