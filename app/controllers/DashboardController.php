<?php

namespace HRAssistant\Controllers;

use HRAssistant\Models\{User, Employee, Asset, Message, Job};
use HRAssistant\Core\View;

/**
 * Dashboard Controller
 */
class DashboardController
{
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        $employees = Employee::getAll($tenantId);
        $sentimentStats = Employee::getSentimentStats($tenantId);
        $upcomingBirthdays = Employee::getUpcomingBirthdays($tenantId, 30);
        
        View::render('dashboard', [
            'tenant' => $tenant,
            'user' => $user,
            'employees' => $employees,
            'sentimentStats' => $sentimentStats,
            'upcomingBirthdays' => $upcomingBirthdays,
            'activeTab' => 'dashboard'
        ]);
    }
}
