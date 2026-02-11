<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\ProviderType;
use App\Core\ProviderSettings;
use App\Core\ProviderFactory;
use App\Models\{ProviderInstance, User};

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
        
        $providers = ProviderType::getByType(ProviderType::TYPE_CALENDAR);
        $providerInstances = ProviderInstance::getByType($tenantId, ProviderType::TYPE_CALENDAR);
        $providerMetadata = ProviderSettings::getProvidersMetadata();
        View::render('pages/calendars', [
            'providers' => $providers,
            'providerInstances' => $providerInstances,
            'providerMetadata' => $providerMetadata,
        ]);
    }
    // Add create, update, delete actions as needed, similar to other provider controllers
}
