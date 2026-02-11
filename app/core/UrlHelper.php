<?php

namespace App\Core;

/**
 * URL Utility Class
 * Standardizes all redirects, links, form posts, and URL generation
 */
class UrlHelper
{
    /**
     * Get the base URL for the application
     */
    public static function getBaseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . $host;
    }

    /**
     * Get the current workspace tenant ID from URL or session
     */
    public static function getCurrentTenantId(): ?string
    {
        // Try to get from current URL first
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (preg_match('#^/workspace/([^/]+)#', $uri, $matches)) {
            return $matches[1];
        }
        
        // Fallback to session
        return $_SESSION['workspace_tenant_id'] ?? null;
    }

    /**
     * Generate a workspace-aware URL
     */
    public static function workspace(string $path = '/', ?string $tenantId = null): string
    {
        $tenantId = $tenantId ?? self::getCurrentTenantId();
        
        if (!$tenantId) {
            throw new \Exception('Tenant ID is required for workspace URLs');
        }
        
        // Ensure path starts with /
        $path = '/' . ltrim($path, '/');
        
        return "/workspace/{$tenantId}{$path}";
    }

    /**
     * Generate a regular (non-workspace) URL
     */
    public static function url(string $path = '/'): string
    {
        // Ensure path starts with /
        return '/' . ltrim($path, '/');
    }

    /**
     * Generate a full absolute URL
     */
    public static function absoluteUrl(string $path = '/'): string
    {
        return self::getBaseUrl() . self::url($path);
    }

    /**
     * Generate a workspace absolute URL
     */
    public static function absoluteWorkspaceUrl(string $path = '/', ?string $tenantId = null): string
    {
        return self::getBaseUrl() . self::workspace($path, $tenantId);
    }

    /**
     * Perform a redirect
     */
    public static function redirect(string $url, int $statusCode = 302): void
    {
        // Clean any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set the redirect header
        header("Location: {$url}", true, $statusCode);
        
        // Ensure no further code executes
        exit();
    }

    /**
     * Redirect to a workspace URL
     */
    public static function redirectToWorkspace(string $path = '/', ?string $tenantId = null, int $statusCode = 302): void
    {
        self::redirect(self::workspace($path, $tenantId), $statusCode);
    }

    /**
     * Redirect to a regular URL
     */
    public static function redirectToUrl(string $path = '/', int $statusCode = 302): void
    {
        self::redirect(self::url($path), $statusCode);
    }

    /**
     * Generate HTML for a link
     */
    public static function link(string $text, string $url, array $attributes = []): string
    {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
        
        return '<a href="' . htmlspecialchars($url) . '"' . $attrs . '>' . htmlspecialchars($text) . '</a>';
    }

    /**
     * Generate HTML for a workspace link
     */
    public static function workspaceLink(string $text, string $path = '/', array $attributes = [], ?string $tenantId = null): string
    {
        return self::link($text, self::workspace($path, $tenantId), $attributes);
    }

    /**
     * Generate HTML for a form with proper action URL
     */
    public static function formStart(string $action, string $method = 'POST', array $attributes = []): string
    {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
        
        return '<form action="' . htmlspecialchars($action) . '" method="' . strtoupper($method) . '"' . $attrs . '>';
    }

    /**
     * Generate HTML for a workspace form
     */
    public static function workspaceFormStart(string $path, string $method = 'POST', array $attributes = [], ?string $tenantId = null): string
    {
        return self::formStart(self::workspace($path, $tenantId), $method, $attributes);
    }

    /**
     * Generate form end tag
     */
    public static function formEnd(): string
    {
        return '</form>';
    }

    /**
     * Generate CSRF token input field
     */
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Generate or get CSRF token
     */
    public static function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Add query parameters to a URL
     */
    public static function withQuery(string $url, array $params): string
    {
        if (empty($params)) {
            return $url;
        }
        
        $query = http_build_query($params);
        $separator = strpos($url, '?') !== false ? '&' : '?';
        
        return $url . $separator . $query;
    }

    /**
     * Get current URL with query parameters
     */
    public static function currentUrl(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    /**
     * Check if current URL matches a pattern
     */
    public static function isCurrentUrl(string $pattern): bool
    {
        $current = self::currentUrl();
        return fnmatch($pattern, $current);
    }

    /**
     * Generate a URL with flash message parameters
     */
    public static function withFlash(string $url, string $message, string $type = 'info'): string
    {
        return self::withQuery($url, [
            'flash_message' => $message,
            'flash_type' => $type
        ]);
    }

    /**
     * Set flash message in session (for redirects)
     */
    public static function setFlash(string $message, string $type = 'info'): void
    {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }

    /**
     * Get and clear flash message from session
     */
    public static function getFlash(): ?array
    {
        if (isset($_SESSION['flash_message'])) {
            $flash = [
                'message' => $_SESSION['flash_message'],
                'type' => $_SESSION['flash_type'] ?? 'info'
            ];
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
            return $flash;
        }
        return null;
    }
}