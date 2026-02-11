<?php

namespace App\Controllers;

use App\Models\{User, Employee, ProviderInstance};
use App\Core\{View, ProviderType, ProviderFactory, SecretsProvider};

/**
 * Secrets Controller
 * Manages password manager integrations (Passbolt, Bitwarden, etc.)
 */
class SecretsController
{
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = \App\Models\Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        // Get all secrets provider instances
        $allInstances = ProviderInstance::getAll($tenantId);
        $secretsInstances = array_filter($allInstances, function($instance) {
            return ProviderType::getAssetType($instance['provider']) === ProviderType::TYPE_SECRETS;
        });
        
        // Get employees with their secrets accounts
        $employees = Employee::getAll($tenantId);
        
        // Build a list of secrets accounts per provider instance
        $secretsAccounts = [];
        foreach ($secretsInstances as $instance) {
            $secretsAccounts[$instance['id']] = [
                'instance' => $instance,
                'employees' => []
            ];
            
            foreach ($employees as $emp) {
                $accounts = $emp['accounts'] ?? [];
                if (isset($accounts[$instance['id']]) && !empty($accounts[$instance['id']])) {
                    $secretsAccounts[$instance['id']]['employees'][] = [
                        'employee' => $emp,
                        'username' => $accounts[$instance['id']]
                    ];
                }
            }
        }
        
        // Get selected provider instance for detail view
        $selectedInstanceId = $_GET['instance'] ?? null;
        $selectedInstance = null;
        $groups = [];
        $users = [];
        
        if ($selectedInstanceId) {
            $selectedInstance = ProviderInstance::find($tenantId, $selectedInstanceId);
            
            if ($selectedInstance) {
                try {
                    $provider = ProviderFactory::create($tenantId, $selectedInstance['provider'], $selectedInstance['settings'] ?? []);
                    
                    if (method_exists($provider, 'listGroups')) {
                        $groups = $provider->listGroups();
                    }
                    
                    if (method_exists($provider, 'listUsers')) {
                        $users = $provider->listUsers();
                    }
                } catch (\Exception $e) {
                    $groups = [];
                    $users = [];
                }
            }
        }
        
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        
        View::render('secrets', [
            'tenant' => $tenant,
            'user' => $user,
            'secretsInstances' => array_values($secretsInstances),
            'secretsAccounts' => $secretsAccounts,
            'employees' => $employees,
            'selectedInstance' => $selectedInstance,
            'groups' => $groups,
            'users' => $users,
            'message' => $message,
            'activeTab' => 'secrets'
        ]);
    }

    /**
     * Create a new secrets provider instance
     */
    public function createProvider(): void
    {
        AuthController::requireTenantAdmin();

        $tenantId = User::getTenantId();
        $result = \App\Core\ProviderFormRenderer::createInstance($tenantId, [
            'type' => $_POST['type'] ?? \App\Core\ProviderType::TYPE_SECRETS,
            'provider' => $_POST['provider'] ?? '',
            'name' => $_POST['name'] ?? '',
            'config' => $_POST['config'] ?? []
        ]);

        if (!$result['success']) {
            $_SESSION['flash_message'] = $result['message'];
        } else {
            $_SESSION['flash_message'] = $result['message'];
        }
        View::redirect(View::workspaceUrl('/secrets'));
    }

    /**
     * Delete a secrets provider instance
     */
    public function deleteProvider(): void
    {
        AuthController::requireTenantAdmin();

        $tenantId = User::getTenantId();
        $id = $_POST['id'] ?? '';

        if (!empty($id)) {
            $result = \App\Core\ProviderFormRenderer::deleteInstance($tenantId, $id);
            $_SESSION['flash_message'] = $result['message'];
        }
        
        View::redirect(View::workspaceUrl('/secrets'));
    }

    /**
     * Test secrets provider connection (AJAX endpoint)
     */
    public function testConnection(): void
    {
        AuthController::requireTenantAdmin();
        header('Content-Type: application/json');

        $tenantId = User::getTenantId();
        $provider = $_POST['provider'] ?? '';
        $config = $_POST['config'] ?? [];

        $result = \App\Core\ProviderFormRenderer::testConnection($tenantId, $provider, $config);
        echo json_encode($result);
        exit;
    }

    /**
     * Assign an employee to a secrets provider
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
            View::redirect(View::workspaceUrl('/secrets'));
            return;
        }
        
        // Update employee accounts
        $employee = Employee::find($tenantId, $employeeId);
        if (!$employee) {
            $_SESSION['flash_message'] = 'Employee not found.';
            View::redirect(View::workspaceUrl('/secrets'));
            return;
        }
        
        $accounts = $employee['accounts'] ?? [];
        $accounts[$instanceId] = $username;
        
        Employee::update($tenantId, $employeeId, ['accounts' => $accounts]);
        
        $_SESSION['flash_message'] = 'Employee linked to secrets provider.';
        View::redirect(View::workspaceUrl('/secrets?instance=' . urlencode($instanceId)));
    }

    /**
     * Remove an employee from a secrets provider
     */
    public function unassignEmployee(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $instanceId = $_POST['instance_id'] ?? '';
        $employeeId = $_POST['employee_id'] ?? '';
        
        if (empty($instanceId) || empty($employeeId)) {
            $_SESSION['flash_message'] = 'Missing required fields.';
            View::redirect(View::workspaceUrl('/secrets'));
            return;
        }
        
        $employee = Employee::find($tenantId, $employeeId);
        if (!$employee) {
            $_SESSION['flash_message'] = 'Employee not found.';
            View::redirect(View::workspaceUrl('/secrets'));
            return;
        }
        
        $accounts = $employee['accounts'] ?? [];
        unset($accounts[$instanceId]);
        
        Employee::update($tenantId, $employeeId, ['accounts' => $accounts]);
        
        $_SESSION['flash_message'] = 'Employee unlinked from secrets provider.';
        View::redirect(View::workspaceUrl('/secrets?instance=' . urlencode($instanceId)));
    }

    /**
     * Get user access info (API endpoint)
     */
    public function getUserAccess(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $instanceId = $_GET['instance_id'] ?? '';
        $username = $_GET['username'] ?? '';
        
        if (empty($instanceId) || empty($username)) {
            View::json(['success' => false, 'error' => 'Missing required parameters']);
            return;
        }
        
        $instance = ProviderInstance::find($tenantId, $instanceId);
        if (!$instance) {
            View::json(['success' => false, 'error' => 'Provider instance not found']);
            return;
        }
        
        try {
            $provider = ProviderFactory::create($tenantId, $instance['provider'], $instance['settings'] ?? []);
            
            if (method_exists($provider, 'getUserAccess')) {
                $access = $provider->getUserAccess($username);
                View::json(['success' => true, 'access' => $access]);
            } else {
                View::json(['success' => false, 'error' => 'Provider does not support access queries']);
            }
        } catch (\Exception $e) {
            View::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
