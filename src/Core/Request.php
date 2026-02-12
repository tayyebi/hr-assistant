<?php
/**
 * HTTP request abstraction.
 */

declare(strict_types=1);

namespace Src\Core;

final class Request
{
    private function __construct(
        public readonly string $method,
        public readonly string $uriPath,
        public readonly string $host,
        public readonly array $query,
        public readonly array $post,
        public readonly array $server,
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $uriPath = (string)parse_url($requestUri, PHP_URL_PATH);
        if ($uriPath === '') {
            $uriPath = '/';
        }

        $host = (string)($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? ''));
        $host = strtolower(trim(explode(':', $host)[0] ?? ''));

        return new self(
            $method,
            $uriPath,
            $host,
            $_GET ?? [],
            $_POST ?? [],
            $_SERVER ?? [],
        );
    }

    public function ip(): string
    {
        return (string)($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function userAgent(): string
    {
        return (string)($this->server['HTTP_USER_AGENT'] ?? '');
    }
}
