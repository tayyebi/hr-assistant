<?php

namespace HRAssistant\Models;

use HRAssistant\Core\Database;

/**
 * User Model
 */
class User
{
    const ROLE_SYSTEM_ADMIN = 'system_admin';
    const ROLE_TENANT_ADMIN = 'tenant_admin';

    public static function authenticate(string $email, string $password): ?array
    {
        try {
            $row = Database::fetchOne('SELECT * FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1', [$email]);
            if (!$row) return null;

            $storedPassword = $row['password_hash'] ?? '';

            if (strpos($storedPassword, '$2') === 0) {
                return password_verify($password, $storedPassword) ? $row : null;
            }

            return $storedPassword === $password ? $row : null;
        } catch (\Exception $e) {
            // Database error: authentication not available
            return null;
        }
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
        try {
            return Database::fetchOne('SELECT * FROM users WHERE id = ? LIMIT 1', [$id]);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getAll(): array
    {
        try {
            return Database::fetchAll('SELECT * FROM users');
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function create(array $data): void
    {
        // Ensure password is hashed
        if (isset($data['password']) && strpos($data['password'], '$2') !== 0) {
            $data['password_hash'] = self::hashPassword($data['password']);
            unset($data['password']);
        }
        $id = $data['id'] ?? ('user_' . time() . '_' . mt_rand(1000,9999));
        Database::execute('INSERT INTO users (id, email, password_hash, role, tenant_id) VALUES (?, ?, ?, ?, ?)', [
            $id,
            $data['email'] ?? '',
            $data['password_hash'] ?? '',
            $data['role'] ?? '',
            $data['tenant_id'] ?? null
        ]);
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
        // For workspace context, use the workspace tenant ID if available
        if (isset($_SESSION['workspace_tenant_id'])) {
            $user = self::getCurrentUser();
            if ($user && ($user['role'] === self::ROLE_SYSTEM_ADMIN || $user['tenant_id'] === $_SESSION['workspace_tenant_id'])) {
                return $_SESSION['workspace_tenant_id'];
            }
        }
        
        $user = self::getCurrentUser();
        return $user ? ($user['tenant_id'] ?: null) : null;
    }

    public static function canAccessWorkspace(string $tenantId): bool
    {
        $user = self::getCurrentUser();
        if (!$user) return false;
        
        // System admins can access any workspace
        if ($user['role'] === self::ROLE_SYSTEM_ADMIN) {
            return true;
        }
        
        // Tenant admins can only access their own workspace
        return $user['tenant_id'] === $tenantId;
    }
}
