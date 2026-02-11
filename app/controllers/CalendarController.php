<?php
namespace App\Controllers;

use App\Core\{View, ProviderType, ProviderSettings, ProviderFactory, ProviderFormRenderer};
use App\Models\{ProviderInstance, User, Tenant};

class CalendarController
{
    public function index()
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        if (!$tenantId) {
            View::redirect('/');
            return;
        }
        
        $tenant = Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        $providers = ProviderType::getByType(ProviderType::TYPE_CALENDAR);
        $providerInstances = ProviderInstance::getByType($tenantId, ProviderType::TYPE_CALENDAR);
        $providerMetadata = ProviderSettings::getProvidersMetadata();
        
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        
        View::render('calendars', [
            'tenant' => $tenant,
            'user' => $user,
            'providers' => $providers,
            'providerInstances' => $providerInstances,
            'providerMetadata' => $providerMetadata,
            'message' => $message,
            'activeTab' => 'calendars'
        ]);
    }

    /**
     * Create a new calendar provider instance
     */
    public function createProvider(): void
    {
        AuthController::requireTenantAdmin();

        $tenantId = User::getTenantId();
        $result = ProviderFormRenderer::createInstance($tenantId, [
            'type' => $_POST['type'] ?? ProviderType::TYPE_CALENDAR,
            'provider' => $_POST['provider'] ?? '',
            'name' => $_POST['name'] ?? '',
            'config' => $_POST['config'] ?? []
        ]);

        if (!$result['success']) {
            $_SESSION['flash_message'] = $result['message'];
        } else {
            $_SESSION['flash_message'] = $result['message'];
        }
        View::redirect(View::workspaceUrl('/calendars'));
    }

    /**
     * Delete a calendar provider instance
     */
    public function deleteProvider(): void
    {
        AuthController::requireTenantAdmin();

        $tenantId = User::getTenantId();
        $id = $_POST['id'] ?? '';

        if (!empty($id)) {
            $result = ProviderFormRenderer::deleteInstance($tenantId, $id);
            $_SESSION['flash_message'] = $result['message'];
        }
        
        View::redirect(View::workspaceUrl('/calendars'));
    }

    /**
     * Test calendar provider connection (AJAX endpoint)
     */
    public function testConnection(): void
    {
        AuthController::requireTenantAdmin();
        header('Content-Type: application/json');

        $tenantId = User::getTenantId();
        $provider = $_POST['provider'] ?? '';
        $config = $_POST['config'] ?? [];

        $result = ProviderFormRenderer::testConnection($tenantId, $provider, $config);
        echo json_encode($result);
        exit;
    }
}

