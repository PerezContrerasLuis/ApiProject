<?php

namespace App\Factories;

use App\Repositories\CategoryRepository;

class RepositoryFactory
{
    public static function makeCategory(): CategoryRepository
    {
        return new CategoryRepository();
    }
}
