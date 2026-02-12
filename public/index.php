<?php
/**
 * Front controller.
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/Core/App.php';

use Src\Core\App;

$app = App::fromGlobals();
$app->run();
