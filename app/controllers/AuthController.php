<?php

namespace App\Controllers;

use App\Models\{User, Tenant};
use App\Core\View;

/**
 * Authentication Controller
 */
class AuthController
{
    public function login(): void
    {
        if (User::isLoggedIn()) {
            if (User::isSystemAdmin()) {
                View::redirect('/admin/');
            } else {
                $tenantId = User::getTenantId();
                if ($tenantId) {
                    View::redirect('/workspace/' . $tenantId . '/dashboard/');
                } else {
                    View::redirect('/admin/');
                }
            }
            return;
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        View::renderWithoutLayout('login', ['error' => $error]);
    }

    public function authenticate(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = User::authenticate($email, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            
            if ($user['role'] === User::ROLE_SYSTEM_ADMIN) {
                View::redirect('/admin/');
            } else {
                $tenantId = $user['tenant_id'];
                if ($tenantId) {
                    View::redirect('/workspace/' . $tenantId . '/dashboard/');
                } else {
                    View::redirect('/admin/');
                }
            }
        } else {
            $_SESSION['login_error'] = 'Invalid email or password.';
            View::redirect('/login/');
        }
    }

    public static function requireTenantAdmin(): void
    {
        if (!User::isLoggedIn()) {
            View::redirect('/login/');
            exit;
        }
        
        $user = User::getCurrentUser();
        if (!$user) {
            View::redirect('/login/');
            exit;
        }
        
        // For workspace routes, check workspace access
        if (isset($_SESSION['workspace_tenant_id'])) {
            if (!User::canAccessWorkspace($_SESSION['workspace_tenant_id'])) {
                http_response_code(403);
                echo '<h1>403 - Access Denied</h1><p>You do not have permission to access this workspace.</p>';
                exit;
            }
            return;
        }
        
        // For regular routes, require tenant admin or system admin
        if (!User::isTenantAdmin() && !User::isSystemAdmin()) {
            View::redirect('/login/');
            exit;
        }
    }

    public function logout(): void
    {
        session_destroy();
        View::redirect('/login/');
    }

    public static function requireAuth(): void
    {
        if (!User::isLoggedIn()) {
            View::redirect('/login/');
            exit;
        }
    }

    public static function requireSystemAdmin(): void
    {
        self::requireAuth();
        
        if (!User::isSystemAdmin()) {
            View::redirect('/login/');
            exit;
        }
    }
}
