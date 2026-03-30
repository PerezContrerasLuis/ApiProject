<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

class CorsMiddleware implements Middleware
{
    private string $allowedOrigin;

    public function __construct(string $allowedOrigin = '*')
    {
        $this->allowedOrigin = $allowedOrigin;
    }

    public function handle(Request $request, callable $next): void
    {
        // Handle preflight requests
        if ($request->method === 'OPTIONS') {
            $this->sendPreflight();
        }

        // Add CORS headers to response
        header("Access-Control-Allow-Origin: {$this->allowedOrigin}");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');

        $next();
    }

    private function sendPreflight(): void
    {
        header("Access-Control-Allow-Origin: {$this->allowedOrigin}");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');
        http_response_code(204);
        exit;
    }
}
