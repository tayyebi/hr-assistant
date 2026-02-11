<?php

namespace App\Controllers;

use App\Models\{User, Tenant};
use App\Core\View;

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
        $messageType = $_SESSION['flash_message_type'] ?? 'success';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_message_type']);
        
        View::renderWithoutLayout('admin', [
            'user' => $user,
            'tenants' => $tenants,
            'message' => $message,
            'messageType' => $messageType
        ]);
    }

    public function createTenant(): void
    {
        AuthController::requireSystemAdmin();
        
        $name = $_POST['name'] ?? '';
        
        if ($name) {
            Tenant::create($name);
            $_SESSION['flash_message'] = 'Tenant created successfully.';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Tenant name is required.';
            $_SESSION['flash_message_type'] = 'error';
        }
        
        View::redirect('/admin');
    }

    public function editTenant(): void
    {
        AuthController::requireSystemAdmin();
        
        $id = $_POST['id'] ?? '';
        $name = $_POST['name'] ?? '';
        
        if (!$id) {
            $_SESSION['flash_message'] = 'Tenant ID is required.';
            $_SESSION['flash_message_type'] = 'error';
            View::redirect('/admin');
            return;
        }

        $tenant = Tenant::find($id);
        if (!$tenant) {
            $_SESSION['flash_message'] = 'Tenant not found.';
            $_SESSION['flash_message_type'] = 'error';
            View::redirect('/admin');
            return;
        }
        
        if ($name) {
            Tenant::update($id, ['name' => $name]);
            $_SESSION['flash_message'] = 'Tenant updated successfully.';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Tenant name is required.';
            $_SESSION['flash_message_type'] = 'error';
        }
        
        View::redirect('/admin');
    }

    public function deactivateTenant(): void
    {
        AuthController::requireSystemAdmin();
        
        $id = $_POST['id'] ?? '';
        
        if (!$id) {
            $_SESSION['flash_message'] = 'Tenant ID is required.';
            $_SESSION['flash_message_type'] = 'error';
            View::redirect('/admin');
            return;
        }

        $tenant = Tenant::find($id);
        if (!$tenant) {
            $_SESSION['flash_message'] = 'Tenant not found.';
            $_SESSION['flash_message_type'] = 'error';
            View::redirect('/admin');
            return;
        }
        
        Tenant::deactivate($id);
        $_SESSION['flash_message'] = 'Tenant deactivated successfully.';
        $_SESSION['flash_message_type'] = 'success';
        
        View::redirect('/admin');
    }

    public function activateTenant(): void
    {
        AuthController::requireSystemAdmin();
        
        $id = $_POST['id'] ?? '';
        
        if (!$id) {
            $_SESSION['flash_message'] = 'Tenant ID is required.';
            $_SESSION['flash_message_type'] = 'error';
            View::redirect('/admin');
            return;
        }

        $tenant = Tenant::find($id);
        if (!$tenant) {
            $_SESSION['flash_message'] = 'Tenant not found.';
            $_SESSION['flash_message_type'] = 'error';
            View::redirect('/admin');
            return;
        }
        
        Tenant::activate($id);
        $_SESSION['flash_message'] = 'Tenant activated successfully.';
        $_SESSION['flash_message_type'] = 'success';
        
        View::redirect('/admin');
    }

    public function deleteTenant(): void
    {
        AuthController::requireSystemAdmin();
        
        $id = $_POST['id'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        
        if (!$id) {
            $_SESSION['flash_message'] = 'Tenant ID is required.';
            $_SESSION['flash_message_type'] = 'error';
            View::redirect('/admin');
            return;
        }

        $tenant = Tenant::find($id);
        if (!$tenant) {
            $_SESSION['flash_message'] = 'Tenant not found.';
            $_SESSION['flash_message_type'] = 'error';
            View::redirect('/admin');
            return;
        }

        if ($confirm !== 'DELETE') {
            $_SESSION['flash_message'] = 'Please type DELETE to confirm deletion.';
            $_SESSION['flash_message_type'] = 'error';
            View::redirect('/admin');
            return;
        }
        
        if (Tenant::delete($id)) {
            $_SESSION['flash_message'] = 'Tenant and all associated data deleted successfully.';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Failed to delete tenant. Please try again.';
            $_SESSION['flash_message_type'] = 'error';
        }
        
        View::redirect('/admin');
    }
}
