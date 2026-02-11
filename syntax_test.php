<?php
/**
 * Quick test to verify the syntax fix
 */

echo "Testing ProviderSettings syntax fix...\n";

try {
    require_once __DIR__ . '/autoload.php';
    
    echo "1. Testing EmailProvider class:\n";
    echo "   MAILCOW = " . App\Core\EmailProvider::MAILCOW . "\n";
    echo "   ✓ EmailProvider working\n";
    
    echo "2. Testing ProviderSettings::getProvidersMetadata():\n";
    $metadata = App\Core\ProviderSettings::getProvidersMetadata();
    echo "   ✓ Got " . count($metadata) . " providers\n";
    
    echo "3. Testing ProviderSettings::getFields():\n";
    $fields = App\Core\ProviderSettings::getFields(App\Core\EmailProvider::MAILCOW);
    echo "   ✓ Got " . count($fields) . " fields for mailcow\n";
    
    echo "\n✅ All tests passed! Syntax error is fixed.\n";
    
} catch (ParseError $e) {
    echo "❌ Parse Error: " . $e->getMessage() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}