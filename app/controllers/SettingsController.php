<?php

namespace App\Controllers;

use App\Models\{User, Config, Tenant, ProviderInstance};
use App\Core\{View, Icon, ProviderSettings, ProviderType, ProviderFormRenderer};

/**
 * Settings Controller
 * Manages general workspace settings. Provider management is delegated to individual controllers.
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
        $messagingChannels = $this->getMessagingChannels($tenantId, $config);

        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        View::render('settings', [
            'tenant' => $tenant,
            'user' => $user,
            'config' => $config,
            'messagingChannels' => $messagingChannels,
            'message' => $message,
            'activeTab' => 'settings'
        ]);
    }

    public function save(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $config = Config::get($tenantId);
        
        // Process messaging channels configuration
        $messagingChannels = ['email', 'telegram', 'whatsapp', 'slack', 'teams'];
        foreach ($messagingChannels as $channel) {
            $config['messaging_' . $channel . '_enabled'] = isset($_POST['messaging_' . $channel . '_enabled']) ? '1' : '0';
        }
        
        // Save updated configuration
        Config::save($tenantId, $config);
        
        // Set success message
        $_SESSION['flash_message'] = 'Messaging channels updated successfully!';
        header('Location: ' . View::workspaceUrl('/settings/'));
        exit();
    }

    /**
     * API endpoint to create a provider instance
     */
    public function createProvider(): void
    {
        AuthController::requireTenantAdmin();
        
        header('Content-Type: application/json');
        
        $tenantId = User::getTenantId();
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        
        $result = ProviderFormRenderer::createInstance($tenantId, [
            'type' => $input['type'] ?? '',
            'provider' => $input['provider'] ?? '',
            'name' => $input['name'] ?? '',
            'config' => $input['config'] ?? []
        ]);
        
        echo json_encode($result);
        exit();
    }

    /**
     * API endpoint to test provider connection
     */
    public function testConnection(): void
    {
        AuthController::requireTenantAdmin();
        
        header('Content-Type: application/json');
        
        $tenantId = User::getTenantId();
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        
        $result = ProviderFormRenderer::testConnection($tenantId, $input['provider'] ?? '', $input['config'] ?? []);
        
        echo json_encode($result);
        exit();
    }

    /**
     * Get messaging channels configuration for tenant
     */
    private function getMessagingChannels(string $tenantId, array $config): array
    {
        $channels = [
            'email' => ['name' => 'Email', 'icon' => 'mail'],
            'telegram' => ['name' => 'Telegram', 'icon' => 'message-circle'],
            'whatsapp' => ['name' => 'WhatsApp', 'icon' => 'phone'],
            'slack' => ['name' => 'Slack', 'icon' => 'hash'],
            'teams' => ['name' => 'Microsoft Teams', 'icon' => 'users']
        ];

        // Check which channels have supporting provider instances
        $providerInstances = ProviderInstance::getAll($tenantId);
        $supportedTypes = ['messenger', 'email'];
        $hasProviders = [];
        foreach ($providerInstances as $instance) {
            if (in_array($instance['type'], $supportedTypes)) {
                $provider = $instance['provider'];
                if (strpos($provider, 'telegram') !== false) $hasProviders['telegram'] = true;
                if (strpos($provider, 'whatsapp') !== false) $hasProviders['whatsapp'] = true;
                if (strpos($provider, 'slack') !== false) $hasProviders['slack'] = true;
                if (strpos($provider, 'teams') !== false) $hasProviders['teams'] = true;
                if (in_array($provider, ['mailcow', 'exchange', 'imap'])) $hasProviders['email'] = true;
            }
        }

        foreach ($channels as $key => &$channel) {
            $channel['enabled'] = ($config['messaging_' . $key . '_enabled'] ?? '0') === '1';
            $channel['hasProvider'] = $hasProviders[$key] ?? false;
        }

        return $channels;
    }
}

