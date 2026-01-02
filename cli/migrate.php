<?php
/**
 * Simple migration runner: executes SQL files in cli/migrations in order
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/Database.php';

$dir = __DIR__ . '/migrations';
$files = glob($dir . '/*.sql');
sort($files, SORT_NATURAL);

foreach ($files as $file) {
    $name = basename($file);

    // Check if already applied. If the migrations table doesn't exist yet, proceed to applying.
    $skip = false;
    try {
        $exists = Database::fetchOne('SELECT 1 FROM migrations WHERE name = ? LIMIT 1', [$name]);
        if ($exists) {
            echo "Skipping {$name}, already applied\n";
            $skip = true;
        }
    } catch (\Exception $e) {
        // migrations table may not exist yet - proceed to apply
        $skip = false;
    }
    if ($skip) continue;

    echo "Applying {$name}...\n";
    $sql = file_get_contents($file);
    try {
        // Execute the SQL file. Avoid wrapping DDL in transactions as some statements
        // cause implicit commits in MariaDB/MySQL which makes transactions unreliable here.
        Database::getConnection()->exec($sql);
        // Record migration
        $stmt = Database::getConnection()->prepare('INSERT INTO migrations (name) VALUES (?)');
        $stmt->execute([$name]);
        echo "Applied {$name}\n";
    } catch (\Exception $e) {
        echo "Error applying {$name}: " . $e->getMessage() . "\n";
        // Do not abort entire process - report and move on
    }
}

echo "Migrations complete.\n";
