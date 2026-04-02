<?php

namespace App\DTOs;

use App\Models\Category;

final class CategoryCollectionDTO
{
    /** @var CategoryDTO[] */
    public readonly array $items;

    public readonly int $total;
    public readonly int $page;
    public readonly int $perPage;
    public readonly int $totalPages;

    /**
     * @param Category[] $categories
     */
    public function __construct(
        array $categories,
        int   $total,
        int   $page,
        int   $perPage,
    ) {
        $this->items      = array_map(
            fn(Category $c) => CategoryDTO::fromEntity($c),
            $categories
        );
        $this->total      = $total;
        $this->page       = $page;
        $this->perPage    = $perPage;
        $this->totalPages = (int) ceil($total / $perPage);
    }

    /** @return array[] */
    public function toDataArray(): array
    {
        return array_map(fn(CategoryDTO $dto) => $dto->toArray(), $this->items);
    }

    public function toMetaArray(): array
    {
        return [
            'total'       => $this->total,
            'page'        => $this->page,
            'per_page'    => $this->perPage,
            'total_pages' => $this->totalPages,
        ];
    }
}
