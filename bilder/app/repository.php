<?php

declare(strict_types=1);

function dateOrderSql(): string
{
    return ' ORDER BY CAST(d.entrytime AS UNSIGNED) DESC, d.id DESC ';
}

function baseListSql(): string
{
    return "
        SELECT
            d.id,
            d.entrytime,
            d.name,
            d.size,
            d.ordner,
            d.to_kat,
            d.downloads,
            k.name AS category_name
        FROM vup_dateien d
        LEFT JOIN vup_kategorien k ON k.id = d.to_kat
        WHERE 1
    ";
}

function categoryFlagColumns(): array
{
    return [1, 3, 4, 7, 9, 10, 13, 15];
}

function categoryWhereSql(int $categoryId): string
{
    $parts = ['d.to_kat = :id'];

    if (in_array($categoryId, categoryFlagColumns(), true)) {
        $parts[] = 'd.k' . $categoryId . ' = 1';
        $parts[] = "d.k{$categoryId} = '1'";
    }

    return '(' . implode(' OR ', array_unique($parts)) . ')';
}

function hasBeschreibungHtmlColumn(): bool
{
    static $hasColumn = null;

    if ($hasColumn !== null) {
        return $hasColumn;
    }

    try {
        $stmt = db()->query("SHOW COLUMNS FROM beschreibung LIKE 'html'");
        $hasColumn = (bool)$stmt->fetch();
    } catch (Throwable $e) {
        $hasColumn = false;
    }

    return $hasColumn;
}

function beschreibungHtmlSelectSql(string $alias = 'b'): string
{
    if (hasBeschreibungHtmlColumn()) {
        return "COALESCE({$alias}.html, 0) AS beschreibung_html";
    }

    return "0 AS beschreibung_html";
}

function countAllImages(): int
{
    return (int)db()->query("SELECT COUNT(*) FROM vup_dateien")->fetchColumn();
}

function getFeaturedImage(): ?array
{
    $sql = baseListSql() . dateOrderSql() . ' LIMIT 1';
    $row = db()->query($sql)->fetch();

    return ($row && looksLikeImage($row)) ? $row : null;
}

function getExploreImages(int $page = 1, int $perPage = PER_PAGE): array
{
    $sql = baseListSql() . dateOrderSql() . ' LIMIT :limit OFFSET :offset';
    $stmt = db()->prepare($sql);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', offsetForPage($page, $perPage), PDO::PARAM_INT);
    $stmt->execute();

    return array_values(array_filter($stmt->fetchAll(), 'looksLikeImage'));
}

