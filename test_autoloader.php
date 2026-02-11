<?php

// Test the refactored autoloader
require_once 'autoload.php';

echo "Testing refactored autoloader...\n\n";

// Test 1: Check if core classes can be loaded
echo "1. Testing class_exists checks...\n";

$testClasses = [
    'App\\Core\\Router' => 'Router',
    'App\\Core\\View' => 'View', 
    'App\\Core\\UrlHelper' => 'UrlHelper',
    'App\\Controllers\\MessageController' => 'MessageController',
    'App\\Models\\Employee' => 'Employee'
];

foreach ($testClasses as $fullClass => $bareClass) {
    echo "   Testing $fullClass...\n";
    
    if (class_exists($fullClass)) {
        echo "   ✓ $fullClass exists\n";
        
        if (class_exists($bareClass, false)) {
            echo "   ✓ Automatic alias '$bareClass' created\n";
        } else {
            echo "   ✗ Automatic alias '$bareClass' NOT created\n";
        }
    } else {
        echo "   ✗ $fullClass does not exist\n";
    }
    echo "\n";
}

// Test 2: Test bare class name loading
echo "2. Testing bare class name loading...\n";

$bareClasses = ['Router', 'View'];

foreach ($bareClasses as $bareClass) {
    echo "   Testing $bareClass...\n";
    if (class_exists($bareClass)) {
        echo "   ✓ '$bareClass' loaded successfully\n";
        // Get the actual class name to see full namespace
        $reflection = new ReflectionClass($bareClass);
        echo "   → Full name: " . $reflection->getName() . "\n";
    } else {
        echo "   ✗ '$bareClass' failed to load\n";
    }
    echo "\n";
}

echo "✅ Autoloader refactoring test complete!\n";