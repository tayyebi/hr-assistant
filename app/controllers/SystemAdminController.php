<?php
/**
 * System Admin Controller
 */
class SystemAdminController
{
    public function index(): void
    {
        AuthController::requireSystemAdmin();
        
        $user = User::getCurrentUser();
        $tenants = Tenant::getAll();
        
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        
        View::renderWithoutLayout('admin', [
            'user' => $user,
            'tenants' => $tenants,
            'message' => $message
        ]);
    }

    public function createTenant(): void
    {
        AuthController::requireSystemAdmin();
        
        $name = $_POST['name'] ?? '';
        
        if ($name) {
            Tenant::create($name);
            $_SESSION['flash_message'] = 'Tenant created successfully.';
        } else {
            $_SESSION['flash_message'] = 'Tenant name is required.';
        }
        
        View::redirect('/admin');
    }
}
