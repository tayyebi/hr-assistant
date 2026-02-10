<?php

namespace App\Core;

/**
 * Workspace Router for PHP MVC with Tenant Support
 */
class Router
{
    private array $routes = [];
    private array $workspaceRoutes = [];

    public function add(string $method, string $path, string $controller, string $action): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function addWorkspace(string $method, string $path, string $controller, string $action): void
    {
        $this->workspaceRoutes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        // Remove trailing slash if present (except for root)
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        // Try workspace routes first (pattern: /workspace/{tenantId}/...)
        if (preg_match('#^/workspace/([^/]+)(/.*)?$#', $uri, $matches)) {
            $tenantId = $matches[1];
            $workspacePath = isset($matches[2]) ? $matches[2] : '/';
            
            // Set tenant context in session for this request
            $_SESSION['workspace_tenant_id'] = $tenantId;
            
            foreach ($this->workspaceRoutes as $route) {
                if ($route['method'] === $method && $route['path'] === $workspacePath) {
                    $controller = new $route['controller']();
                    $action = $route['action'];
                    $controller->$action();
                    return;
                }
            }
            
            // No workspace route matched
            http_response_code(404);
            echo '<h1>404 - Workspace Page Not Found</h1>';
            return;
        }

        // Try regular routes
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                $controller = new $route['controller']();
                $action = $route['action'];
                $controller->$action();
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo '<h1>404 - Page Not Found</h1>';
    }
}
