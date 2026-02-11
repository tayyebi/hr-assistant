<?php

namespace App\Controllers;

use App\Models\{User, Employee, ProviderInstance};
use App\Core\{View, ProviderType, ProviderFactory};

/**
 * Repository Controller
 * Manages Git repository access levels, activity, and commits
 */
class RepositoryController
{
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = \App\Models\Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        // Get all git provider instances
        $allInstances = ProviderInstance::getAll($tenantId);
        $gitInstances = array_filter($allInstances, function($instance) {
            return ProviderType::getAssetType($instance['provider']) === ProviderType::TYPE_GIT;
        });
        
        // Get employees with their git accounts
        $employees = Employee::getAll($tenantId);
        
        // Build a list of git accounts per provider instance
        $gitAccounts = [];
        foreach ($gitInstances as $instance) {
            $gitAccounts[$instance['id']] = [
                'instance' => $instance,
                'employees' => []
            ];
            
            foreach ($employees as $emp) {
                $accounts = $emp['accounts'] ?? [];
                if (isset($accounts[$instance['id']]) && !empty($accounts[$instance['id']])) {
                    $gitAccounts[$instance['id']]['employees'][] = [
                        'employee' => $emp,
                        'username' => $accounts[$instance['id']]
                    ];
                }
            }
        }
        
        // Get selected provider instance for detail view
        $selectedInstanceId = $_GET['instance'] ?? null;
        $selectedInstance = null;
        $repositories = [];
        $recentActivity = [];
        
        if ($selectedInstanceId) {
            $selectedInstance = ProviderInstance::find($tenantId, $selectedInstanceId);
            
            if ($selectedInstance) {
                // Try to fetch repositories from the provider
                try {
                    $provider = ProviderFactory::create($selectedInstance['provider'], $selectedInstance['settings'] ?? []);
                    
                    if (method_exists($provider, 'listRepositories')) {
                        $repositories = $provider->listRepositories();
                    }
                    
                    if (method_exists($provider, 'getRecentActivity')) {
                        $recentActivity = $provider->getRecentActivity();
                    }
                } catch (\Exception $e) {
                    // Provider not fully implemented or connection error
                    $repositories = [];
                    $recentActivity = [];
                }
            }
        }
        
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        
        View::render('repositories', [
            'tenant' => $tenant,
            'user' => $user,
            'gitInstances' => array_values($gitInstances),
            'gitAccounts' => $gitAccounts,
            'employees' => $employees,
            'selectedInstance' => $selectedInstance,
            'repositories' => $repositories,
            'recentActivity' => $recentActivity,
            'message' => $message,
            'activeTab' => 'repositories'
        ]);
    }

    /**
     * Get repository access levels (API endpoint)
     */
    public function getAccess(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $instanceId = $_GET['instance_id'] ?? '';
        $repoPath = $_GET['repo'] ?? '';
        
        if (empty($instanceId) || empty($repoPath)) {
            View::json(['success' => false, 'error' => 'Missing required parameters']);
            return;
        }
        
        $instance = ProviderInstance::find($tenantId, $instanceId);
        if (!$instance) {
            View::json(['success' => false, 'error' => 'Provider instance not found']);
            return;
        }
        
        try {
            $provider = ProviderFactory::create($instance['provider'], $instance['settings'] ?? []);
            
            if (method_exists($provider, 'getRepositoryAccess')) {
                $access = $provider->getRepositoryAccess($repoPath);
                View::json(['success' => true, 'access' => $access]);
            } else {
                View::json(['success' => false, 'error' => 'Provider does not support access management']);
            }
        } catch (\Exception $e) {
            View::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Set repository access level for an employee
     */
    public function setAccess(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $instanceId = $_POST['instance_id'] ?? '';
        $repoPath = $_POST['repo'] ?? '';
        $username = $_POST['username'] ?? '';
        $accessLevel = $_POST['access_level'] ?? '';
        
        if (empty($instanceId) || empty($repoPath) || empty($username)) {
            View::json(['success' => false, 'error' => 'Missing required parameters']);
            return;
        }
        
        $instance = ProviderInstance::find($tenantId, $instanceId);
        if (!$instance) {
            View::json(['success' => false, 'error' => 'Provider instance not found']);
            return;
        }
        
        try {
            $provider = ProviderFactory::create($instance['provider'], $instance['settings'] ?? []);
            
            if (method_exists($provider, 'setRepositoryAccess')) {
                $result = $provider->setRepositoryAccess($repoPath, $username, $accessLevel);
                View::json(['success' => true, 'result' => $result]);
            } else {
                View::json(['success' => false, 'error' => 'Provider does not support access management']);
            }
        } catch (\Exception $e) {
            View::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get repository commits (API endpoint)
     */
    public function getCommits(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $instanceId = $_GET['instance_id'] ?? '';
        $repoPath = $_GET['repo'] ?? '';
        $limit = (int)($_GET['limit'] ?? 20);
        
        if (empty($instanceId) || empty($repoPath)) {
            View::json(['success' => false, 'error' => 'Missing required parameters']);
            return;
        }
        
        $instance = ProviderInstance::find($tenantId, $instanceId);
        if (!$instance) {
            View::json(['success' => false, 'error' => 'Provider instance not found']);
            return;
        }
        
        try {
            $provider = ProviderFactory::create($instance['provider'], $instance['settings'] ?? []);
            
            if (method_exists($provider, 'getCommits')) {
                $commits = $provider->getCommits($repoPath, $limit);
                View::json(['success' => true, 'commits' => $commits]);
            } else {
                View::json(['success' => false, 'error' => 'Provider does not support commit retrieval']);
            }
        } catch (\Exception $e) {
            View::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
