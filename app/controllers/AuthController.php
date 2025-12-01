<?php
/**
 * Authentication Controller
 */
class AuthController
{
    public function login(): void
    {
        if (User::isLoggedIn()) {
            if (User::isSystemAdmin()) {
                View::redirect('/admin');
            } else {
                View::redirect('/dashboard');
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
                View::redirect('/admin');
            } else {
                View::redirect('/dashboard');
            }
        } else {
            $_SESSION['login_error'] = 'Invalid email or password.';
            View::redirect('/login');
        }
    }

    public function logout(): void
    {
        session_destroy();
        View::redirect('/login');
    }

    public static function requireAuth(): void
    {
        if (!User::isLoggedIn()) {
            View::redirect('/login');
            exit;
        }
    }

    public static function requireTenantAdmin(): void
    {
        self::requireAuth();
        
        if (!User::isTenantAdmin()) {
            View::redirect('/login');
            exit;
        }
    }

    public static function requireSystemAdmin(): void
    {
        self::requireAuth();
        
        if (!User::isSystemAdmin()) {
            View::redirect('/login');
            exit;
        }
    }
}
