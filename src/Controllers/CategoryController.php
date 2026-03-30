<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\CategoryService;

class CategoryController
{
    private CategoryService $service;

    public function __construct()
    {
        $this->service = new CategoryService();
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

        Response::success($result['data'], $result['meta'])->send();
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

        Response::success([$category->toArray()])->send();
    }
}
