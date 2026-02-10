<?php
/**
 * CLI Seed Script
 * Usage:
 *   php seed.php                              - Seed default data
 *   php seed.php admin <email> <password>     - Create system admin
 *   php seed.php tenant <name> <email> <pass> - Create tenant with admin
 */

// Bootstrap application
require_once __DIR__ . '/../autoload.php';

// Use PSR-4 imports
use HRAssistant\Core\Database;
use HRAssistant\Models\{User, Tenant, Team, Employee};

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
    // Use DB if available
    try {
        $exists = Database::fetchOne('SELECT * FROM users WHERE LOWER(email) = LOWER(?)', [$email]);
        if ($exists) {
            echo "User already exists: {$email}\n";
            return;
        }

        $id = generateId('user');
        Database::execute('INSERT INTO users (id, email, password_hash, role, tenant_id) VALUES (?, ?, ?, ?, ?)', [
            $id,
            $email,
            User::hashPassword($password),
            User::ROLE_SYSTEM_ADMIN,
            null
        ]);

        echo "System administrator created: {$email}\n";
        return;
    } catch (\Exception $e) {
        echo "Failed to create system admin in DB: " . $e->getMessage() . "\n";
        return;
    }
}

/**
 * Create a tenant with admin user
 */
function createTenant(string $name, string $adminEmail, string $adminPassword): void
{
    // Generate tenant ID from name
    $tenantId = 'tenant_' . preg_replace('/[^a-z0-9_]/', '_', strtolower($name));
    
    // Try DB insertion (create tenant if missing, but always attempt to create admin user)
    try {
        $exists = Database::fetchOne('SELECT * FROM tenants WHERE id = ? LIMIT 1', [$tenantId]);
        if (!$exists) {
            Database::execute('INSERT INTO tenants (id, name) VALUES (?, ?)', [$tenantId, $name]);
            echo "Tenant created: {$name} (ID: {$tenantId})\n";
        } else {
            echo "Tenant already exists: {$name}\n";
        }

        // Create or skip tenant admin if it already exists
        $adminExists = Database::fetchOne('SELECT * FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1', [$adminEmail]);
        if ($adminExists) {
            echo "Tenant admin already exists: {$adminEmail}\n";
            return;
        }

        $userId = generateId('user');
        Database::execute('INSERT INTO users (id, email, password_hash, role, tenant_id) VALUES (?, ?, ?, ?, ?)', [
            $userId,
            $adminEmail,
            User::hashPassword($adminPassword),
            User::ROLE_TENANT_ADMIN,
            $tenantId
        ]);

        echo "Tenant admin created: {$adminEmail}\n";
        return;
    } catch (\Exception $e) {
        echo "Failed to create tenant in DB: " . $e->getMessage() . "\n";
        return;
    }
}

/**
 * Seed default data
 */
function seedDefaultData(): void
{
    echo "Initializing system data...\n";
    
    // Seed default tenants and users into DB where possible
    try {
        // Create default tenant
        $tenantId = 'tenant_default_corp';
        $exists = Database::fetchOne('SELECT * FROM tenants WHERE id = ? LIMIT 1', [$tenantId]);
        if (!$exists) {
            Database::execute('INSERT INTO tenants (id, name) VALUES (?, ?)', [$tenantId, 'Default Corp']);
        }

        // Create system admin
        createSystemAdmin('sysadmin@corp.com', 'password');

        // Create tenant admin
        createTenant('Default Corp', 'admin@defaultcorp.com', 'password');

        // Create a sample team and employee for deterministic test data
        $tenantId = 'tenant_default_corp';
        $team = Team::create($tenantId, ['name' => 'Engineering', 'description' => 'Eng team']);
        $emp = Employee::create($tenantId, ['full_name' => 'Jane Doe', 'email' => 'jane.doe@defaultcorp.com', 'position' => 'Engineer', 'team_id' => $team['id']]);

        echo "System data initialized (DB).\n";
        echo "\nDefault credentials:\n";
        echo "  System Admin: sysadmin@corp.com / password\n";
        echo "  Tenant Admin: admin@defaultcorp.com / password\n";
        return;
    } catch (\Exception $e) {
        // Fallback to previous behavior
    }

    echo "System data initialized (legacy).\n";
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
