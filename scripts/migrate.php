<?php
/**
 * CLI migration runner — called by docker-entrypoint.sh.
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Core/Config.php';
require_once __DIR__ . '/../src/Core/Migration.php';

use Src\Core\Database;
use Src\Core\Migration;

$migration = new Migration(Database::getInstance());
$migration->run();

echo "[migrate] Done.\n";
