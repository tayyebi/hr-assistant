<?php
/**
 * User Model
 */
class User
{
    const ROLE_SYSTEM_ADMIN = 'system_admin';
    const ROLE_TENANT_ADMIN = 'tenant_admin';

    public static function authenticate(string $email, string $password): ?array
    {
        $users = ExcelStorage::readSheet('system.xlsx', 'users');
        
        foreach ($users as $user) {
            if (strtolower($user['email']) === strtolower($email)) {
                // Support both legacy plain text (for demo) and hashed passwords
                $storedPassword = $user['password_hash'];
                
                // Check if it's a bcrypt hash (starts with $2)
                if (strpos($storedPassword, '$2') === 0) {
                    if (password_verify($password, $storedPassword)) {
                        return $user;
                    }
                } else {
                    // Legacy plain text comparison (only for demo/migration)
                    // In production, this should be removed after migration
                    if ($storedPassword === $password) {
                        return $user;
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * Hash a password for storage
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function find(string $id): ?array
    {
        $users = ExcelStorage::readSheet('system.xlsx', 'users');
        
        foreach ($users as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        
        return null;
    }

    public static function getAll(): array
    {
        return ExcelStorage::readSheet('system.xlsx', 'users');
    }

    public static function create(array $data): void
    {
        // Ensure password is hashed
        if (isset($data['password']) && strpos($data['password'], '$2') !== 0) {
            $data['password_hash'] = self::hashPassword($data['password']);
            unset($data['password']);
        }
        
        $headers = ['id', 'email', 'password_hash', 'role', 'tenant_id'];
        ExcelStorage::appendRow('system.xlsx', 'users', $data, $headers);
    }

    public static function getCurrentUser(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        return self::find($_SESSION['user_id']);
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function isSystemAdmin(): bool
    {
        $user = self::getCurrentUser();
        return $user && $user['role'] === self::ROLE_SYSTEM_ADMIN;
    }

    public static function isTenantAdmin(): bool
    {
        $user = self::getCurrentUser();
        return $user && $user['role'] === self::ROLE_TENANT_ADMIN;
    }

    public static function getTenantId(): ?string
    {
        $user = self::getCurrentUser();
        return $user ? ($user['tenant_id'] ?: null) : null;
    }
}
