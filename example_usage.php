<?php
/**
 * Example usage of the HR Assistant with custom autoloader
 */

echo "HR Assistant Autoloader Example\n";
echo "===============================\n\n";

// Load the bootstrap (which includes the autoloader)
require_once __DIR__ . '/bootstrap.php';

echo "1. Bootstrap loaded successfully\n";
echo "2. Constants defined: APP_ROOT = " . APP_ROOT . "\n";
echo "3. CLI Mode: " . (isCliMode() ? "Yes" : "No") . "\n\n";

// Test creating some objects
echo "4. Testing object creation:\n";

try {
    $router = new Router();
    echo "   - Router: ✓\n";
} catch (Exception $e) {
    echo "   - Router: ✗ " . $e->getMessage() . "\n";
}

try {
    // This might fail due to database dependency, which is expected
    $user = new User();
    echo "   - User: ✓\n";
} catch (Error $e) {
    echo "   - User: ⚠ (Expected - requires database)\n";
}

echo "\n5. Autoloader statistics:\n";
$stats = HRAutoloader::getDebugInfo();
echo "   - Total classes registered: " . $stats['total_classes'] . "\n";
echo "   - Controllers: " . $stats['controller_classes'] . "\n";
echo "   - Models: " . $stats['model_classes'] . "\n";
echo "   - Core classes: " . $stats['core_classes'] . "\n";

echo "\n6. Testing utility functions:\n";
writeLog("Test log entry from autoloader example");
echo "   - Log function: ✓\n";

debug("This is a debug message", false);
echo "   - Debug function: ✓\n";

echo "\nExample completed successfully!\n";