function getCategoriesLite(): array
{
    $categories = db()->query("
        SELECT
            k.id,
            k.name,
            k.zielverzeichnis
        FROM vup_kategorien k
        ORDER BY k.name ASC
    ")->fetchAll();

    foreach ($categories as &$category) {
        $categoryId = (int)$category['id'];
        $category['image_count'] = countImagesInCategory($categoryId);
        $category['preview_image'] = getCategoryPreviewImage($categoryId);
    }
    unset($category);

    return $categories;
}

function getCategoryPreviewImage(int $categoryId): ?array
{
    $sql = "
        SELECT DISTINCT
            d.id,
            d.entrytime,
            d.name,
            d.size,
            d.ordner,
            d.to_kat,
            d.downloads,
            k.name AS category_name
        FROM vup_dateien d
        LEFT JOIN vup_kategorien k ON k.id = d.to_kat
        WHERE " . categoryWhereSql($categoryId) . "
        ORDER BY RAND()
        LIMIT 1
    ";

    $stmt = db()->prepare($sql);
    $stmt->bindValue(':id', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();

    return ($row && looksLikeImage($row)) ? $row : null;
}

function getCategory(int $id): ?array
{
    $stmt = db()->prepare('SELECT id, name, zielverzeichnis FROM vup_kategorien WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function countImagesInCategory(int $categoryId): int
{
    $sql = 'SELECT COUNT(DISTINCT d.id) FROM vup_dateien d WHERE ' . categoryWhereSql($categoryId);
    $stmt = db()->prepare($sql);
    $stmt->bindValue(':id', $categoryId, PDO::PARAM_INT);
    $stmt->execute();

    return (int)$stmt->fetchColumn();
}

function getImagesInCategory(int $categoryId, int $page = 1, int $perPage = PER_PAGE): array
{
    $sql = "
        SELECT DISTINCT
            d.id,
            d.entrytime,
            d.name,
            d.size,
            d.ordner,
            d.to_kat,
            d.downloads,
            k.name AS category_name
        FROM vup_dateien d
        LEFT JOIN vup_kategorien k ON k.id = d.to_kat
        WHERE " . categoryWhereSql($categoryId) . "
        ORDER BY CAST(d.entrytime AS UNSIGNED) DESC, d.id DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = db()->prepare($sql);
    $stmt->bindValue(':id', $categoryId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', offsetForPage($page, $perPage), PDO::PARAM_INT);
    $stmt->execute();

    return array_values(array_filter($stmt->fetchAll(), 'looksLikeImage'));
}

function getImageDetail(int $id): ?array
{
    $sql = "
        SELECT
            d.*,
            k.name AS category_name,
            b.beschreibung,
            " . beschreibungHtmlSelectSql('b') . "
        FROM vup_dateien d
        LEFT JOIN vup_kategorien k ON k.id = d.to_kat
        LEFT JOIN beschreibung b ON b.id = d.id
        WHERE d.id = :id
        LIMIT 1
    ";
    $stmt = db()->prepare($sql);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    return ($row && looksLikeImage($row)) ? $row : null;
}

function imageIdExists(int $id): bool
{
    $stmt = db()->prepare("SELECT id FROM vup_dateien WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);

    return (bool)$stmt->fetchColumn();
}

function getImageTags(int $imageId): array
{
    $stmt = db()->prepare("SELECT tag FROM tags WHERE bid = :id AND aktiv = '1' ORDER BY tag ASC");
    $stmt->execute([':id' => $imageId]);

    return array_column($stmt->fetchAll(), 'tag');
}

function getImageTagsDetailed(int $imageId): array
{
    $stmt = db()->prepare("
        SELECT id, tag
        FROM tags
        WHERE bid = :id
        ORDER BY id ASC
    ");
    $stmt->execute([':id' => $imageId]);

    return $stmt->fetchAll();
}

function getImageLikesCount(int $imageId): int
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM likes WHERE datei_id = :id');
    $stmt->execute([':id' => $imageId]);

    return (int)$stmt->fetchColumn();
}

function hasUserLikedImageByIp(int $imageId): bool
{
    $userIp = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($userIp === '') {
        return false;
    }

    $stmt = db()->prepare('SELECT COUNT(*) FROM likes WHERE datei_id = :id AND user_ip = :ip');
    $stmt->execute([
        ':id' => $imageId,
        ':ip' => $userIp,
    ]);

    return (int)$stmt->fetchColumn() > 0;
}

function getRelatedImages(int $categoryId, int $excludeId, int $limit = 4): array
{
    $sql = baseListSql() . " AND d.to_kat = :category_id AND d.id <> :exclude_id " . dateOrderSql() . ' LIMIT :limit';
    $stmt = db()->prepare($sql);
    $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return array_values(array_filter($stmt->fetchAll(), 'looksLikeImage'));
}

function countSearchResults(string $q): int
{
    $stmt = db()->prepare("
        SELECT COUNT(*)
        FROM (
            SELECT d.id
            FROM vup_dateien d
            LEFT JOIN vup_kategorien k ON k.id = d.to_kat
            WHERE d.name LIKE :q
               OR d.ordner LIKE :q
               OR k.name LIKE :q

            UNION

            SELECT d.id
            FROM vup_dateien d
            INNER JOIN beschreibung b ON b.id = d.id
            WHERE b.beschreibung LIKE :q

            UNION

            SELECT d.id
            FROM vup_dateien d
            INNER JOIN tags t ON t.bid = d.id
            WHERE t.aktiv = '1'
              AND t.tag LIKE :q
        ) AS search_result
    ");
    $stmt->execute([':q' => '%' . $q . '%']);

    return (int)$stmt->fetchColumn();
}

function searchImages(string $q, int $page = 1, int $perPage = PER_PAGE): array
{
    $sql = "
        SELECT
            d.id,
            d.entrytime,
            d.name,
            d.size,
            d.ordner,
            d.to_kat,
            d.downloads,
            k.name AS category_name
        FROM vup_dateien d
        LEFT JOIN vup_kategorien k ON k.id = d.to_kat
        WHERE d.id IN (
            SELECT result.id
            FROM (
                SELECT d1.id
                FROM vup_dateien d1
                LEFT JOIN vup_kategorien k1 ON k1.id = d1.to_kat
                WHERE d1.name LIKE :q
                   OR d1.ordner LIKE :q
                   OR k1.name LIKE :q

                UNION

                SELECT d2.id
                FROM vup_dateien d2
                INNER JOIN beschreibung b ON b.id = d2.id
                WHERE b.beschreibung LIKE :q

                UNION

                SELECT d3.id
                FROM vup_dateien d3
                INNER JOIN tags t ON t.bid = d3.id
                WHERE t.aktiv = '1'
                  AND t.tag LIKE :q
            ) AS result
        )
        ORDER BY CAST(d.entrytime AS UNSIGNED) DESC, d.id DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = db()->prepare($sql);
    $stmt->bindValue(':q', '%' . $q . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', offsetForPage($page, $perPage), PDO::PARAM_INT);
    $stmt->execute();

    return array_values(array_filter($stmt->fetchAll(), 'looksLikeImage'));
}

function getImagesForDayRange(int $start, int $end): array
{
    $sql = "
        SELECT DISTINCT
            d.id,
            d.entrytime,
            d.name,
            d.size,
            d.ordner,
            d.to_kat,
            d.downloads,
            k.name AS category_name,
            b.beschreibung,
            " . beschreibungHtmlSelectSql('b') . "
        FROM vup_dateien d
        LEFT JOIN vup_kategorien k ON k.id = d.to_kat
        LEFT JOIN beschreibung b ON b.id = d.id
        WHERE CAST(d.entrytime AS UNSIGNED) >= :start
          AND CAST(d.entrytime AS UNSIGNED) <= :end
        ORDER BY CAST(d.entrytime AS UNSIGNED) DESC, d.id DESC
    ";

    $stmt = db()->prepare($sql);
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':end', $end, PDO::PARAM_INT);
    $stmt->execute();

    return array_values(array_filter($stmt->fetchAll(), 'looksLikeImage'));
}

function getRandomSlideshowImages(int $limit = 50): array
{
    $sql = "
        SELECT DISTINCT
            d.id,
            d.entrytime,
            d.name,
            d.size,
            d.ordner,
            d.to_kat,
            d.downloads,
            k.name AS category_name
        FROM vup_dateien d
        LEFT JOIN vup_kategorien k ON k.id = d.to_kat
        ORDER BY RAND()
        LIMIT :limit
    ";

    $stmt = db()->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return array_values(array_filter($stmt->fetchAll(), 'looksLikeImage'));
}

function countTimelineImages(): int
{
    $stmt = db()->query("
        SELECT COUNT(*)
        FROM vup_dateien d
        WHERE d.name REGEXP '\\.(jpg|jpeg|png|webp|gif)$'
    ");

    return (int)$stmt->fetchColumn();
}

function getTimelineImagesPaged(int $offset = 0, int $limit = 20): array
{
    $sql = "
        SELECT
            d.id,
            d.entrytime,
            d.name,
            d.size,
            d.ordner,
            d.to_kat,
            d.downloads,
            k.name AS category_name,
            b.beschreibung,
            " . beschreibungHtmlSelectSql('b') . "
        FROM vup_dateien d
        LEFT JOIN vup_kategorien k ON k.id = d.to_kat
        LEFT JOIN beschreibung b ON b.id = d.id
        ORDER BY CAST(d.entrytime AS UNSIGNED) DESC, d.id DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = db()->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return array_values(array_filter($stmt->fetchAll(), 'looksLikeImage'));
}