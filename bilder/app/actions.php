<?php

declare(strict_types=1);

function handleLikeAjaxRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (($_POST['action'] ?? '') !== 'like_toggle') {
        return;
    }

    $imageId = (int)($_POST['image_id'] ?? 0);
    if ($imageId <= 0) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false]);
        exit;
    }

    $userIp = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($userIp === '') {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false]);
        exit;
    }

    try {
        $check = db()->prepare('SELECT COUNT(*) FROM likes WHERE datei_id = :id AND user_ip = :ip');
        $check->execute([
            ':id' => $imageId,
            ':ip' => $userIp,
        ]);
        $exists = (int)$check->fetchColumn() > 0;

        if ($exists) {
            $delete = db()->prepare('DELETE FROM likes WHERE datei_id = :id AND user_ip = :ip');
            $delete->execute([
                ':id' => $imageId,
                ':ip' => $userIp,
            ]);
            $liked = false;
        } else {
            $insert = db()->prepare('INSERT INTO likes (datei_id, user_ip) VALUES (:id, :ip)');
            $insert->execute([
                ':id' => $imageId,
                ':ip' => $userIp,
            ]);
            $liked = true;
        }

        $countStmt = db()->prepare('SELECT COUNT(*) FROM likes WHERE datei_id = :id');
        $countStmt->execute([':id' => $imageId]);
        $count = (int)$countStmt->fetchColumn();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => true,
            'liked' => $liked,
            'count' => $count,
        ]);
        exit;
    } catch (Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false]);
        exit;
    }
}

function handleThemeAjaxRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (($_POST['action'] ?? '') !== 'theme_toggle') {
        return;
    }

    $theme = (string)($_POST['theme'] ?? '');

    if (!in_array($theme, ['light', 'dark'], true)) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false]);
        exit;
    }

    $_SESSION['theme'] = $theme;

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => true,
        'theme' => $theme,
    ]);
    exit;
}

function incrementPageCounter(): void
{
    try {
        db()->exec("UPDATE counter SET `count` = `count` + 1");
    } catch (Throwable $e) {
        // Counter-Fehler sollen die Seite nicht kaputt machen
    }
}