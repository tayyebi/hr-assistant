<?php
/**
 * Enhanced migration runner: executes SQL files in cli/migrations in order with better error handling
 */
require_once __DIR__ . '/../autoload.php';

// Use the existing Database class without namespace for now
// Create migrations table if it doesn't exist
try {
    Database::execute("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) UNIQUE NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Migrations table ready\n";
} catch (Exception $e) {
    echo "❌ Failed to create migrations table: " . $e->getMessage() . "\n";
    exit(1);
}

$dir = __DIR__ . '/migrations';
$files = glob($dir . '/*.sql');
sort($files, SORT_NATURAL);

foreach ($files as $file) {
    $name = basename($file);

    // Check if already applied
    $skip = false;
    try {
        $exists = Database::fetchOne('SELECT 1 FROM migrations WHERE name = ? LIMIT 1', [$name]);
        if ($exists) {
            echo "⏭️  Skipping {$name}, already applied\n";
            $skip = true;
        }
    } catch (\Exception $e) {
        // migrations table may not exist yet - proceed to apply
        $skip = false;
    }
    if ($skip) continue;

    echo "🚀 Applying {$name}...\n";
    $sql = file_get_contents($file);
    try {
        // Split multiple statements and execute them separately for better error handling
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                Database::getConnection()->exec($statement);
            }
        }
        
        // Record migration
        $stmt = Database::getConnection()->prepare('INSERT INTO migrations (name) VALUES (?)');
        $stmt->execute([$name]);
        echo "✅ Applied {$name}\n";
    } catch (\Exception $e) {
        echo "❌ Error applying {$name}: " . $e->getMessage() . "\n";
        exit(1); // Exit on error to prevent partial migrations
    }
}

echo "\n🎉 All migrations completed successfully!\n";
