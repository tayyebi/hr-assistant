<?php
/**
 * HR Assistant - Entry Point
 * Pure PHP MVC Application with Custom Autoloader
 */

session_start();

// Load our custom autoloader (replaces Composer and manual requires)
require_once __DIR__ . '/../autoload.php';

// Initialize router and dispatch
use App\Core\Router;
$router = new Router();

// Define routes
$router->add('GET', '/', 'App\\Controllers\\AuthController', 'login');
$router->add('GET', '/login', 'App\\Controllers\\AuthController', 'login');
$router->add('POST', '/login', 'App\\Controllers\\AuthController', 'authenticate');
$router->add('GET', '/logout', 'App\\Controllers\\AuthController', 'logout');

// Protected routes
$router->add('GET', '/dashboard', 'App\\Controllers\\DashboardController', 'index');
$router->add('GET', '/employees', 'App\\Controllers\\EmployeeController', 'index');
$router->add('POST', '/employees', 'App\\Controllers\\EmployeeController', 'store');
$router->add('POST', '/employees/update', 'App\\Controllers\\EmployeeController', 'update');
$router->add('POST', '/employees/delete', 'App\\Controllers\\EmployeeController', 'delete');

$router->add('GET', '/teams', 'App\\Controllers\\TeamController', 'index');
$router->add('POST', '/teams', 'App\\Controllers\\TeamController', 'store');
$router->add('POST', '/teams/add-member', 'App\\Controllers\\TeamController', 'addMember');
$router->add('POST', '/teams/remove-member', 'App\\Controllers\\TeamController', 'removeMember');
$router->add('POST', '/teams/add-alias', 'App\\Controllers\\TeamController', 'addAlias');
$router->add('POST', '/teams/remove-alias', 'App\\Controllers\\TeamController', 'removeAlias');

$router->add('GET', '/messages', 'App\\Controllers\\MessageController', 'index');
$router->add('POST', '/messages/send', 'App\\Controllers\\MessageController', 'send');
$router->add('POST', '/messages/assign', 'App\\Controllers\\MessageController', 'assign');
$router->add('POST', '/messages/retry', 'App\\Controllers\\MessageController', 'retryDelivery');

$router->add('GET', '/assets', 'App\\Controllers\\AssetController', 'index');
$router->add('POST', '/assets/provision', 'App\\Controllers\\AssetController', 'provision');
$router->add('POST', '/assets/assign', 'App\\Controllers\\AssetController', 'assignAsset');
$router->add('POST', '/assets/unassignAsset', 'App\\Controllers\\AssetController', 'unassignAsset');
$router->add('GET', '/api/provider-instances', 'App\\Controllers\\AssetController', 'getProviderInstances');
$router->add('GET', '/api/employee-assets', 'App\\Controllers\\AssetController', 'getEmployeeAssets');

$router->add('GET', '/jobs', 'App\\Controllers\\JobController', 'index');
$router->add('POST', '/jobs/retry', 'App\\Controllers\\JobController', 'retry');

$router->add('GET', '/settings', 'App\\Controllers\\SettingsController', 'index');
$router->add('POST', '/settings', 'App\\Controllers\\SettingsController', 'save');
// Provider Instances management
$router->add('POST', '/settings/providers', 'App\\Controllers\\SettingsController', 'createProviderInstance');
$router->add('POST', '/settings/providers/delete', 'App\\Controllers\\SettingsController', 'deleteProviderInstance');

$router->add('GET', '/admin', 'App\\Controllers\\SystemAdminController', 'index');
$router->add('POST', '/admin/tenants', 'App\\Controllers\\SystemAdminController', 'createTenant');

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));