<?php
/**
 * HR Assistant - Entry Point with Workspace Support
 * Pure PHP MVC Application
 */

session_start();

// Load our custom autoloader
require_once __DIR__ . '/../autoload.php';

// Initialize router and dispatch
$router = new Router();

// Define regular routes (login, admin)
$router->add('GET', '/', 'AuthController', 'login');
$router->add('GET', '/login', 'AuthController', 'login');
$router->add('POST', '/login', 'AuthController', 'authenticate');
$router->add('GET', '/logout', 'AuthController', 'logout');

// System admin routes
$router->add('GET', '/admin', 'SystemAdminController', 'index');
$router->add('POST', '/admin/tenants', 'SystemAdminController', 'createTenant');

// Define workspace routes (pattern: /workspace/{tenantId}/...)
$router->addWorkspace('GET', '/', 'DashboardController', 'index');
$router->addWorkspace('GET', '/dashboard', 'DashboardController', 'index');

$router->addWorkspace('GET', '/employees', 'EmployeeController', 'index');
$router->addWorkspace('POST', '/employees', 'EmployeeController', 'store');
$router->addWorkspace('POST', '/employees/update', 'EmployeeController', 'update');
$router->addWorkspace('POST', '/employees/delete', 'EmployeeController', 'delete');

$router->addWorkspace('GET', '/teams', 'TeamController', 'index');
$router->addWorkspace('POST', '/teams', 'TeamController', 'store');
$router->addWorkspace('POST', '/teams/add-member', 'TeamController', 'addMember');
$router->addWorkspace('POST', '/teams/remove-member', 'TeamController', 'removeMember');
$router->addWorkspace('POST', '/teams/add-alias', 'TeamController', 'addAlias');
$router->addWorkspace('POST', '/teams/remove-alias', 'TeamController', 'removeAlias');

$router->addWorkspace('GET', '/messages', 'MessageController', 'index');
$router->addWorkspace('POST', '/messages/send', 'MessageController', 'send');
$router->addWorkspace('POST', '/messages/assign', 'MessageController', 'assign');
$router->addWorkspace('POST', '/messages/retry', 'MessageController', 'retryDelivery');

$router->addWorkspace('GET', '/assets', 'AssetController', 'index');
$router->addWorkspace('POST', '/assets/provision', 'AssetController', 'provision');
$router->addWorkspace('POST', '/assets/assign', 'AssetController', 'assignAsset');
$router->addWorkspace('POST', '/assets/unassignAsset', 'AssetController', 'unassignAsset');
$router->addWorkspace('GET', '/api/provider-instances', 'AssetController', 'getProviderInstances');
$router->addWorkspace('GET', '/api/employee-assets', 'AssetController', 'getEmployeeAssets');

$router->addWorkspace('GET', '/jobs', 'JobController', 'index');
$router->addWorkspace('POST', '/jobs/retry', 'JobController', 'retry');

$router->addWorkspace('GET', '/settings', 'SettingsController', 'index');
$router->addWorkspace('POST', '/settings', 'SettingsController', 'save');
$router->addWorkspace('POST', '/settings/providers', 'SettingsController', 'createProviderInstance');
$router->addWorkspace('POST', '/settings/providers/delete', 'SettingsController', 'deleteProviderInstance');

$router->addWorkspace('GET', '/reports', 'ReportsController', 'index');
$router->addWorkspace('GET', '/notifications', 'NotificationController', 'index');
$router->addWorkspace('GET', '/audit', 'AuditController', 'index');
$router->addWorkspace('GET', '/api-docs', 'ApiController', 'index');

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
