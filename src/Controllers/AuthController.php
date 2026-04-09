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

    public function register(Request $request): void
    {
        // Get authenticated user from JWT (set by AuthMiddleware)
        $authUser = $request->getAttribute('user');

        // Verify user is admin
        if ($authUser->role !== 'admin') {
            Response::forbidden('Only admins can register users')->send();
        }

        $body = $request->json();
        $name = trim($body['name'] ?? '');
        $email = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';
        $role = trim($body['role'] ?? '');

        // Validate name
        if (empty($name)) {
            Response::validationError(['name' => 'Name is required'])->send();
        }

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

        // Validate role
        $validRoles = ['admin', 'manager', 'viewer'];
        if (empty($role) || !in_array($role, $validRoles, true)) {
            Response::validationError(['role' => 'Role must be one of: admin, manager, viewer'])->send();
        }

        // Attempt register
        $result = $this->service->register($name, $email, $password, $role);

        if (!$result['success']) {
            Response::conflict($result['message'])->send();
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
