<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$offset = max(0, (int)($_GET['offset'] ?? 0));
$limit = max(1, min(100, (int)($_GET['limit'] ?? 12)));

try {
    $items = getTimelineImagesPaged($offset, $limit);
    $total = countTimelineImages();

    $html = '';

    foreach ($items as $image) {
        $description = trim((string)($image['beschreibung'] ?? ''));

        $html .= '<article class="timeline-item">';
        $html .= '<div class="timeline-date">';
        $html .= '<span class="date-badge">' . e(formatDate($image['entrytime'] ?? null)) . '</span>';
        $html .= '</div>';

        $html .= '<div class="timeline-dot" aria-hidden="true"></div>';

        $html .= '<div class="timeline-card box">';
        $html .= '<div class="timeline-card-inner">';

        $html .= '<a class="timeline-thumb-link" href="?page=detail&id=' . (int)$image['id'] . '">';
        $html .= '<img class="timeline-thumb" src="' . e(imagePath($image, true)) . '" alt="' . displayText($image['name']) . '" loading="lazy">';
        $html .= '</a>';

        $html .= '<div class="timeline-content">';
        $html .= '<h2 class="timeline-title">';
        $html .= '<a href="?page=detail&id=' . (int)$image['id'] . '">';
        $html .= displayText($image['name']);
        $html .= '</a>';
        $html .= '</h2>';

        $html .= '<p class="muted" style="margin-top:6px;">' . displayText($image['category_name'] ?? 'Ohne Kategorie') . '</p>';

        if ($description !== '') {
            $html .= '<div class="timeline-desc">' . nl2br(displayText($description)) . '</div>';
        }

        $html .= '<div class="timeline-actions">';
        $html .= '<a class="button primary" href="?page=detail&id=' . (int)$image['id'] . '">Details</a>';
        $html .= '<a class="button" href="dl.php?id=' . (int)$image['id'] . '">Download</a>';
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</article>';
    }

    echo json_encode([
        'ok' => true,
        'html' => $html,
        'count' => count($items),
        'next_offset' => $offset + count($items),
        'has_more' => ($offset + count($items)) < $total,
        'total' => $total,
    ], JSON_UNESCAPED_UNICODE);

    exit;
} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);

    exit;
}