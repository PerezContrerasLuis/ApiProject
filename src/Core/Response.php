<?php

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers = ['Content-Type' => 'application/json; charset=utf-8'];
    private array $data = [];

    public function __construct(array $data = [], int $statusCode = 200)
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
    }

    public static function success(array $data, array $meta = null, int $statusCode = 200): self
    {
        return new self([
            'status' => 'success',
            'data' => $data,
            'meta' => $meta,
        ], $statusCode);
    }

    public static function error(string $message, int $statusCode = 400): self
    {
        return new self([
            'status' => 'error',
            'message' => $message,
        ], $statusCode);
    }

    public static function notFound(string $message = 'Not Found'): self
    {
        return self::error($message, 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return self::error($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return self::error($message, 403);
    }

    public static function validationError(array $errors, int $statusCode = 422): self
    {
        return new self([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors,
        ], $statusCode);
    }

    public function withHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        echo json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
