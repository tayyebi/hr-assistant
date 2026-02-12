<?php
/**
 * PHP template renderer.
 * Templates live in src/Views/. Layouts via $layout variable in templates.
 */

declare(strict_types=1);

namespace Src\Core;

final class View
{
    private string $viewsDir;

    public function __construct(
        private readonly Tenant $tenant,
        private readonly Auth $auth,
    ) {
        $this->viewsDir = dirname(__DIR__) . '/Views';
    }

    public function render(string $template, array $data = []): string
    {
        $data['tenant'] = $this->tenant;
        $data['auth'] = $this->auth;
        $data['prefix'] = $this->tenant->pathPrefix();

        $templateFile = $this->viewsDir . '/' . $template . '.php';
        if (!is_file($templateFile)) {
            return '<!-- template not found: ' . htmlspecialchars($template) . ' -->';
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $templateFile;
        $content = ob_get_clean();

        if (isset($layout)) {
            $layoutFile = $this->viewsDir . '/layouts/' . $layout . '.php';
            if (is_file($layoutFile)) {
                $title = $title ?? 'HCMS';
                ob_start();
                require $layoutFile;
                return ob_get_clean();
            }
        }

        return $content;
    }
}
