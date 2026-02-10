<?php
/**
 * Simple migration runner with custom autoloader
 * Executes SQL files in cli/migrations in order
 */
require_once __DIR__ . '/../autoload.php';

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
    } catch (Exception $e) {
        // migrations table might not exist yet, continue
    }

    if ($skip) continue;

    echo "Applying {$name}...\n";
    $sql = file_get_contents($file);
    
    try {
        Database::execute($sql);
        // Record this migration as applied
        Database::execute(
            'INSERT INTO migrations (name, applied_at) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE applied_at = NOW()',
            [$name]
        );
        echo "✓ Applied {$name}\n";
    } catch (Exception $e) {
        echo "✗ Failed to apply {$name}: " . $e->getMessage() . "\n";
        break;
    }
}

echo "Migration complete.\n";