<?php
/**
 * Test script to verify provider class loading fixes
 */

echo "Testing Provider class loading fixes...\n\n";

require_once __DIR__ . '/autoload.php';

echo "1. Testing core provider type classes:\n";

$classesToTest = [
    'App\\Core\\EmailProvider',
    'App\\Core\\GitProvider',
    'App\\Core\\MessengerProvider', 
    'App\\Core\\IamProvider'
];

foreach ($classesToTest as $class) {
    $shortName = basename(str_replace('\\', '/', $class));
    if (class_exists($class)) {
        echo "   ✓ $shortName class exists\n";
        
        // Test constants
        try {
            if ($class === 'App\\Core\\EmailProvider') {
                $const = $class::MAILCOW;
                echo "     ✓ MAILCOW constant: $const\n";
            }
            if ($class === 'App\\Core\\GitProvider') {
                $const = $class::GITLAB;
                echo "     ✓ GITLAB constant: $const\n";
            }
            if ($class === 'App\\Core\\MessengerProvider') {
                $const = $class::TELEGRAM;
                echo "     ✓ TELEGRAM constant: $const\n";
            }
            if ($class === 'App\\Core\\IamProvider') {
                $const = $class::KEYCLOAK;
                echo "     ✓ KEYCLOAK constant: $const\n";
            }
        } catch (Exception $e) {
            echo "     ✗ Error accessing constants: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ✗ $shortName class not found\n";
    }
}

echo "\n2. Testing ProviderSettings class:\n";
try {
    $metadata = App\Core\ProviderSettings::getProvidersMetadata();
    echo "   ✓ ProviderSettings::getProvidersMetadata() works\n";
    echo "   ✓ Found " . count($metadata) . " provider metadata entries\n";
} catch (Exception $e) {
    echo "   ✗ ProviderSettings error: " . $e->getMessage() . "\n";
}

echo "\n3. Testing provider implementation classes:\n";

$implClasses = [
    'App\\Core\\MailcowProvider',
    'App\\Core\\GitLabProvider',
    'App\\Core\\TelegramProvider',
    'App\\Core\\KeycloakProvider'
];

foreach ($implClasses as $class) {
    $shortName = basename(str_replace('\\', '/', $class));
    if (class_exists($class)) {
        echo "   ✓ $shortName implementation exists\n";
    } else {
        echo "   ✗ $shortName implementation not found\n";
    }
}

echo "\nTest completed!\n";