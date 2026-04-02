<?php

namespace App\Factories;

use App\Services\CategoryService;

class ServiceFactory
{
    public static function makeCategory(): CategoryService
    {
        $repository = RepositoryFactory::makeCategory();
        return new CategoryService($repository);
    }
}
