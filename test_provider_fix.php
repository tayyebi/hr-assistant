<?php
/**
 * Quick test to verify provider class loading
 */

echo "Testing provider class loading fix...\n";

require_once __DIR__ . '/autoload.php';

try {
    echo "1. Testing ProviderSettings::getProvidersMetadata():\n";
    $metadata = App\Core\ProviderSettings::getProvidersMetadata();
    echo "   ✓ Success! Got " . count($metadata) . " provider metadata entries\n";
    
    echo "2. Testing individual provider classes:\n";
    echo "   - EmailProvider::MAILCOW: " . App\Core\EmailProvider::MAILCOW . "\n";
    echo "   - GitProvider::GITLAB: " . App\Core\GitProvider::GITLAB . "\n";
    echo "   - MessengerProvider::TELEGRAM: " . App\Core\MessengerProvider::TELEGRAM . "\n";
    echo "   - IamProvider::KEYCLOAK: " . App\Core\IamProvider::KEYCLOAK . "\n";
    echo "   ✓ All provider constants accessible\n";
    
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "Test completed!\n";