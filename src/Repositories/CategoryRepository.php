<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Category;
use PDO;

class CategoryRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all categories
     *
     * @return Category[]
     */
    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT id, name, slug, parent_id FROM categories ORDER BY id ASC');
        $rows = $stmt->fetchAll();

        return array_map(fn(array $row) => Category::fromArray($row), $rows);
    }

    /**
     * Get category by ID
     */
    public function findById(int $id): ?Category
    {
        $stmt = $this->db->prepare('SELECT id, name, slug, parent_id FROM categories WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? Category::fromArray($row) : null;
    }

    /**
     * Get total count of categories
     */
    public function count(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) as total FROM categories');
        $result = $stmt->fetch();
        return (int)$result['total'];
    }

    /**
     * Get paginated categories
     *
     * @return array{data: Category[], total: int}
     */
    public function paginate(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare('
            SELECT id, name, slug, parent_id
            FROM categories
            ORDER BY id ASC
            LIMIT ? OFFSET ?
        ');
        $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        $categories = array_map(fn(array $row) => Category::fromArray($row), $rows);
        $total = $this->count();

        return [
            'data' => $categories,
            'total' => $total,
        ];
    }
}
