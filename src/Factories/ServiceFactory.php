<?php

namespace App\Factories;

use App\Core\JwtService;
use App\Services\AuthService;
use App\Services\CategoryService;

class ServiceFactory
{
    public static function makeCategory(): CategoryService
    {
        $repository = RepositoryFactory::makeCategory();
        return new CategoryService($repository);
    }

    public static function makeAuth(): AuthService
    {
        return new AuthService(
            RepositoryFactory::makeUser(),
            new JwtService(),
        );
    }
}
