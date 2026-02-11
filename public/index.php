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
$router->add('POST', '/admin/tenants/edit', 'App\\Controllers\\SystemAdminController', 'editTenant');
$router->add('POST', '/admin/tenants/deactivate', 'App\\Controllers\\SystemAdminController', 'deactivateTenant');
$router->add('POST', '/admin/tenants/activate', 'App\\Controllers\\SystemAdminController', 'activateTenant');
$router->add('POST', '/admin/tenants/delete', 'App\\Controllers\\SystemAdminController', 'deleteTenant');

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
$router->addWorkspace('POST', '/teams/update', 'App\\Controllers\\TeamController', 'update');
$router->addWorkspace('POST', '/teams/delete', 'App\\Controllers\\TeamController', 'delete');
$router->addWorkspace('POST', '/teams/add-member', 'App\\Controllers\\TeamController', 'addMember');
$router->addWorkspace('POST', '/teams/remove-member', 'App\\Controllers\\TeamController', 'removeMember');
$router->addWorkspace('POST', '/teams/add-alias', 'App\\Controllers\\TeamController', 'addAlias');
$router->addWorkspace('POST', '/teams/remove-alias', 'App\\Controllers\\TeamController', 'removeAlias');

// Message routes
$router->addWorkspace('GET', '/messages', 'App\\Controllers\\MessageController', 'index');
$router->addWorkspace('POST', '/messages/send', 'App\\Controllers\\MessageController', 'send');
$router->addWorkspace('POST', '/messages/assign', 'App\\Controllers\\MessageController', 'assign');
$router->addWorkspace('POST', '/messages/retry', 'App\\Controllers\\MessageController', 'retryDelivery');
$router->addWorkspace('POST', '/messages/providers', 'App\\Controllers\\MessageController', 'createProvider');
$router->addWorkspace('POST', '/messages/providers/delete', 'App\\Controllers\\MessageController', 'deleteProvider');
$router->addWorkspace('POST', '/messages/test-connection', 'App\\Controllers\\MessageController', 'testConnection');

// Job routes
$router->addWorkspace('GET', '/jobs', 'App\\Controllers\\JobController', 'index');
$router->addWorkspace('POST', '/jobs/retry', 'App\\Controllers\\JobController', 'retry');

// Settings routes (now read-only, provider management moved to controllers)
$router->addWorkspace('GET', '/settings', 'App\\Controllers\\SettingsController', 'index');
$router->addWorkspace('POST', '/settings', 'App\\Controllers\\SettingsController', 'save');

// Repository routes with provider management
$router->addWorkspace('GET', '/repositories', 'App\\Controllers\\RepositoryController', 'index');
$router->addWorkspace('POST', '/repositories/providers', 'App\\Controllers\\RepositoryController', 'createProvider');
$router->addWorkspace('POST', '/repositories/providers/delete', 'App\\Controllers\\RepositoryController', 'deleteProvider');
$router->addWorkspace('POST', '/repositories/test-connection', 'App\\Controllers\\RepositoryController', 'testConnection');
$router->addWorkspace('GET', '/api/repositories/access', 'App\\Controllers\\RepositoryController', 'getAccess');
$router->addWorkspace('POST', '/api/repositories/access', 'App\\Controllers\\RepositoryController', 'setAccess');
$router->addWorkspace('GET', '/api/repositories/commits', 'App\\Controllers\\RepositoryController', 'getCommits');

// Calendar routes with provider management
$router->addWorkspace('GET', '/calendars', 'App\\Controllers\\CalendarController', 'index');
$router->addWorkspace('POST', '/calendars/providers', 'App\\Controllers\\CalendarController', 'createProvider');
$router->addWorkspace('POST', '/calendars/providers/delete', 'App\\Controllers\\CalendarController', 'deleteProvider');
$router->addWorkspace('POST', '/calendars/test-connection', 'App\\Controllers\\CalendarController', 'testConnection');

// Secrets routes with provider management
$router->addWorkspace('GET', '/secrets', 'App\\Controllers\\SecretsController', 'index');
$router->addWorkspace('POST', '/secrets/providers', 'App\\Controllers\\SecretsController', 'createProvider');
$router->addWorkspace('POST', '/secrets/providers/delete', 'App\\Controllers\\SecretsController', 'deleteProvider');
$router->addWorkspace('POST', '/secrets/test-connection', 'App\\Controllers\\SecretsController', 'testConnection');
$router->addWorkspace('POST', '/secrets/assign', 'App\\Controllers\\SecretsController', 'assignEmployee');
$router->addWorkspace('POST', '/secrets/unassign', 'App\\Controllers\\SecretsController', 'unassignEmployee');
$router->addWorkspace('GET', '/api/secrets/access', 'App\\Controllers\\SecretsController', 'getUserAccess');

// Identity routes with provider management
$router->addWorkspace('GET', '/identity', 'App\\Controllers\\IdentityController', 'index');
$router->addWorkspace('POST', '/identity/providers', 'App\\Controllers\\IdentityController', 'createProvider');
$router->addWorkspace('POST', '/identity/providers/delete', 'App\\Controllers\\IdentityController', 'deleteProvider');
$router->addWorkspace('POST', '/identity/test-connection', 'App\\Controllers\\IdentityController', 'testConnection');
$router->addWorkspace('POST', '/identity/assign', 'App\\Controllers\\IdentityController', 'assignEmployee');
$router->addWorkspace('POST', '/identity/unassign', 'App\\Controllers\\IdentityController', 'unassignEmployee');
$router->addWorkspace('POST', '/identity/sync', 'App\\Controllers\\IdentityController', 'syncUsers');
$router->addWorkspace('POST', '/api/identity/provision', 'App\\Controllers\\IdentityController', 'provisionUser');

// Other routes
$router->addWorkspace('GET', '/reports', 'App\\Controllers\\ReportsController', 'index');
$router->addWorkspace('GET', '/notifications', 'App\\Controllers\\NotificationController', 'index');
$router->addWorkspace('GET', '/audit', 'App\\Controllers\\AuditController', 'index');
$router->addWorkspace('GET', '/api-docs', 'App\\Controllers\\ApiController', 'index');

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
