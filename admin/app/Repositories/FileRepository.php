<?php

declare(strict_types=1);

class FileRepository
{
    public function __construct(private Database $db)
    {
    }

    public function latest(int $limit = 12): array
    {
        $stmt = $this->db->pdo()->prepare('SELECT d.*, k.name AS category_name FROM vup_dateien d LEFT JOIN vup_kategorien k ON k.id = d.to_kat ORDER BY CAST(d.entrytime AS UNSIGNED) DESC, d.id DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->pdo()->prepare('SELECT d.*, k.name AS category_name FROM vup_dateien d LEFT JOIN vup_kategorien k ON k.id = d.to_kat WHERE d.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function forDay(int $start, int $end): array
    {
        $stmt = $this->db->pdo()->prepare('SELECT d.*, k.name AS category_name FROM vup_dateien d LEFT JOIN vup_kategorien k ON k.id = d.to_kat WHERE CAST(d.entrytime AS UNSIGNED) BETWEEN :start AND :end ORDER BY CAST(d.entrytime AS UNSIGNED) ASC, d.id ASC');
        $stmt->execute(['start' => $start, 'end' => $end]);
        return $stmt->fetchAll();
    }

    public function forMonth(int $start, int $end): array
    {
        $stmt = $this->db->pdo()->prepare('SELECT id, entrytime, name FROM vup_dateien WHERE CAST(entrytime AS UNSIGNED) BETWEEN :start AND :end ORDER BY CAST(entrytime AS UNSIGNED) ASC, id ASC');
        $stmt->execute(['start' => $start, 'end' => $end]);
        return $stmt->fetchAll();
    }

    public function topDownloads(int $limit = 100): array
    {
        $stmt = $this->db->pdo()->prepare('SELECT * FROM vup_dateien ORDER BY downloads DESC, id DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function paginated(int $offset, int $limit): array
    {
        $stmt = $this->db->pdo()->prepare('SELECT d.*, k.name AS category_name, EXISTS(SELECT 1 FROM tags t WHERE t.bid = d.id LIMIT 1) AS has_tags FROM vup_dateien d LEFT JOIN vup_kategorien k ON k.id = d.to_kat ORDER BY CAST(d.entrytime AS UNSIGNED) DESC, d.id DESC LIMIT :offset, :limit');
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->db->pdo()->query('SELECT COUNT(*) FROM vup_dateien')->fetchColumn();
    }

    public function insertFromTemp(string $filename, int $timestamp, string $size, int $categoryId, array $flags): void
    {
        $columns = ['entrytime', 'name', 'size', 'ordner', 'to_kat', 'downloads'];
        $values = [':entrytime', ':name', ':size', ':ordner', ':to_kat', ':downloads'];
        $params = ['entrytime' => (string) $timestamp, 'name' => $filename, 'size' => $size, 'ordner' => '', 'to_kat' => $categoryId, 'downloads' => 0];

        foreach (['k1','k3','k4','k7','k9','k10','k13','k15'] as $flag) {
            $columns[] = $flag;
            $values[] = ':' . $flag;
            $params[$flag] = !empty($flags[$flag]) ? '1' : '0';
        }

        $sql = 'INSERT INTO vup_dateien (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ')';
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute($params);
    }
}
