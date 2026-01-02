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
require_once __DIR__ . '/../app/core/Icon.php';
// Database helper (for MySQL-backed storage)
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/ProviderSettings.php';
require_once __DIR__ . '/../app/core/ProviderType.php';
// Provider base classes
require_once __DIR__ . '/../app/core/Provider.php';
require_once __DIR__ . '/../app/core/HttpProvider.php';

// Provider implementations (depends on the base classes above)
require_once __DIR__ . '/../app/core/Providers.php';

// Provider factory must be loaded after implementations to allow auto-registration
require_once __DIR__ . '/../app/core/ProviderFactory.php';
require_once __DIR__ . '/../app/core/AssetManager.php';

// Load models
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Employee.php';
require_once __DIR__ . '/../app/models/Team.php';
require_once __DIR__ . '/../app/models/Asset.php';
require_once __DIR__ . '/../app/models/Message.php';
require_once __DIR__ . '/../app/models/Job.php';
require_once __DIR__ . '/../app/models/ProviderInstance.php';
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
$router->add('POST', '/messages/retry', 'MessageController', 'retryDelivery');

$router->add('GET', '/assets', 'AssetController', 'index');
$router->add('POST', '/assets/provision', 'AssetController', 'provision');
$router->add('POST', '/assets/assign', 'AssetController', 'assignAsset');
$router->add('GET', '/api/provider-instances', 'AssetController', 'getProviderInstances');

$router->add('GET', '/jobs', 'JobController', 'index');
$router->add('POST', '/jobs/retry', 'JobController', 'retry');

$router->add('GET', '/settings', 'SettingsController', 'index');
$router->add('POST', '/settings', 'SettingsController', 'save');
// Provider Instances management
$router->add('POST', '/settings/providers', 'SettingsController', 'createProviderInstance');
$router->add('POST', '/settings/providers/delete', 'SettingsController', 'deleteProviderInstance');

$router->add('GET', '/admin', 'SystemAdminController', 'index');
$router->add('POST', '/admin/tenants', 'SystemAdminController', 'createTenant');

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
