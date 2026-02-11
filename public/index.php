<?php
/**
 * HR Assistant - Entry Point with Workspace Support
 * Pure PHP MVC Application
 */

session_start();

// Load our custom autoloader
require_once __DIR__ . '/../autoload.php';

// Initialize router and dispatch
use App\Core\Router;
$router = new Router();

// Define regular routes (login, admin)
$router->add('GET', '/', 'App\\Controllers\\AuthController', 'login');
$router->add('GET', '/login', 'App\\Controllers\\AuthController', 'login');
$router->add('POST', '/login', 'App\\Controllers\\AuthController', 'authenticate');
$router->add('GET', '/logout', 'App\\Controllers\\AuthController', 'logout');

// System admin routes
$router->add('GET', '/admin', 'App\\Controllers\\SystemAdminController', 'index');
$router->add('POST', '/admin/tenants', 'App\\Controllers\\SystemAdminController', 'createTenant');

// Define workspace routes (pattern: /workspace/{tenantId}/...)
$router->addWorkspace('GET', '/', 'App\\Controllers\\DashboardController', 'index');
$router->addWorkspace('GET', '/dashboard', 'App\\Controllers\\DashboardController', 'index');

// Employee routes
$router->addWorkspace('GET', '/employees', 'App\\Controllers\\EmployeeController', 'index');
$router->addWorkspace('POST', '/employees', 'App\\Controllers\\EmployeeController', 'store');
$router->addWorkspace('POST', '/employees/update', 'App\\Controllers\\EmployeeController', 'update');
$router->addWorkspace('POST', '/employees/delete', 'App\\Controllers\\EmployeeController', 'delete');

// Team routes
$router->addWorkspace('GET', '/teams', 'App\\Controllers\\TeamController', 'index');
$router->addWorkspace('POST', '/teams', 'App\\Controllers\\TeamController', 'store');
$router->addWorkspace('POST', '/teams/add-member', 'App\\Controllers\\TeamController', 'addMember');
$router->addWorkspace('POST', '/teams/remove-member', 'App\\Controllers\\TeamController', 'removeMember');
$router->addWorkspace('POST', '/teams/add-alias', 'App\\Controllers\\TeamController', 'addAlias');
$router->addWorkspace('POST', '/teams/remove-alias', 'App\\Controllers\\TeamController', 'removeAlias');

// Message routes
$router->addWorkspace('GET', '/messages', 'App\\Controllers\\MessageController', 'index');
$router->addWorkspace('POST', '/messages/send', 'App\\Controllers\\MessageController', 'send');
$router->addWorkspace('POST', '/messages/assign', 'App\\Controllers\\MessageController', 'assign');
$router->addWorkspace('POST', '/messages/retry', 'App\\Controllers\\MessageController', 'retryDelivery');

// Asset routes
$router->addWorkspace('GET', '/assets', 'App\\Controllers\\AssetController', 'index');
$router->addWorkspace('POST', '/assets/provision', 'App\\Controllers\\AssetController', 'provision');
$router->addWorkspace('POST', '/assets/assign', 'App\\Controllers\\AssetController', 'assignAsset');
$router->addWorkspace('POST', '/assets/unassignAsset', 'App\\Controllers\\AssetController', 'unassignAsset');
$router->addWorkspace('GET', '/api/provider-instances', 'App\\Controllers\\AssetController', 'getProviderInstances');
$router->addWorkspace('GET', '/api/employee-assets', 'App\\Controllers\\AssetController', 'getEmployeeAssets');

// Job routes
$router->addWorkspace('GET', '/jobs', 'App\\Controllers\\JobController', 'index');
$router->addWorkspace('POST', '/jobs/retry', 'App\\Controllers\\JobController', 'retry');

// Settings routes
$router->addWorkspace('GET', '/settings', 'App\\Controllers\\SettingsController', 'index');
$router->addWorkspace('POST', '/settings', 'App\\Controllers\\SettingsController', 'save');
$router->addWorkspace('POST', '/settings/providers', 'App\\Controllers\\SettingsController', 'createProviderInstance');
$router->addWorkspace('POST', '/settings/providers/delete', 'App\\Controllers\\SettingsController', 'deleteProviderInstance');

// Other routes
$router->addWorkspace('GET', '/reports', 'App\\Controllers\\ReportsController', 'index');
$router->addWorkspace('GET', '/notifications', 'App\\Controllers\\NotificationController', 'index');
$router->addWorkspace('GET', '/audit', 'App\\Controllers\\AuditController', 'index');
$router->addWorkspace('GET', '/api-docs', 'App\\Controllers\\ApiController', 'index');

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
