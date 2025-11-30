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
            if (strtolower($user['email']) === strtolower($email) && $user['password_hash'] === $password) {
                return $user;
            }
        }
        
        return null;
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
