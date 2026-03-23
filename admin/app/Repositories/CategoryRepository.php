<?php

declare(strict_types=1);

class CategoryRepository
{
    public function __construct(private Database $db)
    {
    }

    public function all(): array
    {
        return $this->db->pdo()->query('SELECT id, name FROM vup_kategorien ORDER BY id ASC')->fetchAll();
    }
}
