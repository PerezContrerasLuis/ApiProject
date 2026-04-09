<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\JwtService;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class AuthMiddleware implements Middleware
{
    private JwtService $jwtService;

    private array $publicRoutes = [
        '/test',
        '/api/v1/auth/login',
    ];

    public function __construct()
    {
        $this->jwtService = new JwtService();
    }

    public function handle(Request $request, callable $next): void
    {
        // Skip auth validation for public routes
        if (in_array($request->uri, $this->publicRoutes, true)) {
            $next();
            return;
        }

        $authHeader = $request->header('Authorization');

        if (empty($authHeader)) {
            Response::unauthorized('Missing authorization token')->send();
            return;
        }

        if (strpos($authHeader, 'Bearer ') !== 0) {
            Response::unauthorized('Invalid authorization header format')->send();
            return;
        }

        $token = substr($authHeader, 7);

        try {
            $decoded = $this->jwtService->decode($token);
            $request->setAttribute('user', $decoded);
            $next();
        } catch (ExpiredException $e) {
            Response::unauthorized('Token expired')->send();
        } catch (SignatureInvalidException $e) {
            Response::unauthorized('Invalid token signature')->send();
        } catch (\Exception $e) {
            Response::unauthorized('Invalid token')->send();
        }
    }
}
