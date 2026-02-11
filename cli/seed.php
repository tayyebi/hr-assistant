<?php
/**
 * CLI Seed Script
 * Usage:
 *   php seed.php                    - Create default system admin
 *   php seed.php <email> <password> - Create system admin with custom credentials
 */

// Bootstrap application
require_once __DIR__ . '/../autoload.php';

// Use PSR-4 imports
use App\Core\Database;
use App\Models\User;

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
    try {
        $exists = Database::fetchOne('SELECT * FROM users WHERE LOWER(email) = LOWER(?)', [$email]);
        if ($exists) {
            echo "✗ User already exists: {$email}\n";
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

        echo "✓ System administrator created: {$email}\n";
        echo "\nLogin credentials:\n";
        echo "  Email: {$email}\n";
        echo "  Password: {$password}\n";
        echo "\nYou can now login at /admin\n";
        return;
    } catch (\Exception $e) {
        echo "✗ Failed to create system admin: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Main execution
$email = $argv[1] ?? 'admin@localhost';
$password = $argv[2] ?? 'password';

echo "HR Assistant - System Administrator Seeder\n";
echo "==========================================\n\n";

createSystemAdmin($email, $password);
