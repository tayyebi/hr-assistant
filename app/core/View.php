<?php
/**
 * View Helper Class
 */
class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data);
        
        $viewPath = __DIR__ . '/../views/pages/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("View not found: {$view}");
        }
        
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        
        include __DIR__ . '/../views/layouts/main.php';
    }

    public static function renderWithoutLayout(string $view, array $data = []): void
    {
        extract($data);
        
        $viewPath = __DIR__ . '/../views/pages/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("View not found: {$view}");
        }
        
        include $viewPath;
    }

    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    public static function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
