<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;

class CategoryService
{
    private CategoryRepository $repository;

    public function __construct()
    {
        $this->repository = new CategoryRepository();
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
     * @return array{data: Category[], meta: array}
     */
    public function getCategoriesPaginated(int $page = 1, int $perPage = 10): array
    {
        $result = $this->repository->paginate($page, $perPage);

        return [
            'data' => array_map(fn(Category $cat) => $cat->toArray(), $result['data']),
            'meta' => [
                'total' => $result['total'],
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($result['total'] / $perPage),
            ],
        ];
    }
}
