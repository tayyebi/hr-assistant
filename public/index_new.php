<?php
/**
 * HR Assistant - Entry Point
 * Pure PHP MVC Application with Custom Autoloader
 */

session_start();

// Load our custom autoloader (replaces Composer and manual requires)
require_once __DIR__ . '/../autoload.php';

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
$router->add('POST', '/assets/unassignAsset', 'AssetController', 'unassignAsset');
$router->add('GET', '/api/provider-instances', 'AssetController', 'getProviderInstances');
$router->add('GET', '/api/employee-assets', 'AssetController', 'getEmployeeAssets');

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