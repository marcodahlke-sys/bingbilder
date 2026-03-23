<?php

declare(strict_types=1);

class LikeRepository
{
    public function __construct(private Database $db)
    {
    }

    public function top(int $limit = 100): array
    {
        $stmt = $this->db->pdo()->prepare('SELECT d.*, COUNT(l.id) AS likes_count FROM vup_dateien d INNER JOIN likes l ON l.datei_id = d.id GROUP BY d.id, d.name, d.size, d.ordner, d.entrytime, d.to_kat, d.downloads, d.k1, d.k3, d.k4, d.k7, d.k9, d.k10, d.k13, d.k15, d.aktivator, d.video, d.empty ORDER BY likes_count DESC, d.id DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
