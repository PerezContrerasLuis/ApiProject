<?php

namespace App\DTOs;

use App\Models\Category;

final class CategoryDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $name,
        public readonly string  $slug,
        public readonly ?int    $parent_id,
    ) {}

    public static function fromEntity(Category $category): self
    {
        return new self(
            id:        $category->id,
            name:      $category->name,
            slug:      $category->slug,
            parent_id: $category->parent_id,
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'slug'      => $this->slug,
            'parent_id' => $this->parent_id,
        ];
    }
}
