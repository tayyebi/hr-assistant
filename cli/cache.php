<?php
/**
 * CLI Cache Script
 * 
 * Usage:
 *   php cache.php clear  - Clear application cache
 *   php cache.php stats  - Show cache statistics
 */

// Bootstrap application
require_once __DIR__ . '/../vendor/autoload.php';

// Ensure CLI context
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

/**
 * Clear application cache
 */
function clearCache(): void
{
    $cacheDir = __DIR__ . '/../data/cache';
    
    if (!is_dir($cacheDir)) {
        echo "No cache directory found.\n";
        return;
    }
    
    $files = glob($cacheDir . '/*');
    $count = 0;
    
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $count++;
        }
    }
    
    echo "Cleared {$count} cache file(s).\n";
    
    // Also clear any .lock files
    $dataDir = __DIR__ . '/../data';
    $lockFiles = glob($dataDir . '/*.lock');
    $lockCount = 0;
    
    foreach ($lockFiles as $file) {
        if (is_file($file)) {
            unlink($file);
            $lockCount++;
        }
    }
    
    if ($lockCount > 0) {
        echo "Removed {$lockCount} stale lock file(s).\n";
    }
}

/**
 * Show cache statistics
 */
function cacheStats(): void
{
    $dataDir = __DIR__ . '/../data';
    $cacheDir = __DIR__ . '/../data/cache';
    
    echo "=== Cache Statistics ===\n\n";
    
    echo "Data is stored in the configured database (MySQL). No legacy .xlsx data files are used.\n";
    
    // Cache files
    if (is_dir($cacheDir)) {
        $cacheFiles = glob($cacheDir . '/*');
        echo "Cache files: " . count($cacheFiles) . "\n";
    } else {
        echo "Cache files: 0 (no cache directory)\n";
    }
    
    // Lock files
    $lockFiles = glob($dataDir . '/*.lock');
    echo "Lock files: " . count($lockFiles) . "\n";
    
    if (!empty($lockFiles)) {
        echo "\nActive locks:\n";
        foreach ($lockFiles as $file) {
            $age = time() - filemtime($file);
            echo "  - " . basename($file) . " (age: {$age}s)\n";
        }
    }
}

/**
 * Format bytes to human readable
 */
function formatBytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

// Main execution
$command = $argv[1] ?? 'stats';

switch ($command) {
    case 'clear':
        clearCache();
        break;
        
    case 'stats':
        cacheStats();
        break;
        
    default:
        echo "Usage: php cache.php <clear|stats>\n";
        exit(1);
}
