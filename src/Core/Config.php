<?php
/**
 * Application configuration.
 * Reads from environment variables for bootstrap (DB connection),
 * then delegates to the `settings` table for all runtime config.
 */

declare(strict_types=1);

namespace Src\Core;

final class Config
{
    private static ?self $instance = null;
    private array $envCache = [];

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function env(string $key, string $default = ''): string
    {
        if (isset($this->envCache[$key])) {
            return $this->envCache[$key];
        }
        $value = getenv($key);
        if ($value === false) {
            $value = $default;
        }
        $this->envCache[$key] = $value;
        return $value;
    }

    public function dbHost(): string
    {
        return $this->env('DB_HOST', '127.0.0.1');
    }

    public function dbPort(): int
    {
        return (int)$this->env('DB_PORT', '3306');
    }

    public function dbName(): string
    {
        return $this->env('DB_NAME', 'app');
    }

    public function dbUser(): string
    {
        return $this->env('DB_USER', 'root');
    }

    public function dbPass(): string
    {
        return $this->env('DB_PASS', '');
    }
}
