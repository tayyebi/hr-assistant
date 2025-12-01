<?php
/**
 * Employee Controller
 */
class EmployeeController
{
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        $employees = Employee::getAll($tenantId);
        $search = $_GET['search'] ?? '';
        
        if ($search) {
            $employees = array_filter($employees, function($emp) use ($search) {
                return stripos($emp['full_name'], $search) !== false 
                    || stripos($emp['email'], $search) !== false;
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
            'email' => $_POST['email'] ?? '',
            'telegram_chat_id' => $_POST['telegram_chat_id'] ?? '',
            'birthday' => $_POST['birthday'] ?? '',
            'hired_date' => $_POST['hired_date'] ?? date('Y-m-d'),
            'position' => $_POST['position'] ?? ''
        ]);
        
        $_SESSION['flash_message'] = 'Employee added successfully.';
        View::redirect('/employees');
    }

    public function update(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $id = $_POST['id'] ?? '';
        
        Employee::update($tenantId, $id, [
            'full_name' => $_POST['full_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'telegram_chat_id' => $_POST['telegram_chat_id'] ?? '',
            'birthday' => $_POST['birthday'] ?? '',
            'hired_date' => $_POST['hired_date'] ?? '',
            'position' => $_POST['position'] ?? ''
        ]);
        
        $_SESSION['flash_message'] = 'Employee updated successfully.';
        View::redirect('/employees');
    }

    public function delete(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $id = $_POST['id'] ?? '';
        
        Employee::delete($tenantId, $id);
        
        $_SESSION['flash_message'] = 'Employee removed successfully.';
        View::redirect('/employees');
    }
}
