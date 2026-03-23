<?php

declare(strict_types=1);

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function looksBrokenEncoding(string $text): bool
{
    return
        str_contains($text, 'Ã') ||
        str_contains($text, 'Â') ||
        str_contains($text, 'â€') ||
        str_contains($text, 'Æ’') ||
        str_contains($text, 'â€š') ||
        str_contains($text, 'Ãƒ') ||
        str_contains($text, 'Ã‚') ||
        str_contains($text, 'â€“') ||
        str_contains($text, 'â€”');
}

function repairTextEncoding(string $text): string
{
    $text = trim($text);

    if ($text === '') {
        return $text;
    }

    $replacements = [
        'Ã¤' => 'ä',
        'Ã„' => 'Ä',
        'Ã¶' => 'ö',
        'Ã–' => 'Ö',
        'Ã¼' => 'ü',
        'Ãœ' => 'Ü',
        'ÃŸ' => 'ß',
        'Ã¸' => 'ø',
        'Ã˜' => 'Ø',
        'Ã¥' => 'å',
        'Ã…' => 'Å',
        'Ã¦' => 'æ',
        'Ã†' => 'Æ',
        'Â©' => '©',
        'Â®' => '®',
        'Â°' => '°',
        'Â·' => '·',
        'Â«' => '«',
        'Â»' => '»',
        'â€“' => '–',
        'â€”' => '—',
        'â€ž' => '„',
        'â€œ' => '“',
        'â€˜' => '‘',
        'â€™' => '’',
        'â€¦' => '…',
        'â€¢' => '•',
        'â‚¬' => '€',
        'â€¹' => '‹',
        'â€º' => '›',
        'â€‘' => '-',
        'Ã¡' => 'á',
        'Ê»' => 'ʻ',
    ];

    $text = strtr($text, $replacements);

    for ($i = 0; $i < 3; $i++) {
        if (!looksBrokenEncoding($text)) {
            break;
        }

        $converted = @mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
        if (is_string($converted) && $converted !== '') {
            $text = $converted;
        }

        $text = strtr($text, $replacements);
    }

    return $text;
}

function displayText($value): string
{
    $text = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = repairTextEncoding($text);

    return e($text);
}

