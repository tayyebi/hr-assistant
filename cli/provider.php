<?php
/**
 * CLI Provider management
 * Usage:
 *   php provider.php create <type> <provider> <name> [settings_json]
 *   php provider.php list
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/ProviderInstance.php';

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

$cmd = $argv[1] ?? 'list';
switch ($cmd) {
    case 'create':
        if (!isset($argv[2]) || !isset($argv[3]) || !isset($argv[4])) {
            echo "Usage: php provider.php create <type> <provider> <name> [settings_json]\n";
            exit(1);
        }
        $type = $argv[2];
        $provider = $argv[3];
        $name = $argv[4];
        $settings = $argv[5] ?? '';
        $settingsParsed = [];
        if (!empty($settings)) {
            $decoded = json_decode($settings, true);
            $settingsParsed = is_array($decoded) ? $decoded : [];
        }

        // Use default tenant for CLI environment 'tenant_default_corp'
        $tenantId = 'tenant_default_corp';
        $row = ProviderInstance::create($tenantId, [
            'type' => $type,
            'provider' => $provider,
            'name' => $name,
            'settings' => $settingsParsed
        ]);
        echo "Created provider instance: {$row['id']}\n";
        break;

    case 'list':
    default:
        $tenantId = 'tenant_default_corp';
        $rows = ProviderInstance::getAll($tenantId);
        foreach ($rows as $r) {
            echo "{$r['id']}\t{$r['name']}\t{$r['type']}\t{$r['provider']}\n";
        }
        break;
}
