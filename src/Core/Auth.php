<?php
/**
 * Authentication and RBAC.
 * Roles: system_admin (global), workspace_admin, hr_specialist, team_member (per-tenant).
 */

declare(strict_types=1);

namespace Src\Core;

final class Auth
{
    public function __construct(
        private readonly Database $db,
        private readonly Session $session,
        private readonly Tenant $tenant,
    ) {
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->db->fetchOne(
            'SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1',
            [$email],
        );
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        $this->session->set('user_id', (int)$user['id']);
        return true;
    }

    public function logout(): void
    {
        $this->session->destroy();
    }

    public function user(): ?array
    {
        $uid = $this->session->get('user_id');
        if ($uid === null) {
            return null;
        }
        return $this->db->fetchOne(
            'SELECT * FROM users WHERE id = ? AND is_active = 1 LIMIT 1',
            [$uid],
        ) ?: null;
    }

    public function userId(): ?int
    {
        return $this->session->get('user_id');
    }

    public function isLoggedIn(): bool
    {
        return $this->user() !== null;
    }

    public function isSystemAdmin(): bool
    {
        $user = $this->user();
        return $user !== null && (int)($user['is_system_admin'] ?? 0) === 1;
    }

    public function tenantRole(): ?string
    {
        $user = $this->user();
        $tenantId = $this->tenant->id();
        if ($user === null || $tenantId === null) {
            return null;
        }
        $tu = $this->db->fetchOne(
            'SELECT role FROM tenant_users WHERE tenant_id = ? AND user_id = ? LIMIT 1',
            [$tenantId, $user['id']],
        );
        return $tu ? $tu['role'] : null;
    }

    public function hasRole(string ...$roles): bool
    {
        if ($this->isSystemAdmin()) {
            return true;
        }
        $current = $this->tenantRole();
        if ($current === null) {
            return false;
        }
        return in_array($current, $roles, true);
    }

    public function requireLogin(Response $response): bool
    {
        if (!$this->isLoggedIn()) {
            $response->redirect('/login');
            return false;
        }
        return true;
    }

    public function requireRole(Response $response, string ...$roles): bool
    {
        if (!$this->requireLogin($response)) {
            return false;
        }
        if (!$this->hasRole(...$roles)) {
            $response->status(403)->html('<h1>403 Forbidden</h1>');
            return false;
        }
        return true;
    }

    public function userTenants(): array
    {
        $uid = $this->userId();
        if ($uid === null) {
            return [];
        }
        if ($this->isSystemAdmin()) {
            return $this->db->fetchAll('SELECT * FROM tenants WHERE is_active = 1 ORDER BY name');
        }
        return $this->db->fetchAll(
            'SELECT t.* FROM tenants t '
            . 'INNER JOIN tenant_users tu ON tu.tenant_id = t.id '
            . 'WHERE tu.user_id = ? AND t.is_active = 1 ORDER BY t.name',
            [$uid],
        );
    }
}