function descriptionEditorValue($value, $htmlFlag = 0): string
{
    $text = (string)$value;

    if ((int)$htmlFlag === 1) {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    return repairTextEncoding($text);
}

function encodeDescriptionForStorage(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function tagEditorValue($value, $htmlFlag = 0): string
{
    $text = (string)$value;

    if ((int)$htmlFlag === 1) {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    return repairTextEncoding($text);
}

function encodeTagForStorage(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function getPage(): string
{
    return $_GET['page'] ?? 'home';
}

function title(string $suffix = ''): string
{
    return SITE_NAME;
}

function currentTheme(): string
{
    if (isset($_GET['theme']) && in_array($_GET['theme'], ['light', 'dark'], true)) {
        $_SESSION['theme'] = $_GET['theme'];
    }

    return $_SESSION['theme'] ?? 'dark';
}

function randomBodyBackground(): string
{
    $file = BODY_BACKGROUNDS[array_rand(BODY_BACKGROUNDS)];
    return BASE_PATH . '/img/' . $file;
}

function projectRootPath(): string
{
    return dirname(__DIR__);
}

function imagePath(array $row, bool $useThumb = false): string
{
    $folder = trim((string)($row['ordner'] ?? ''), '/');
    $name = ltrim((string)($row['name'] ?? ''), '/');
    $base = rtrim(BASE_IMAGE_URL, '/');

    if ($name === '') {
        return '';
    }

    if ($useThumb) {
        $thumbCreatedOrExists = ensureThumbnailExists($row, 500);

        if ($thumbCreatedOrExists) {
            return $base . '/' . THUMBS_FOLDER . '/' . $name;
        }

        if ($folder === '') {
            return $base . '/uploads/' . $name;
        }

        return $base . '/' . $folder . '/' . $name;
    }

    if ($folder === '') {
        return $base . '/uploads/' . $name;
    }

    return $base . '/' . $folder . '/' . $name;
}

function resolveSourceImagePath(array $row): ?string
{
    $folder = trim((string)($row['ordner'] ?? ''), '/');
    $name = ltrim((string)($row['name'] ?? ''), '/');

    if ($name === '') {
        return null;
    }

    $root = projectRootPath();

    if ($folder !== '') {
        $candidate = $root . '/' . $folder . '/' . $name;
        if (is_file($candidate)) {
            return $candidate;
        }
    }

    $fallback = $root . '/uploads/' . $name;
    if (is_file($fallback)) {
        return $fallback;
    }

    return null;
}

function ensureThumbnailExists(array $row, int $targetWidth = 500): bool
{
    $name = ltrim((string)($row['name'] ?? ''), '/');
    if ($name === '') {
        return false;
    }

    $root = projectRootPath();
    $thumbDir = $root . '/' . THUMBS_FOLDER;
    $thumbPath = $thumbDir . '/' . $name;

    if (is_file($thumbPath)) {
        return true;
    }

    if (!is_dir($thumbDir)) {
        if (!@mkdir($thumbDir, 0775, true) && !is_dir($thumbDir)) {
            return false;
        }
    }

    $sourcePath = resolveSourceImagePath($row);
    if ($sourcePath === null) {
        return false;
    }

    return createThumbnail($sourcePath, $thumbPath, $targetWidth);
}

function createThumbnail(string $sourcePath, string $targetPath, int $targetWidth = 500): bool
{
    if (!extension_loaded('gd')) {
        return false;
    }

    if (
        !function_exists('imagecreatetruecolor') ||
        !function_exists('imagecopyresampled')
    ) {
        return false;
    }

    $info = @getimagesize($sourcePath);
    if ($info === false) {
        return false;
    }

    $srcWidth = (int)($info[0] ?? 0);
    $srcHeight = (int)($info[1] ?? 0);
    $mime = (string)($info['mime'] ?? '');

    if ($srcWidth <= 0 || $srcHeight <= 0) {
        return false;
    }

    $newWidth = min($targetWidth, $srcWidth);
    $newHeight = (int)round(($srcHeight / $srcWidth) * $newWidth);

    $sourceImage = false;

    switch ($mime) {
        case 'image/jpeg':
            if (function_exists('imagecreatefromjpeg')) {
                $sourceImage = @imagecreatefromjpeg($sourcePath);
            }
            break;

        case 'image/png':
            if (function_exists('imagecreatefrompng')) {
                $sourceImage = @imagecreatefrompng($sourcePath);
            }
            break;

        case 'image/gif':
            if (function_exists('imagecreatefromgif')) {
                $sourceImage = @imagecreatefromgif($sourcePath);
            }
            break;

        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $sourceImage = @imagecreatefromwebp($sourcePath);
            }
            break;
    }

    if (!$sourceImage) {
        return false;
    }

    $thumbImage = imagecreatetruecolor($newWidth, $newHeight);
    if (!$thumbImage) {
        imagedestroy($sourceImage);
        return false;
    }

    if (in_array($mime, ['image/png', 'image/gif', 'image/webp'], true)) {
        imagealphablending($thumbImage, false);
        imagesavealpha($thumbImage, true);
        $transparent = imagecolorallocatealpha($thumbImage, 0, 0, 0, 127);
        imagefilledrectangle($thumbImage, 0, 0, $newWidth, $newHeight, $transparent);
    }

    $ok = imagecopyresampled(
        $thumbImage,
        $sourceImage,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $srcWidth,
        $srcHeight
    );

    if (!$ok) {
        imagedestroy($sourceImage);
        imagedestroy($thumbImage);
        return false;
    }

    $saved = false;

    switch ($mime) {
        case 'image/jpeg':
            $saved = imagejpeg($thumbImage, $targetPath, 85);
            break;

        case 'image/png':
            $saved = imagepng($thumbImage, $targetPath, 6);
            break;

        case 'image/gif':
            $saved = imagegif($thumbImage, $targetPath);
            break;

        case 'image/webp':
            if (function_exists('imagewebp')) {
                $saved = imagewebp($thumbImage, $targetPath, 85);
            }
            break;
    }

    imagedestroy($sourceImage);
    imagedestroy($thumbImage);

    return $saved && is_file($targetPath);
}

function imageDimensionsFromUploads(array $row): array
{
    $sourcePath = resolveSourceImagePath($row);

    if ($sourcePath === null || !is_file($sourcePath)) {
        return [0, 0];
    }

    $size = @getimagesize($sourcePath);
    if ($size === false) {
        return [0, 0];
    }

    return [
        (int)($size[0] ?? 0),
        (int)($size[1] ?? 0),
    ];
}

function formatDate(?string $entrytime): string
{
    if (!$entrytime || !ctype_digit($entrytime)) {
        return 'Unbekannt';
    }

    return date('d.m.Y', (int)$entrytime);
}

function formatBytesValue(?string $bytes): string
{
    $size = (float)($bytes ?? 0);
    if ($size <= 0) {
        return 'Unbekannt';
    }

    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;

    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }

    return number_format($size, $i === 0 ? 0 : 1, ',', '.') . ' ' . $units[$i];
}

function looksLikeImage(array $row): bool
{
    $ext = strtolower(pathinfo((string)($row['name'] ?? ''), PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
}

function pageParam(int $default = 1): int
{
    return max(1, (int)($_GET['p'] ?? $default));
}

function offsetForPage(int $page, int $perPage = PER_PAGE): int
{
    return ($page - 1) * $perPage;
}

function monthNameGerman(int $month): string
{
    $names = [
        1 => 'Januar',
        2 => 'Februar',
        3 => 'März',
        4 => 'April',
        5 => 'Mai',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'August',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Dezember',
    ];

    return $names[$month] ?? (string)$month;
}

function monthOptionsGerman(): array
{
    return [
        1 => 'Januar',
        2 => 'Februar',
        3 => 'März',
        4 => 'April',
        5 => 'Mai',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'August',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Dezember',
    ];
}

function normalizeCalendarParams(): void
{
    if (empty($_GET['amonat'])) {
        $_GET['amonat'] = date('n');
    }

    if (empty($_GET['ajahr'])) {
        $_GET['ajahr'] = date('Y');
    }

    $_GET['amonat'] = (int)$_GET['amonat'];
    $_GET['ajahr'] = (int)$_GET['ajahr'];
}

function getImageForDayTimestamp(int $startOfDay): ?array
{
    $stmt = db()->prepare("
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
        WHERE d.entrytime LIKE :entrytime
        ORDER BY d.id DESC
        LIMIT 1
    ");
    $stmt->execute([
        ':entrytime' => '%' . $startOfDay . '%'
    ]);
    $row = $stmt->fetch();

    return ($row && looksLikeImage($row)) ? $row : null;
}

function getCalendarImage(): ?array
{
    if (!empty($_GET['dat']) && ctype_digit((string)$_GET['dat'])) {
        $image = getImageForDayTimestamp((int)$_GET['dat']);
        if ($image) {
            return $image;
        }
    }

    return getFeaturedImage();
}

function getCalendarHtml(int $month, int $year): string
{
    $dayNames = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];
    $today = (int)date('d');
    $thisMonth = (int)date('n');
    $thisYear = (int)date('Y');

    $dayInWeek = (int)date('N', mktime(0, 0, 0, $month, 1, $year)) - 1;
    $daysInMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));

    $html = '<table class="cal">';
    $html .= '<tr class="calRow">';

    foreach ($dayNames as $dayName) {
        $html .= '<td class="calHead">' . e($dayName) . '</td>';
    }

    $html .= '</tr><tr class="calRow">';

    for ($i = 0; $i < $dayInWeek; $i++) {
        $html .= '<td></td>';
    }

    for ($currentDay = 1; $currentDay <= $daysInMonth; $currentDay++) {
        $weekday = $dayInWeek % 7;
        $dayInWeek++;

        $currentDayFormatted = sprintf('%02d', $currentDay);
        $start = mktime(0, 0, 0, $month, $currentDay, $year);
        $hasImage = getImageForDayTimestamp($start) !== null;

        $isToday = (
            $today === $currentDay &&
            $month === $thisMonth &&
            $year === $thisYear
        );

        $classes = [];
        if ($isToday) {
            $classes[] = 'today';
        }
        if ($hasImage) {
            $classes[] = 'has-image';
        }

        $html .= '<td' . (!empty($classes) ? ' class="' . e(implode(' ', $classes)) . '"' : '') . '>';

        if ($hasImage) {
            $url = '?page=home&dat=' . $start . '&amonat=' . $month . '&ajahr=' . $year;
            $html .= '<a href="' . e($url) . '" class="red">' . $currentDayFormatted . '</a>';
        } else {
            $html .= $currentDayFormatted;
        }

        $html .= '</td>';

        if ($weekday === 6 && $currentDay !== $daysInMonth) {
            $html .= '</tr><tr class="calRow">';
        }
    }

    $remaining = (7 - ($dayInWeek % 7)) % 7;
    for ($i = 0; $i < $remaining; $i++) {
        $html .= '<td></td>';
    }

    $html .= '</tr></table>';

    return $html;
}

function getArchiveCalendarHtml(int $month, int $year): string
{
    $dayNames = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];
    $today = (int)date('d');
    $thisMonth = (int)date('n');
    $thisYear = (int)date('Y');

    $dayInWeek = (int)date('N', mktime(0, 0, 0, $month, 1, $year)) - 1;
    $daysInMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));

    $html = '<table class="archive-cal">';
    $html .= '<tr>';

    foreach ($dayNames as $dayName) {
        $html .= '<th>' . e($dayName) . '</th>';
    }

    $html .= '</tr><tr>';

    for ($i = 0; $i < $dayInWeek; $i++) {
        $html .= '<td class="empty"></td>';
    }

    for ($currentDay = 1; $currentDay <= $daysInMonth; $currentDay++) {
        $weekday = $dayInWeek % 7;
        $dayInWeek++;

        $currentDayFormatted = sprintf('%02d', $currentDay);
        $start = mktime(0, 0, 0, $month, $currentDay, $year);
        $end = mktime(23, 59, 59, $month, $currentDay, $year);
        $images = getImagesForDayRange($start, $end);

        $isToday = (
            $today === $currentDay &&
            $month === $thisMonth &&
            $year === $thisYear
        );

        $cellClass = $isToday ? 'today' : '';
        $html .= '<td' . ($cellClass !== '' ? ' class="' . $cellClass . '"' : '') . '>';
        $html .= '<div class="archive-daynum">' . $currentDayFormatted . '</div>';

        if (!empty($images)) {
            foreach ($images as $image) {
                $imageId = (int)$image['id'];
                $likes = getImageLikesCount($imageId);
                $liked = hasUserLikedImageByIp($imageId);
                $tagsDetailed = getImageTagsDetailed($imageId);
                $tags = array_map(static fn ($row) => (string)($row['tag'] ?? ''), $tagsDetailed);
                $description = trim((string)($image['beschreibung'] ?? ''));
                $fixedName = displayText($image['name']);
                $detailUrl = '?page=detail&id=' . $imageId;
                $thumbUrl = imagePath($image, true);
                $fullUrl = imagePath($image);
                [$width, $height] = imageDimensionsFromUploads($image);

                $html .= '<div class="archive-entry">';

                $html .= '<a class="archive-thumb-link js-lightbox-trigger" href="' . e($fullUrl) . '" data-full="' . e($fullUrl) . '" data-title="' . $fixedName . '">';
                $html .= '<img class="archive-thumb" src="' . e($thumbUrl) . '" alt="' . $fixedName . '" loading="lazy">';
                $html .= '</a>';

                $html .= '<div class="archive-entry-body">';
                $html .= '<div class="archive-entry-title"><a href="' . e($detailUrl) . '">' . $fixedName . '</a></div>';

                $html .= '<div class="archive-entry-meta">';
                $html .= '<div><strong>Kat:</strong> <a href="?page=category&id=' . (int)$image['to_kat'] . '">' . displayText($image['category_name'] ?? 'Ohne Kategorie') . '</a></div>';
                $html .= '<div><strong>Größe:</strong> ' . e(formatBytesValue($image['size'] ?? null)) . '</div>';

                if ($width > 0) {
                    $html .= '<div><strong>Bildbreite:</strong> ' . $width . ' Pixel</div>';
                }

                if ($height > 0) {
                    $html .= '<div><strong>Bildhöhe:</strong> ' . $height . ' Pixel</div>';
                }

                $html .= '</div>';

                if ($description !== '') {
                    $html .= '<div class="archive-entry-desc">' . nl2br(displayText($description)) . '</div>';
                } else {
                    $html .= '<div class="archive-entry-desc muted">Keine Beschreibung vorhanden.</div>';
                }

                $html .= '<div class="archive-like-row">';
                $html .= '<button type="button" class="archive-like-btn" data-id="' . $imageId . '" aria-label="Like umschalten">';
                $html .= $liked ? '❤' : '♡';
                $html .= '</button> ';
                $html .= '<span class="archive-like-count" id="archive-like-count-' . $imageId . '">' . $likes . '</span>';
                $html .= '</div>';

                if (!empty($tags)) {
                    $html .= '<div class="archive-tags">';
                    foreach ($tags as $tag) {
                        $tagText = (string)$tag;
                        $html .= '<a class="tag" href="?page=search&q=' . urlencode($tagText) . '">' . displayText($tagText) . '</a> ';
                    }
                    $html .= '</div>';
                } else {
                    $html .= '<div class="muted">Keine Tags vorhanden.</div>';
                }

                $html .= '<div class="archive-entry-actions">';
                $html .= '<a class="button" href="' . e($detailUrl) . '">Details</a>';
                $html .= '<a class="button" href="dl.php?id=' . $imageId . '">Download</a>';
                $html .= '</div>';

                $html .= '</div>';
                $html .= '</div>';
            }
        }

        $html .= '</td>';

        if ($weekday === 6 && $currentDay !== $daysInMonth) {
            $html .= '</tr><tr>';
        }
    }

    $remaining = (7 - ($dayInWeek % 7)) % 7;
    for ($i = 0; $i < $remaining; $i++) {
        $html .= '<td class="empty"></td>';
    }

    $html .= '</tr></table>';

    return $html;
}