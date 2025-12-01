<?php
/**
 * CLI Seed Script
 * Usage:
 *   php seed.php                              - Seed default data
 *   php seed.php admin <email> <password>     - Create system admin
 *   php seed.php tenant <name> <email> <pass> - Create tenant with admin
 */

// Bootstrap application
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/ExcelStorage.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Tenant.php';

// Ensure CLI context
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

/**
 * Generate a unique ID with prefix
 */
function generateId(string $prefix): string
{
    return $prefix . '_' . time() . '_' . mt_rand(1000, 9999);
}

/**
 * Create a system administrator user
 */
function createSystemAdmin(string $email, string $password): void
{
    $users = ExcelStorage::readSheet('system.xlsx', 'users');
    
    // Check if user exists
    foreach ($users as $user) {
        if (strtolower($user['email']) === strtolower($email)) {
            echo "User already exists: {$email}\n";
            return;
        }
    }
    
    $user = [
        'id' => generateId('user'),
        'email' => $email,
        'password_hash' => User::hashPassword($password),
        'role' => User::ROLE_SYSTEM_ADMIN,
        'tenant_id' => ''
    ];
    
    $headers = ['id', 'email', 'password_hash', 'role', 'tenant_id'];
    ExcelStorage::appendRow('system.xlsx', 'users', $user, $headers);
    
    echo "System administrator created: {$email}\n";
}

/**
 * Create a tenant with admin user
 */
function createTenant(string $name, string $adminEmail, string $adminPassword): void
{
    // Generate tenant ID from name
    $tenantId = 'tenant_' . preg_replace('/[^a-z0-9_]/', '_', strtolower($name));
    
    // Check if tenant exists
    $tenants = ExcelStorage::readSheet('system.xlsx', 'tenants');
    foreach ($tenants as $tenant) {
        if ($tenant['id'] === $tenantId) {
            echo "Tenant already exists: {$name}\n";
            return;
        }
    }
    
    // Create tenant
    $tenant = [
        'id' => $tenantId,
        'name' => $name
    ];
    
    $tenantHeaders = ['id', 'name'];
    ExcelStorage::appendRow('system.xlsx', 'tenants', $tenant, $tenantHeaders);
    
    // Initialize tenant data
    ExcelStorage::initializeTenantData($tenantId);
    
    // Create tenant admin user
    $user = [
        'id' => generateId('user'),
        'email' => $adminEmail,
        'password_hash' => User::hashPassword($adminPassword),
        'role' => User::ROLE_TENANT_ADMIN,
        'tenant_id' => $tenantId
    ];
    
    $userHeaders = ['id', 'email', 'password_hash', 'role', 'tenant_id'];
    ExcelStorage::appendRow('system.xlsx', 'users', $user, $userHeaders);
    
    echo "Tenant created: {$name} (ID: {$tenantId})\n";
    echo "Tenant admin created: {$adminEmail}\n";
}

/**
 * Seed default data
 */
function seedDefaultData(): void
{
    echo "Initializing system data...\n";
    
    // ExcelStorage::init() is called automatically on require
    // It creates default data if not exists
    
    echo "System data initialized.\n";
    echo "\nDefault credentials:\n";
    echo "  System Admin: sysadmin@corp.com / password\n";
    echo "  Tenant Admin: admin@defaultcorp.com / password\n";
    echo "\nNOTE: For production, create new admins with hashed passwords:\n";
    echo "  ./make.sh seed:admin your@email.com yourpassword\n";
}

// Main execution
$command = $argv[1] ?? 'default';

switch ($command) {
    case 'admin':
        if (!isset($argv[2]) || !isset($argv[3])) {
            echo "Usage: php seed.php admin <email> <password>\n";
            exit(1);
        }
        createSystemAdmin($argv[2], $argv[3]);
        break;
        
    case 'tenant':
        if (!isset($argv[2]) || !isset($argv[3]) || !isset($argv[4])) {
            echo "Usage: php seed.php tenant <name> <email> <password>\n";
            exit(1);
        }
        createTenant($argv[2], $argv[3], $argv[4]);
        break;
        
    default:
        seedDefaultData();
        break;
}
