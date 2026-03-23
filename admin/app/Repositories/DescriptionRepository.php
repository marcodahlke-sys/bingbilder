<?php

declare(strict_types=1);

class DescriptionRepository
{
    public function __construct(private Database $db)
    {
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->pdo()->prepare('SELECT * FROM beschreibung WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
