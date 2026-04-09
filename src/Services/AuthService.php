<?php

namespace App\Services;

use App\Core\JwtService;
use App\DTOs\UserDTO;
use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $repository;
    private JwtService $jwtService;

    public function __construct(UserRepository $repository, JwtService $jwtService)
    {
        $this->repository = $repository;
        $this->jwtService = $jwtService;
    }

    public function login(string $email, string $password): array
    {
        $user = $this->repository->findByEmail($email);

        if (!$user || !password_verify($password, $user->passwordHash)) {
            return [
                'success' => false,
                'message' => 'Invalid credentials',
            ];
        }

        $token = $this->jwtService->generate($user->id, $user->role);
        $userDTO = UserDTO::fromEntity($user);

        return [
            'success' => true,
            'token' => $token,
            'user' => $userDTO->toArray(),
        ];
    }
}
