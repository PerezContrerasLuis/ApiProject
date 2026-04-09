<?php

use App\Core\Request;
use App\Core\Router;
use App\Core\Response;
use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Middleware\CorsMiddleware;
use App\Middleware\AuthMiddleware;

$router = new Router();

// Register middleware
$router->use(new CorsMiddleware());
$router->use(new AuthMiddleware());

// Routes
$router->get('/test', function (Request $request) {
    Response::success([
        'message' => 'Welcome to ApiProject',
        'version' => '1.0.0',
    ])->send();
});

// Auth
$router->post('/api/v1/auth/login', [AuthController::class, 'login']);

// Categories
$router->get('/api/v1/categories', [CategoryController::class, 'index']);
$router->get('/api/v1/categories/{id:\d+}', [CategoryController::class, 'show']);

// Dispatch the request
$request = new Request();
$router->dispatch($request);
