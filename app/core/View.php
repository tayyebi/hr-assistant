<?php
/**
 * View Helper Class
 */
class View
{
    public static function render(string $viewName, array $data = []): void
    {
        $viewPath = __DIR__ . '/../views/pages/' . $viewName . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("View not found: {$viewName}");
        }
        
        extract($data);
        
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        
        include __DIR__ . '/../views/layouts/main.php';
    }

    public static function renderWithoutLayout(string $viewName, array $data = []): void
    {
        $viewPath = __DIR__ . '/../views/pages/' . $viewName . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("View not found: {$viewName}");
        }
        
        extract($data);
        
        include $viewPath;
    }

    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Generate a workspace-aware URL
     */
    public static function workspaceUrl(string $path): string
    {
        $tenantId = $_SESSION['workspace_tenant_id'] ?? null;
        if ($tenantId) {
            return '/workspace/' . $tenantId . $path;
        }
        return $path;
    }

    /**
     * Get the current workspace tenant info for display
     */
    public static function getWorkspaceContext(): array
    {
        $tenantId = $_SESSION['workspace_tenant_id'] ?? null;
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            return [
                'isWorkspace' => true,
                'tenantId' => $tenantId,
                'tenantName' => $tenant['name'] ?? 'Unknown Workspace'
            ];
        }
        return ['isWorkspace' => false];
    }

    public static function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
