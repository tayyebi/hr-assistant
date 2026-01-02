<?php
/**
 * Asset Controller
 * Handles asset discovery, assignment, and management across providers
 */
class AssetController
{
    /**
     * Display assets management page with available and assigned assets
     */
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        $employees = Employee::getAll($tenantId);
        $assetManager = new AssetManager($tenantId);
        
        // Get available assets grouped by type
        $availableAssets = $assetManager->getAvailableAssetsGrouped();
        
        // Get all assigned assets
        $assignedAssets = [];
        foreach ($employees as $employee) {
            $assignedAssets[$employee['id']] = $assetManager->getEmployeeAssets($employee['id']);
        }
        
        $message = $_SESSION['flash_message'] ?? null;
        $messageType = $_SESSION['flash_message_type'] ?? 'success';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_message_type']);
        
        View::render('assets', [
            'tenant' => $tenant,
            'user' => $user,
            'employees' => $employees,
            'availableAssets' => $availableAssets,
            'assignedAssets' => $assignedAssets,
            'message' => $message,
            'messageType' => $messageType,
            'activeTab' => 'assets'
        ]);
    }

    /**
     * API endpoint to get available assets from a specific provider
     * Called via AJAX to dynamically fetch assets
     */
    public function getProviderAssets(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $providerType = $_GET['provider'] ?? '';
        
        if (empty($providerType) || !ProviderType::isValid($providerType)) {
            View::json(['error' => 'Invalid provider', 'success' => false]);
            return;
        }
        
        $assetManager = new AssetManager($tenantId);
        $assets = $assetManager->getAssetsByProvider($providerType);
        
        View::json([
            'success' => true,
            'provider' => $providerType,
            'providerName' => ProviderType::getName($providerType),
            'assets' => $assets ?? [],
        ]);
    }

    /**
     * API endpoint to get assets by type (email, git, messenger, iam)
     */
    public function getAssetsByType(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $assetType = $_GET['type'] ?? '';
        
        $validTypes = [
            ProviderType::TYPE_EMAIL,
            ProviderType::TYPE_GIT,
            ProviderType::TYPE_MESSENGER,
            ProviderType::TYPE_IAM,
        ];
        
        if (!in_array($assetType, $validTypes)) {
            View::json(['error' => 'Invalid asset type', 'success' => false]);
            return;
        }
        
        $assetManager = new AssetManager($tenantId);
        $assets = $assetManager->getAssetsByType($assetType);
        
        View::json([
            'success' => true,
            'type' => $assetType,
            'assets' => $assets,
        ]);
    }

    /**
     * API endpoint to test provider connection
     */
    public function testConnection(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $providerType = $_POST['provider'] ?? '';
        
        if (empty($providerType) || !ProviderType::isValid($providerType)) {
            View::json(['error' => 'Invalid provider', 'success' => false]);
            return;
        }
        
        $assetManager = new AssetManager($tenantId);
        $result = $assetManager->testProviderConnection($providerType);
        
        View::json($result);
    }

    /**
     * API endpoint to assign an asset to an employee
     */
    public function assignAsset(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $employeeId = $_POST['employee_id'] ?? '';
        $providerType = $_POST['provider'] ?? '';
        $assetIdentifier = $_POST['asset_identifier'] ?? '';
        $assetType = $_POST['asset_type'] ?? '';
        
        if (empty($employeeId) || empty($providerType) || empty($assetIdentifier) || empty($assetType)) {
            View::json(['error' => 'Missing required fields', 'success' => false]);
            return;
        }
        
        // Verify employee exists in tenant
        $employee = Employee::find($tenantId, $employeeId);
        if (!$employee) {
            View::json(['error' => 'Employee not found', 'success' => false]);
            return;
        }
        
        if (!ProviderType::isValid($providerType)) {
            View::json(['error' => 'Invalid provider', 'success' => false]);
            return;
        }
        
        $assetManager = new AssetManager($tenantId);
        $success = $assetManager->assignAssetToEmployee(
            $employeeId,
            $providerType,
            $assetIdentifier,
            $assetType
        );
        
        if ($success) {
            View::json([
                'success' => true,
                'message' => 'Asset assigned successfully',
            ]);
        } else {
            View::json(['error' => 'Failed to assign asset', 'success' => false]);
        }
    }

    /**
     * API endpoint to unassign an asset from an employee
     */
    public function unassignAsset(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $assetId = $_POST['asset_id'] ?? '';
        
        if (empty($assetId)) {
            View::json(['error' => 'Missing asset ID', 'success' => false]);
            return;
        }
        
        $assetManager = new AssetManager($tenantId);
        $success = $assetManager->unassignAsset($assetId);
        
        if ($success) {
            View::json([
                'success' => true,
                'message' => 'Asset unassigned successfully',
            ]);
        } else {
            View::json(['error' => 'Failed to unassign asset', 'success' => false]);
        }
    }

    /**
     * Legacy endpoint for provisioning jobs (kept for backward compatibility)
     */
    public function provision(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
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
        $_SESSION['flash_message_type'] = 'success';
        View::redirect('/assets?service=' . urlencode($service));
    }
}
