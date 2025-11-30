<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/models/Storage.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

$validPages = ['dashboard', 'employees', 'teams', 'messages', 'assets', 'settings'];
if (!in_array($page, $validPages)) {
    $page = 'dashboard';
}

$controllerFile = BASE_PATH . '/controllers/' . ucfirst($page) . 'Controller.php';
if (!file_exists($controllerFile)) {
    $page = 'dashboard';
    $controllerFile = BASE_PATH . '/controllers/DashboardController.php';
}

require_once $controllerFile;

$controllerName = ucfirst($page) . 'Controller';
$controller = new $controllerName();

if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    $controller->index();
}
