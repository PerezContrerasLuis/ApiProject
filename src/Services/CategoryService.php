<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;

class CategoryService
{
    private CategoryRepository $repository;

    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all categories
     *
     * @return Category[]
     */
    public function getAllCategories(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Get category by ID
     */
    public function getCategoryById(int $id): ?Category
    {
        return $this->repository->findById($id);
    }

    /**
     * Get categories with pagination
     *
     * @return array{data: Category[], total: int, page: int, perPage: int}
     */
    public function getCategoriesPaginated(int $page = 1, int $perPage = 10): array
    {
        $result = $this->repository->paginate($page, $perPage);

        return [
            'data'    => $result['data'],
            'total'   => $result['total'],
            'page'    => $page,
            'perPage' => $perPage,
        ];
    }
}
