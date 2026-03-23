<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo 'Ungültige ID.';
    exit;
}

try {
    $stmt = db()->prepare("
        SELECT
            id,
            name,
            ordner,
            downloads
        FROM vup_dateien
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo 'Datei nicht gefunden.';
        exit;
    }

    $name = trim((string)($row['name'] ?? ''));
    $folder = trim((string)($row['ordner'] ?? ''), '/');

    if ($name === '') {
        http_response_code(404);
        echo 'Dateiname fehlt.';
        exit;
    }

    if ($folder !== '') {
        $filePath = __DIR__ . '/' . $folder . '/' . $name;
    } else {
        $filePath = __DIR__ . '/' . $name;
    }

    if (!is_file($filePath)) {
        $fallbackPath = __DIR__ . '/uploads/' . $name;
        if (is_file($fallbackPath)) {
            $filePath = $fallbackPath;
        }
    }

    if (!is_file($filePath) || !is_readable($filePath)) {
        http_response_code(404);
        echo 'Datei nicht vorhanden oder nicht lesbar.';
        exit;
    }

    $updateDownloads = db()->prepare("
        UPDATE vup_dateien
        SET downloads = COALESCE(downloads, 0) + 1
        WHERE id = :id
        LIMIT 1
    ");
    $updateDownloads->execute([':id' => $id]);

    $userIp = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($userIp !== '') {
        $timeLimit = time() - 86400;

        $checkLike = db()->prepare("
            SELECT COUNT(*)
            FROM likes
            WHERE datei_id = :id
              AND user_ip = :ip
              AND datum >= :time_limit
        ");
        $checkLike->bindValue(':id', $id, PDO::PARAM_INT);
        $checkLike->bindValue(':ip', $userIp, PDO::PARAM_STR);
        $checkLike->bindValue(':time_limit', $timeLimit, PDO::PARAM_INT);
        $checkLike->execute();

        $hasRecentLike = (int)$checkLike->fetchColumn() > 0;

        if (!$hasRecentLike) {
            $insertLike = db()->prepare("
                INSERT INTO likes (datei_id, user_ip, datum)
                VALUES (:id, :ip, :datum)
            ");
            $insertLike->bindValue(':id', $id, PDO::PARAM_INT);
            $insertLike->bindValue(':ip', $userIp, PDO::PARAM_STR);
            $insertLike->bindValue(':datum', time(), PDO::PARAM_INT);
            $insertLike->execute();
        }
    }

    $fileSize = filesize($filePath);
    $downloadName = basename($name);
    $mimeType = mime_content_type($filePath);

    if ($mimeType === false) {
        $mimeType = 'application/octet-stream';
    }

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $downloadName . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
    header('Content-Length: ' . (string)$fileSize);
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: public');
    header('Expires: 0');

    readfile($filePath);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo 'Fehler beim Download: ' . e($e->getMessage());
    exit;
}