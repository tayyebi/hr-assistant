<?php
/**
 * Asset Controller
 */
class AssetController
{
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        $employees = Employee::getAll($tenantId);
        $config = Config::get($tenantId);
        
        $activeService = $_GET['service'] ?? 'mailcow';
        
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        
        View::render('assets', [
            'tenant' => $tenant,
            'user' => $user,
            'employees' => $employees,
            'config' => $config,
            'activeService' => $activeService,
            'message' => $message,
            'activeTab' => 'assets'
        ]);
    }

    public function provision(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $config = Config::get($tenantId);
        
        $service = $_POST['service'] ?? '';
        $action = $_POST['action'] ?? '';
        $targetName = $_POST['target_name'] ?? '';
        $metadata = $_POST['metadata'] ?? '';
        
        Job::create($tenantId, [
            'service' => $service,
            'action' => $action,
            'target_name' => $targetName,
            'metadata' => $metadata ? json_decode($metadata, true) : []
        ]);
        
        $_SESSION['flash_message'] = "Task \"{$action}\" for {$targetName} queued successfully.";
        View::redirect('/assets?service=' . urlencode($service));
    }
}
