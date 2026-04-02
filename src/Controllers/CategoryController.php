<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\DTOs\CategoryDTO;
use App\DTOs\CategoryCollectionDTO;
use App\Factories\ServiceFactory;
use App\Services\CategoryService;

class CategoryController
{
    private CategoryService $service;

    public function __construct()
    {
        $this->service = ServiceFactory::makeCategory();
    }

    /**
     * GET /api/v1/categories
     */
    public function index(Request $request): void
    {
        $page = (int)$request->get('page', 1);
        $perPage = (int)$request->get('per_page', 10);

        // Validate pagination params
        if ($page < 1) {
            Response::validationError(['page' => 'Must be >= 1'])->send();
        }
        if ($perPage < 1 || $perPage > 100) {
            Response::validationError(['per_page' => 'Must be between 1 and 100'])->send();
        }

        $result = $this->service->getCategoriesPaginated($page, $perPage);

        $collection = new CategoryCollectionDTO(
            categories: $result['data'],
            total:      $result['total'],
            page:       $result['page'],
            perPage:    $result['perPage'],
        );

        Response::success($collection->toDataArray(), $collection->toMetaArray())->send();
    }

    /**
     * GET /api/v1/categories/{id}
     */
    public function show(Request $request): void
    {
        $id = (int)$request->getAttribute('id');

        $category = $this->service->getCategoryById($id);
        if (!$category) {
            Response::notFound('Category not found')->send();
        }

        $dto = CategoryDTO::fromEntity($category);

        Response::success([$dto->toArray()])->send();
    }
}
