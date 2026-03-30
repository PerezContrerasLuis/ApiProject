<?php

namespace App\Models;

class Category
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?int $parent_id,
    ) {}

    /**
     * Create Category from database row
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['id'],
            name: (string)$data['name'],
            slug: (string)$data['slug'],
            parent_id: $data['parent_id'] ? (int)$data['parent_id'] : null,
        );
    }

    /**
     * Convert to array for serialization
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'parent_id' => $this->parent_id,
        ];
    }
}
