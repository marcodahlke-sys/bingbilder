<?php

declare(strict_types=1);

class TagRepository
{
    public function __construct(private Database $db)
    {
    }

    public function forImage(int $id): array
    {
        $stmt = $this->db->pdo()->prepare('SELECT * FROM tags WHERE bid = :id ORDER BY tag ASC');
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }

    public function grouped(bool $orphanOnly = false): array
    {
        if ($orphanOnly) {
            $sql = 'SELECT t.id, t.tag FROM tags t LEFT JOIN vup_dateien d ON d.id = t.bid WHERE d.id IS NULL ORDER BY t.tag ASC';
            return $this->db->pdo()->query($sql)->fetchAll();
        }
        return $this->db->pdo()->query('SELECT id, tag FROM tags ORDER BY tag ASC')->fetchAll();
    }
}
