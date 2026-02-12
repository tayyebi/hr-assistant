<?php
/**
 * URL router with path-parameter support and workspace-prefix awareness.
 * Routes registered as /dashboard will match /w/{slug}/dashboard when
 * tenant is resolved via prefix. Domain-resolved tenants match as-is.
 *
 * Path parameters: /employees/{id} captures $params['id'].
 */

declare(strict_types=1);

namespace Src\Core;

final class Router
{
    private array $routes = [];

    public function __construct(
        private readonly Request $request,
        private readonly Response $response,
        private readonly Tenant $tenant,
        private readonly Auth $auth,
        private readonly View $view,
        private readonly Database $db,
    ) {
    }

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][] = ['pattern' => $path, 'handler' => $handler];
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][] = ['pattern' => $path, 'handler' => $handler];
    }

    public function response(): Response
    {
        return $this->response;
    }

    public function view(): View
    {
        return $this->view;
    }

    public function auth(): Auth
    {
        return $this->auth;
    }

    public function tenant(): Tenant
    {
        return $this->tenant;
    }

    public function db(): Database
    {
        return $this->db;
    }

    public function registerCoreRoutes(): void
    {
        $this->get('/healthz', function (): void {
            $this->response->text('ok');
        });

        $this->get('/login', function (): void {
            if ($this->auth->isLoggedIn()) {
                $this->response->redirect('/');
                return;
            }
            $this->response->html($this->view->render('auth/login', [
                'title' => 'Login',
                'layout' => 'minimal',
            ]));
        });

        $this->post('/login', function (): void {
            $email = trim((string)($this->request->post['email'] ?? ''));
            $password = (string)($this->request->post['password'] ?? '');
            if ($this->auth->attempt($email, $password)) {
                AuditLog::record('user.login');
                $this->response->redirect('/');
            } else {
                $this->response->html($this->view->render('auth/login', [
                    'title' => 'Login',
                    'layout' => 'minimal',
                    'error' => 'Invalid credentials.',
                ]));
            }
        });

        $this->get('/logout', function (): void {
            AuditLog::record('user.logout');
            $this->auth->logout();
            $this->response->redirect('/login');
        });

        $this->get('/', function (): void {
            if (!$this->auth->requireLogin($this->response)) {
                return;
            }
            if ($this->auth->isSystemAdmin() && !$this->tenant->isResolved()) {
                $this->response->html($this->view->render('admin/dashboard', [
                    'title' => 'System Admin',
                    'layout' => 'admin',
                    'tenants' => $this->db->fetchAll('SELECT * FROM tenants ORDER BY name'),
                ]));
                return;
            }
            if (!$this->tenant->isResolved()) {
                $tenants = $this->auth->userTenants();
                if (count($tenants) === 1) {
                    $this->response->redirect('/w/' . $tenants[0]['slug'] . '/dashboard');
                    return;
                }
                $this->response->html($this->view->render('workspace/select', [
                    'title' => 'Select Workspace',
                    'layout' => 'minimal',
                    'tenants' => $tenants,
                ]));
                return;
            }
            $this->response->redirect($this->tenant->pathPrefix() . '/dashboard');
        });

        $this->get('/dashboard', function (): void {
            if (!$this->auth->requireLogin($this->response)) {
                return;
            }
            $pluginManager = null;
            foreach ($GLOBALS as $k => $v) {
                if ($v instanceof PluginManager) {
                    $pluginManager = $v;
                    break;
                }
            }
            $sidebarItems = $pluginManager ? $pluginManager->sidebarItems() : [];
            $this->response->html($this->view->render('workspace/dashboard', [
                'title' => 'Dashboard',
                'layout' => 'app',
                'sidebarItems' => $sidebarItems,
            ]));
        });

        $this->get('/employees', function (): void {
            if (!$this->auth->requireRole($this->response, 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $employees = $this->db->tenantFetchAll('employees');
            $this->response->html($this->view->render('employees/index', [
                'title' => 'Employees',
                'layout' => 'app',
                'employees' => $employees,
            ]));
        });

        $this->post('/employees', function (): void {
            if (!$this->auth->requireRole($this->response, 'workspace_admin', 'hr_specialist')) {
                return;
            }
            $hireDate = trim((string)($this->request->post['hire_date'] ?? ''));
            $data = [
                'first_name' => trim((string)($this->request->post['first_name'] ?? '')),
                'last_name'  => trim((string)($this->request->post['last_name'] ?? '')),
                'employee_code' => trim((string)($this->request->post['employee_code'] ?? '')),
                'position'   => trim((string)($this->request->post['position'] ?? '')),
                'department' => trim((string)($this->request->post['department'] ?? '')),
                'hire_date'  => $hireDate !== '' ? $hireDate : null,
            ];
            $id = $this->db->tenantInsert('employees', $data);
            AuditLog::record('employee.created', 'employee', (int)$id, null, json_encode($data));
            $this->response->redirect($this->tenant->pathPrefix() . '/employees');
        });

        $this->get('/settings', function (): void {
            if (!$this->auth->requireRole($this->response, 'workspace_admin')) {
                return;
            }
            $settings = $this->db->fetchAll(
                'SELECT * FROM plugin_settings WHERE tenant_id = ? ORDER BY plugin_name, `key`',
                [$this->tenant->id()],
            );
            $this->response->html($this->view->render('settings/index', [
                'title' => 'Settings',
                'layout' => 'app',
                'settings' => $settings,
            ]));
        });

        $this->get('/admin/tenants', function (): void {
            if (!$this->auth->isSystemAdmin()) {
                $this->response->status(403)->html('<h1>403</h1>');
                return;
            }
            $tenants = $this->db->fetchAll('SELECT * FROM tenants ORDER BY name');
            $this->response->html($this->view->render('admin/tenants', [
                'title' => 'Tenants',
                'layout' => 'admin',
                'tenants' => $tenants,
            ]));
        });

        $this->post('/admin/tenants', function (): void {
            if (!$this->auth->isSystemAdmin()) {
                $this->response->status(403)->html('<h1>403</h1>');
                return;
            }
            $name = trim((string)($this->request->post['name'] ?? ''));
            $slug = trim((string)($this->request->post['slug'] ?? ''));
            $domain = trim((string)($this->request->post['domain'] ?? '')) ?: null;
            $this->db->query(
                'INSERT INTO tenants (name, slug, domain) VALUES (?, ?, ?)',
                [$name, $slug, $domain],
            );
            AuditLog::record('tenant.created', 'tenant', (int)$this->db->lastInsertId());
            $this->response->redirect('/admin/tenants');
        });

        $this->get('/admin/audit', function (): void {
            if (!$this->auth->isSystemAdmin()) {
                $this->response->status(403)->html('<h1>403</h1>');
                return;
            }
            $logs = $this->db->fetchAll(
                'SELECT al.*, u.display_name, u.email FROM audit_logs al '
                . 'LEFT JOIN users u ON u.id = al.user_id '
                . 'ORDER BY al.created_at DESC LIMIT 200'
            );
            $this->response->html($this->view->render('admin/audit', [
                'title' => 'Audit Log',
                'layout' => 'admin',
                'logs' => $logs,
            ]));
        });

        $this->get('/admin/audit/{id}', function (array $p): void {
            if (!$this->auth->isSystemAdmin()) {
                $this->response->status(403)->html('<h1>403</h1>');
                return;
            }
            $id = (int)($p['id'] ?? 0);
            $log = $this->db->fetchOne('SELECT al.*, u.display_name, u.email FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id WHERE al.id = ?', [$id]);
            if (!$log) { $this->response->status(404)->html('<h1>Not found</h1>'); return; }
            $this->response->html($this->view->render('admin/audit_detail', [
                'title' => 'Audit Detail', 'layout' => 'admin', 'log' => $log,
            ]));
        });
    }

    public function dispatch(): void
    {
        $method = $this->request->method;
        $path = $this->request->uriPath;

        // Global trailing-slash policy: redirect GET requests for non-file paths
        // do not apply trailing-slash redirect to healthcheck or auth endpoints
        $noSlashRedirectExceptions = ['/healthz', '/login', '/logout'];
        if ($method === 'GET' && $path !== '/' && substr($path, -1) !== '/' && strpos($path, '.') === false && !in_array($path, $noSlashRedirectExceptions, true)) {
            $qs = $this->request->server['QUERY_STRING'] ?? '';
            $loc = $path . '/';
            if ($qs !== '') { $loc .= '?' . $qs; }
            $this->response->status(301)->redirect($loc);
            return;
        }

        $prefix = $this->tenant->pathPrefix();
        if ($prefix !== '' && str_starts_with($path, $prefix)) {
            $path = substr($path, strlen($prefix));
            if ($path === '' || $path === false) {
                $path = '/';
            }
        }

        // normalize trailing slash for matching (routes are registered without trailing slash)
        if ($path !== '/') {
            $path = rtrim($path, '/');
            if ($path === '') { $path = '/'; }
        }

        $routes = $this->routes[$method] ?? [];
        foreach ($routes as $route) {
            $params = $this->matchRoute($route['pattern'], $path);
            if ($params !== null) {
                ($route['handler'])($params);
                return;
            }
        }

        $this->response->status(404)->html($this->view->render('errors/404', [
            'title' => 'Not Found',
            'layout' => 'minimal',
        ]));
    }

    private function matchRoute(string $pattern, string $path): ?array
    {
        if ($pattern === $path) {
            return [];
        }

        $patternParts = explode('/', trim($pattern, '/'));
        $pathParts = explode('/', trim($path, '/'));

        if (count($patternParts) !== count($pathParts)) {
            return null;
        }

        $params = [];
        foreach ($patternParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $paramName = substr($part, 1, -1);
                $params[$paramName] = $pathParts[$i];
                continue;
            }
            if ($part !== $pathParts[$i]) {
                return null;
            }
        }

        return $params;
    }
}
