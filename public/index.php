<?php
/**
 * HR Assistant - Entry Point
 * Pure PHP MVC Application
 */

session_start();

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load core framework
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/View.php';
require_once __DIR__ . '/../app/core/ExcelStorage.php';

// Load models
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Employee.php';
require_once __DIR__ . '/../app/models/Team.php';
require_once __DIR__ . '/../app/models/Message.php';
require_once __DIR__ . '/../app/models/Job.php';
require_once __DIR__ . '/../app/models/Config.php';
require_once __DIR__ . '/../app/models/Tenant.php';

// Load controllers
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/EmployeeController.php';
require_once __DIR__ . '/../app/controllers/TeamController.php';
require_once __DIR__ . '/../app/controllers/MessageController.php';
require_once __DIR__ . '/../app/controllers/AssetController.php';
require_once __DIR__ . '/../app/controllers/JobController.php';
require_once __DIR__ . '/../app/controllers/SettingsController.php';
require_once __DIR__ . '/../app/controllers/SystemAdminController.php';

// Initialize router and dispatch
$router = new Router();

// Define routes
$router->add('GET', '/', 'AuthController', 'login');
$router->add('GET', '/login', 'AuthController', 'login');
$router->add('POST', '/login', 'AuthController', 'authenticate');
$router->add('GET', '/logout', 'AuthController', 'logout');

// Protected routes
$router->add('GET', '/dashboard', 'DashboardController', 'index');
$router->add('GET', '/employees', 'EmployeeController', 'index');
$router->add('POST', '/employees', 'EmployeeController', 'store');
$router->add('POST', '/employees/update', 'EmployeeController', 'update');
$router->add('POST', '/employees/delete', 'EmployeeController', 'delete');

$router->add('GET', '/teams', 'TeamController', 'index');
$router->add('POST', '/teams', 'TeamController', 'store');
$router->add('POST', '/teams/add-member', 'TeamController', 'addMember');
$router->add('POST', '/teams/remove-member', 'TeamController', 'removeMember');
$router->add('POST', '/teams/add-alias', 'TeamController', 'addAlias');
$router->add('POST', '/teams/remove-alias', 'TeamController', 'removeAlias');

$router->add('GET', '/messages', 'MessageController', 'index');
$router->add('POST', '/messages/send', 'MessageController', 'send');
$router->add('POST', '/messages/assign', 'MessageController', 'assign');

$router->add('GET', '/assets', 'AssetController', 'index');
$router->add('POST', '/assets/provision', 'AssetController', 'provision');

$router->add('GET', '/jobs', 'JobController', 'index');
$router->add('POST', '/jobs/retry', 'JobController', 'retry');

$router->add('GET', '/settings', 'SettingsController', 'index');
$router->add('POST', '/settings', 'SettingsController', 'save');

$router->add('GET', '/admin', 'SystemAdminController', 'index');
$router->add('POST', '/admin/tenants', 'SystemAdminController', 'createTenant');

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
