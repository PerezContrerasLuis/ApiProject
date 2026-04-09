<?php

namespace App\Factories;

use App\Repositories\CategoryRepository;
use App\Repositories\UserRepository;

class RepositoryFactory
{
    public static function makeCategory(): CategoryRepository
    {
        return new CategoryRepository();
    }

    public static function makeUser(): UserRepository
    {
        return new UserRepository();
    }
}
