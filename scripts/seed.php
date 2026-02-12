<?php
/**
 * Seed script — creates default system administrator.
 * Usage: php scripts/seed.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Core/Config.php';

use Src\Core\Database;

$db = Database::getInstance();

$email = 'admin@hcms.local';
$password = 'admin';
$hash = password_hash($password, PASSWORD_BCRYPT);

$existing = $db->fetchOne('SELECT id FROM users WHERE email = ?', [$email]);
if ($existing) {
    echo "[seed] Admin user already exists (id={$existing['id']}).\n";
} else {
    $db->query(
        'INSERT INTO users (email, password_hash, display_name, is_system_admin) VALUES (?, ?, ?, 1)',
        [$email, $hash, 'System Admin'],
    );
    echo "[seed] Created admin: {$email} / {$password}\n";
}
