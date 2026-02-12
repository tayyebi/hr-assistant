<?php
/**
 * Schema migration runner.
 * Scans core and plugin migration directories for .sql files.
 * Files sorted by name, executed once, tracked in `migrations` table.
 */

declare(strict_types=1);

namespace Src\Core;

final class Migration
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function run(): void
    {
        $this->ensureMigrationsTable();
        $executed = $this->executedMigrations();
        $files = $this->collectMigrationFiles();

        sort($files);

        foreach ($files as $file) {
            $name = basename($file);
            if (in_array($name, $executed, true)) {
                continue;
            }
            $sql = file_get_contents($file);
            if ($sql === false || trim($sql) === '') {
                continue;
            }
            $this->db->pdo()->exec($sql);
            $this->db->query(
                'INSERT INTO migrations (name, executed_at) VALUES (?, NOW())',
                [$name],
            );
        }
    }

    private function ensureMigrationsTable(): void
    {
        $this->db->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS migrations ('
            . 'id INT AUTO_INCREMENT PRIMARY KEY,'
            . 'name VARCHAR(255) NOT NULL UNIQUE,'
            . 'executed_at DATETIME NOT NULL'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    private function executedMigrations(): array
    {
        $rows = $this->db->fetchAll('SELECT name FROM migrations');
        return array_column($rows, 'name');
    }

    private function collectMigrationFiles(): array
    {
        $files = [];

        $coreDir = dirname(__DIR__) . '/Migrations';
        if (is_dir($coreDir)) {
            foreach (glob($coreDir . '/*.sql') as $f) {
                $files[] = $f;
            }
        }

        $pluginsDir = dirname(__DIR__) . '/Plugins';
        if (is_dir($pluginsDir)) {
            foreach (glob($pluginsDir . '/*/migrations/*.sql') as $f) {
                $files[] = $f;
            }
        }

        return $files;
    }
}
