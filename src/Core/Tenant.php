<?php
/**
 * Tenant resolution.
 * 1. Check HTTP_HOST against tenants.domain column.
 * 2. Fallback: extract slug from /w/{slug}/... path prefix.
 * Sets tenant context on Database singleton.
 */

declare(strict_types=1);

namespace Src\Core;

final class Tenant
{
    private ?array $current = null;

    public function __construct(
        private readonly Database $db,
        private readonly Request $request,
    ) {
    }

    public function resolve(): void
    {
        $this->current = $this->resolveByDomain();
        if ($this->current === null) {
            $this->current = $this->resolveByPrefix();
        }
        if ($this->current !== null) {
            $this->db->setTenantId((int)$this->current['id']);
        }
    }

    public function current(): ?array
    {
        return $this->current;
    }

    public function id(): ?int
    {
        return $this->current !== null ? (int)$this->current['id'] : null;
    }

    public function slug(): ?string
    {
        return $this->current['slug'] ?? null;
    }

    public function isResolved(): bool
    {
        return $this->current !== null;
    }

    public function pathPrefix(): string
    {
        if ($this->current === null) {
            return '';
        }
        if ($this->resolvedViaDomain()) {
            return '';
        }
        return '/w/' . $this->current['slug'];
    }

    public function setCurrent(?array $tenant): void
    {
        $this->current = $tenant;
        if ($tenant !== null) {
            $this->db->setTenantId((int)$tenant['id']);
        } else {
            $this->db->setTenantId(null);
        }
    }

    private function resolveByDomain(): ?array
    {
        $host = $this->request->host;
        if ($host === '' || $host === 'localhost') {
            return null;
        }
        try {
            $row = $this->db->fetchOne(
                'SELECT * FROM tenants WHERE domain = ? AND is_active = 1 LIMIT 1',
                [$host],
            );
            return $row ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveByPrefix(): ?array
    {
        $path = $this->request->uriPath;
        if (!str_starts_with($path, '/w/')) {
            return null;
        }
        $parts = explode('/', trim($path, '/'));
        if (count($parts) < 2 || $parts[0] !== 'w') {
            return null;
        }
        $slug = $parts[1];
        try {
            $row = $this->db->fetchOne(
                'SELECT * FROM tenants WHERE slug = ? AND is_active = 1 LIMIT 1',
                [$slug],
            );
            return $row ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolvedViaDomain(): bool
    {
        if ($this->current === null) {
            return false;
        }
        $host = $this->request->host;
        return isset($this->current['domain']) && $this->current['domain'] === $host;
    }
}
