<?php

namespace App\Controllers;

use App\Models\{User, Team, Employee};
use App\Core\View;

/**
 * Team Controller
 */
class TeamController
{
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = \App\Models\Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        $teams = Team::getAll($tenantId);
        $employees = Employee::getAll($tenantId);
        
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        
        View::render('teams', [
            'tenant' => $tenant,
            'user' => $user,
            'teams' => $teams,
            'employees' => $employees,
            'message' => $message,
            'activeTab' => 'teams'
        ]);
    }

    public function store(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        
        Team::create($tenantId, [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? 'New team description'
        ]);
        
        $_SESSION['flash_message'] = 'Team created successfully.';
        View::redirect(View::workspaceUrl('/teams'));
    }

    public function update(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $teamId = $_POST['team_id'] ?? '';
        
        Team::update($tenantId, $teamId, [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? ''
        ]);
        
        $_SESSION['flash_message'] = 'Team updated successfully.';
        View::redirect(View::workspaceUrl('/teams'));
    }

    public function delete(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $teamId = $_POST['team_id'] ?? '';
        
        Team::delete($tenantId, $teamId);
        
        $_SESSION['flash_message'] = 'Team deleted successfully.';
        View::redirect(View::workspaceUrl('/teams'));
    }

    public function addMember(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $teamId = $_POST['team_id'] ?? '';
        $employeeId = $_POST['employee_id'] ?? '';
        
        Team::addMember($tenantId, $teamId, $employeeId);
        
        $_SESSION['flash_message'] = 'Member added to team.';
        View::redirect(View::workspaceUrl('/teams'));
    }

    public function removeMember(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $teamId = $_POST['team_id'] ?? '';
        $employeeId = $_POST['employee_id'] ?? '';
        
        Team::removeMember($tenantId, $teamId, $employeeId);
        
        $_SESSION['flash_message'] = 'Member removed from team.';
        View::redirect(View::workspaceUrl('/teams'));
    }

    public function addAlias(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $teamId = $_POST['team_id'] ?? '';
        $alias = $_POST['alias'] ?? '';
        
        if (filter_var($alias, FILTER_VALIDATE_EMAIL)) {
            Team::addAlias($tenantId, $teamId, $alias);
            $_SESSION['flash_message'] = 'Alias added (queued for creation).';
        } else {
            $_SESSION['flash_message'] = 'Invalid email format.';
        }
        
        View::redirect(View::workspaceUrl('/teams'));
    }

    public function removeAlias(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $teamId = $_POST['team_id'] ?? '';
        $alias = $_POST['alias'] ?? '';
        
        Team::removeAlias($tenantId, $teamId, $alias);
        
        $_SESSION['flash_message'] = 'Alias removed (queued for deletion).';
        View::redirect(View::workspaceUrl('/teams'));
    }
}
