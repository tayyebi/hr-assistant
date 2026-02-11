<?php

namespace App\Controllers;

use App\Models\{User, Employee, Team};
use App\Core\View;

/**
 * Employee Controller
 */
class EmployeeController
{
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = \App\Models\Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        $employees = Employee::getAll($tenantId);
        $search = $_GET['search'] ?? '';
        
        if ($search) {
            $employees = array_filter($employees, function($emp) use ($search) {
                return stripos($emp['full_name'], $search) !== false;
            });
        }
        
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        
        View::render('employees', [
            'tenant' => $tenant,
            'user' => $user,
            'employees' => array_values($employees),
            'search' => $search,
            'message' => $message,
            'activeTab' => 'employees'
        ]);
    }

    public function store(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        
        Employee::create($tenantId, [
            'full_name' => $_POST['full_name'] ?? '',
            'birthday' => $_POST['birthday'] ?? '',
            'hired_date' => $_POST['hired_date'] ?? date('Y-m-d'),
            'position' => $_POST['position'] ?? ''
        ]);
        
        $_SESSION['flash_message'] = 'Employee added successfully.';
        View::redirect(View::workspaceUrl('/employees/'));
    }

    public function update(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $id = $_POST['id'] ?? '';
        
        Employee::update($tenantId, $id, [
            'full_name' => $_POST['full_name'] ?? '',
            'birthday' => $_POST['birthday'] ?? '',
            'hired_date' => $_POST['hired_date'] ?? '',
            'position' => $_POST['position'] ?? ''
        ]);
        
        $_SESSION['flash_message'] = 'Employee updated successfully.';
        View::redirect(View::workspaceUrl('/employees/'));
    }

    public function delete(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $id = $_POST['id'] ?? '';
        
        Employee::delete($tenantId, $id);
        
        $_SESSION['flash_message'] = 'Employee removed successfully.';
        View::redirect(View::workspaceUrl('/employees/'));
    }
}
