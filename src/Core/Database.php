<?php
/**
 * PDO wrapper with singleton access and tenant-aware helpers.
 */

declare(strict_types=1);

namespace Src\Core;

use PDO;
use PDOStatement;

final class Database
{
    private static ?self $instance = null;
    private PDO $pdo;
    private ?int $tenantId = null;

    private function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            require_once __DIR__ . '/Config.php';
            $cfg = Config::getInstance();
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $cfg->dbHost(),
                $cfg->dbPort(),
                $cfg->dbName(),
            );
            $pdo = new PDO($dsn, $cfg->dbUser(), $cfg->dbPass(), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function setTenantId(?int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function tenantId(): ?int
    {
        return $this->tenantId;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function tenantFetchAll(string $table, string $where = '1=1', array $params = []): array
    {
        $sql = "SELECT * FROM {$table} WHERE tenant_id = ? AND ({$where})";
        array_unshift($params, $this->tenantId);
        return $this->fetchAll($sql, $params);
    }

    public function tenantInsert(string $table, array $data): string
    {
        $data['tenant_id'] = $this->tenantId;
        $cols = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$cols}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        return $this->lastInsertId();
    }
}
