<?php

namespace App\Controllers;

use App\Models\{User, Employee, ProviderInstance};
use App\Core\{View, ProviderType, ProviderFactory};

/**
 * Identity Controller
 * Manages IAM provider integrations (Keycloak, Okta, Azure AD, etc.)
 */
class IdentityController
{
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = \App\Models\Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        // Get all IAM provider instances
        $allInstances = ProviderInstance::getAll($tenantId);
        $iamInstances = array_filter($allInstances, function($instance) {
            return ProviderType::getAssetType($instance['provider']) === ProviderType::TYPE_IAM;
        });
        
        // Get employees with their IAM accounts
        $employees = Employee::getAll($tenantId);
        
        // Build a list of IAM accounts per provider instance
        $iamAccounts = [];
        foreach ($iamInstances as $instance) {
            $iamAccounts[$instance['id']] = [
                'instance' => $instance,
                'employees' => []
            ];
            
            foreach ($employees as $emp) {
                $accounts = $emp['accounts'] ?? [];
                if (isset($accounts[$instance['id']]) && !empty($accounts[$instance['id']])) {
                    $iamAccounts[$instance['id']]['employees'][] = [
                        'employee' => $emp,
                        'username' => $accounts[$instance['id']]
                    ];
                }
            }
        }
        
        // Get selected provider instance for detail view
        $selectedInstanceId = $_GET['instance'] ?? null;
        $selectedInstance = null;
        $iamUsers = [];
        $iamGroups = [];
        $syncStatus = null;
        
        if ($selectedInstanceId) {
            $selectedInstance = ProviderInstance::find($tenantId, $selectedInstanceId);
            
            if ($selectedInstance) {
                try {
                    $provider = ProviderFactory::create($selectedInstance['provider'], $selectedInstance['settings'] ?? []);
                    
                    if (method_exists($provider, 'listUsers')) {
                        $iamUsers = $provider->listUsers();
                    }
                    
                    if (method_exists($provider, 'listGroups')) {
                        $iamGroups = $provider->listGroups();
                    }
                    
                    if (method_exists($provider, 'getSyncStatus')) {
                        $syncStatus = $provider->getSyncStatus();
                    }
                } catch (\Exception $e) {
                    $iamUsers = [];
                    $iamGroups = [];
                }
            }
        }
        
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        
        View::render('identity', [
            'tenant' => $tenant,
            'user' => $user,
            'iamInstances' => array_values($iamInstances),
            'iamAccounts' => $iamAccounts,
            'employees' => $employees,
            'selectedInstance' => $selectedInstance,
            'iamUsers' => $iamUsers,
            'iamGroups' => $iamGroups,
            'syncStatus' => $syncStatus,
            'message' => $message,
            'activeTab' => 'identity'
        ]);
    }

    /**
     * Assign an employee to an IAM provider
     */
    public function assignEmployee(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $instanceId = $_POST['instance_id'] ?? '';
        $employeeId = $_POST['employee_id'] ?? '';
        $username = $_POST['username'] ?? '';
        
        if (empty($instanceId) || empty($employeeId) || empty($username)) {
            $_SESSION['flash_message'] = 'Missing required fields.';
            View::redirect(View::workspaceUrl('/identity'));
            return;
        }
        
        $employee = Employee::find($tenantId, $employeeId);
        if (!$employee) {
            $_SESSION['flash_message'] = 'Employee not found.';
            View::redirect(View::workspaceUrl('/identity'));
            return;
        }
        
        $accounts = $employee['accounts'] ?? [];
        $accounts[$instanceId] = $username;
        
        Employee::update($tenantId, $employeeId, ['accounts' => $accounts]);
        
        $_SESSION['flash_message'] = 'Employee linked to identity provider.';
        View::redirect(View::workspaceUrl('/identity?instance=' . urlencode($instanceId)));
    }

    /**
     * Remove an employee from an IAM provider
     */
    public function unassignEmployee(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $instanceId = $_POST['instance_id'] ?? '';
        $employeeId = $_POST['employee_id'] ?? '';
        
        if (empty($instanceId) || empty($employeeId)) {
            $_SESSION['flash_message'] = 'Missing required fields.';
            View::redirect(View::workspaceUrl('/identity'));
            return;
        }
        
        $employee = Employee::find($tenantId, $employeeId);
        if (!$employee) {
            $_SESSION['flash_message'] = 'Employee not found.';
            View::redirect(View::workspaceUrl('/identity'));
            return;
        }
        
        $accounts = $employee['accounts'] ?? [];
        unset($accounts[$instanceId]);
        
        Employee::update($tenantId, $employeeId, ['accounts' => $accounts]);
        
        $_SESSION['flash_message'] = 'Employee unlinked from identity provider.';
        View::redirect(View::workspaceUrl('/identity?instance=' . urlencode($instanceId)));
    }

    /**
     * Trigger user sync from IAM provider
     */
    public function syncUsers(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $instanceId = $_POST['instance_id'] ?? '';
        
        if (empty($instanceId)) {
            $_SESSION['flash_message'] = 'Provider instance required.';
            View::redirect(View::workspaceUrl('/identity'));
            return;
        }
        
        $instance = ProviderInstance::find($tenantId, $instanceId);
        if (!$instance) {
            $_SESSION['flash_message'] = 'Provider instance not found.';
            View::redirect(View::workspaceUrl('/identity'));
            return;
        }
        
        // Create a job for async user sync
        \App\Models\Job::create($tenantId, [
            'service' => 'iam',
            'action' => 'sync_users',
            'target_name' => $instance['name'],
            'metadata' => [
                'provider_instance_id' => $instanceId,
                'provider' => $instance['provider']
            ]
        ]);
        
        $_SESSION['flash_message'] = 'User sync job queued.';
        View::redirect(View::workspaceUrl('/identity?instance=' . urlencode($instanceId)));
    }

    /**
     * Provision a new user in the IAM provider
     */
    public function provisionUser(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $instanceId = $_POST['instance_id'] ?? '';
        $employeeId = $_POST['employee_id'] ?? '';
        
        if (empty($instanceId) || empty($employeeId)) {
            View::json(['success' => false, 'error' => 'Missing required fields']);
            return;
        }
        
        $instance = ProviderInstance::find($tenantId, $instanceId);
        $employee = Employee::find($tenantId, $employeeId);
        
        if (!$instance || !$employee) {
            View::json(['success' => false, 'error' => 'Instance or employee not found']);
            return;
        }
        
        try {
            $provider = ProviderFactory::create($instance['provider'], $instance['settings'] ?? []);
            
            if (method_exists($provider, 'createUser')) {
                $result = $provider->createUser([
                    'username' => strtolower(str_replace(' ', '.', $employee['full_name'])),
                    'full_name' => $employee['full_name'],
                    'employee_id' => $employeeId
                ]);
                
                // Link the employee to the new IAM account
                $accounts = $employee['accounts'] ?? [];
                $accounts[$instanceId] = $result['username'] ?? $result['id'];
                Employee::update($tenantId, $employeeId, ['accounts' => $accounts]);
                
                View::json(['success' => true, 'result' => $result]);
            } else {
                View::json(['success' => false, 'error' => 'Provider does not support user provisioning']);
            }
        } catch (\Exception $e) {
            View::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
