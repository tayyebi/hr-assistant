<?php
echo "Testing EmailProvider class loading...\n";

try {
    require_once __DIR__ . '/autoload.php';
    
    // Test if EmailProvider class exists and can be accessed
    echo "EmailProvider::MAILCOW = " . \App\Core\EmailProvider::MAILCOW . "\n";
    echo "✓ EmailProvider class is working!\n";
    
    // Test ProviderSettings
    echo "Testing ProviderSettings::getProvidersMetadata()...\n";
    $metadata = \App\Core\ProviderSettings::getProvidersMetadata();
    echo "✓ Got " . count($metadata) . " provider metadata entries\n";
    echo "✓ ProviderSettings is working!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}