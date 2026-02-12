<?php
/**
 * Audit log writer.
 * Records all sensitive system and user actions.
 */

declare(strict_types=1);

namespace Src\Core;

final class AuditLog
{
    public function __construct(
        private readonly Database $db,
    ) {
    }

    public function log(
        string $action,
        ?int $tenantId = null,
        ?int $userId = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        $this->db->query(
            'INSERT INTO audit_logs '
            . '(tenant_id, user_id, action, entity_type, entity_id, old_value, new_value, ip_address, user_agent) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$tenantId, $userId, $action, $entityType, $entityId, $oldValue, $newValue, $ipAddress, $userAgent],
        );
    }

    public static function record(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $oldValue = null,
        ?string $newValue = null,
    ): void {
        $db = Database::getInstance();
        $instance = new self($db);
        $tenantId = $db->tenantId();
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        $instance->log($action, $tenantId, $userId, $entityType, $entityId, $oldValue, $newValue, $ip, $ua);
    }
}
