<?php
/**
 * HTTP response helper.
 */

declare(strict_types=1);

namespace Src\Core;

final class Response
{
    private int $statusCode = 200;
    private array $headers = [];

    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function html(string $body): void
    {
        $this->header('Content-Type', 'text/html; charset=utf-8');
        $this->sendHeaders();
        echo $body;
    }

    public function text(string $body): void
    {
        $this->header('Content-Type', 'text/plain; charset=utf-8');
        $this->sendHeaders();
        echo $body;
    }

    public function json(mixed $data, int $status = 200): void
    {
        $this->status($status);
        $this->header('Content-Type', 'application/json; charset=utf-8');
        $this->sendHeaders();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function redirect(string $url, int $status = 302): void
    {
        $this->status($status);
        $this->header('Location', $url);
        $this->sendHeaders();
    }

    private function sendHeaders(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
    }
}
