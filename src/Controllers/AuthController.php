<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Factories\ServiceFactory;
use App\Services\AuthService;

class AuthController
{
    private AuthService $service;

    public function __construct()
    {
        $this->service = ServiceFactory::makeAuth();
    }

    public function login(Request $request): void
    {
        $body = $request->json();
        $email = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';

        // Validate email format
        if (empty($email)) {
            Response::validationError(['email' => 'Email is required'])->send();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::validationError(['email' => 'Email must be a valid email address'])->send();
        }

        // Validate password
        if (empty($password)) {
            Response::validationError(['password' => 'Password is required'])->send();
        }

        // Attempt login
        $result = $this->service->login($email, $password);

        if (!$result['success']) {
            Response::unauthorized('Invalid credentials')->send();
        }

        // Success: return token and user
        Response::success([
            [
                'token' => $result['token'],
                'user' => $result['user'],
            ]
        ])->send();
    }
}
