<?php

namespace App\Core;

class Router
{
    /** @var array<string, array> */
    private array $routes = [];
    /** @var Middleware[] */
    private array $middlewares = [];

    public function get(string $pattern, mixed $handler): self
    {
        return $this->addRoute('GET', $pattern, $handler);
    }

    public function post(string $pattern, mixed $handler): self
    {
        return $this->addRoute('POST', $pattern, $handler);
    }

    public function put(string $pattern, mixed $handler): self
    {
        return $this->addRoute('PUT', $pattern, $handler);
    }

    public function delete(string $pattern, mixed $handler): self
    {
        return $this->addRoute('DELETE', $pattern, $handler);
    }

    public function patch(string $pattern, mixed $handler): self
    {
        return $this->addRoute('PATCH', $pattern, $handler);
    }

    public function addRoute(string $method, string $pattern, mixed $handler): self
    {
        $key = "$method $pattern";
        $this->routes[$key] = [
            'method' => $method,
            'pattern' => $pattern,
            'regex' => $this->patternToRegex($pattern),
            'handler' => $handler,
        ];
        return $this;
    }

    public function use(Middleware $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) {
                continue;
            }

            if (preg_match($route['regex'], $request->uri, $matches)) {
                // Extract named parameters
                foreach ($matches as $key => $value) {
                    if (!is_numeric($key)) {
                        $request->setAttribute($key, $value);
                    }
                }

                // Execute middleware pipeline
                $this->executeMiddlewareChain(
                    $request,
                    fn() => $this->callHandler($route['handler'], $request)
                );
                return;
            }
        }

        // No route found
        Response::notFound('Endpoint not found')->send();
    }

    private function patternToRegex(string $pattern): string
    {
        // Convert /api/v1/categories/{id} to /api/v1/categories/(?P<id>\d+)
        $regex = preg_replace_callback(
            '/{(\w+)(?::([^}]+))?}/',
            function ($matches) {
                $name = $matches[1];
                $constraint = $matches[2] ?? '\d+';
                return "(?P<$name>$constraint)";
            },
            $pattern
        );

        return "#^$regex$#";
    }

    private function executeMiddlewareChain(Request $request, callable $handler): void
    {
        $middlewares = array_reverse($this->middlewares);

        $chain = $handler;
        foreach ($middlewares as $middleware) {
            $currentChain = $chain;
            $chain = fn() => $middleware->handle($request, $currentChain);
        }

        $chain();
    }

    private function callHandler(mixed $handler, Request $request): void
    {
        if ($handler instanceof \Closure) {
            $handler($request);
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$controller, $method] = $handler;
            $instance = new $controller();
            $instance->$method($request);
            return;
        }

        throw new \RuntimeException('Invalid handler type');
    }
}
