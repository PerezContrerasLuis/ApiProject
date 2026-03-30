<?php

namespace App\Core;

class Request
{
    public readonly string $method;
    public readonly string $uri;
    public readonly array $queryParams;
    public readonly array $headers;
    private ?string $cachedBody = null;
    private ?array $cachedParsedBody = null;
    public array $attributes = [];

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->queryParams = $_GET;
        $this->headers = $this->parseHeaders();
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('HTTP_', '', $key);
                $header = str_replace('_', '-', $header);
                $headers[strtolower($header)] = $value;
            }
        }
        return $headers;
    }

    public function body(): string
    {
        if ($this->cachedBody === null) {
            $this->cachedBody = file_get_contents('php://input');
        }
        return $this->cachedBody;
    }

    public function json(): array
    {
        if ($this->cachedParsedBody === null) {
            $decoded = json_decode($this->body(), true);
            $this->cachedParsedBody = is_array($decoded) ? $decoded : [];
        }
        return $this->cachedParsedBody;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if ($this->isJson()) {
            return $this->json()[$key] ?? $default;
        }
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function isJson(): bool
    {
        return strpos($this->headers['content-type'] ?? '', 'application/json') !== false;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
}
