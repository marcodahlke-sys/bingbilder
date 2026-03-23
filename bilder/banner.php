<?php
declare(strict_types=1);

$baseDir = __DIR__;
$folder = 'thumbs';

$logoPath  = $baseDir . '/assets/logo.png';
$bogenPath = $baseDir . '/assets/bogen.png';

$totalWidth = isset($_GET['w']) ? (int)$_GET['w'] : 1400;
$height     = isset($_GET['h']) ? (int)$_GET['h'] : 120;
$count      = isset($_GET['count']) ? (int)$_GET['count'] : 12;

$totalWidth = max(500, min(5000, $totalWidth));
$height     = max(120, min(2000, $height));
$count      = max(1, min(200, $count));

$logoAreaWidth  = 437;
$logoAreaHeight = 120;
$gap            = 10;

$collageWidth = $totalWidth - $logoAreaWidth - $gap;
if ($collageWidth < 40) {
    $collageWidth = 40;
}

$stageWidth  = $totalWidth;
$stageHeight = $height;

$imageDir = $baseDir . DIRECTORY_SEPARATOR . $folder;

if (!extension_loaded('gd')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('GD-Erweiterung ist nicht aktiviert.');
}

if (!is_dir($imageDir)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Ordner nicht gefunden: ' . $folder);
}

if (!is_file($logoPath)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Logo nicht gefunden: assets/logo.png');
}

if (!is_file($bogenPath)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Bogen nicht gefunden: assets/bogen.png');
}

$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$allFiles = scandir($imageDir);

if ($allFiles === false) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Ordner konnte nicht gelesen werden.');
}

$images = [];

foreach ($allFiles as $file) {
    $filePath = $imageDir . DIRECTORY_SEPARATOR . $file;

    if (!is_file($filePath)) {
        continue;
    }

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions, true)) {
        continue;
    }

    $size = @getimagesize($filePath);
    if ($size === false) {
        continue;
    }

    $images[] = [
        'file' => $filePath,
        'w'    => (int)$size[0],
        'h'    => (int)$size[1],
        'ext'  => $ext,
    ];
}

if (count($images) === 0) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Keine Bilder im Ordner gefunden.');
}

shuffle($images);
$selected = [];

for ($i = 0; $i < $count; $i++) {
    if ($count <= count($images)) {
        $selected[] = $images[$i];
    } else {
        $selected[] = $images[array_rand($images)];
    }
}

function randomFloat(float $min, float $max): float
{
    return $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
}

function clampFloat(float $value, float $min, float $max): float
{
    return max($min, min($max, $value));
}

function loadImageResource(string $file, string $ext)
{
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            return @imagecreatefromjpeg($file);
        case 'png':
            return @imagecreatefrompng($file);
        case 'gif':
            return @imagecreatefromgif($file);
        case 'webp':
            if (function_exists('imagecreatefromwebp')) {
                return @imagecreatefromwebp($file);
            }
            return false;
        default:
            return false;
    }
}

function createTransparentCanvas(int $width, int $height)
{
    $img = imagecreatetruecolor($width, $height);
    if ($img === false) {
        return false;
    }

    imagealphablending($img, false);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);

    return $img;
}

function rotateBounds(float $w, float $h, float $deg): array
{
    $rad = deg2rad($deg);
    $cos = abs(cos($rad));
    $sin = abs(sin($rad));

    return [
        ($w * $cos) + ($h * $sin),
        ($w * $sin) + ($h * $cos),
    ];
}

$canvas = createTransparentCanvas($stageWidth, $stageHeight);
if ($canvas === false) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Canvas konnte nicht erstellt werden.');
}

imagealphablending($canvas, true);
imagesavealpha($canvas, true);

/* Bogen hinter alles */
$bogen = @imagecreatefrompng($bogenPath);
if ($bogen !== false) {
    imagealphablending($bogen, true);
    imagesavealpha($bogen, true);

    $bogenScaled = createTransparentCanvas($stageWidth, 120);
    if ($bogenScaled !== false) {
        imagealphablending($bogenScaled, true);
        imagesavealpha($bogenScaled, true);

        imagecopyresampled(
            $bogenScaled,
            $bogen,
            0,
            0,
            0,
            0,
            $stageWidth,
            120,
            imagesx($bogen),
            imagesy($bogen)
        );

        imagecopy($canvas, $bogenScaled, 0, 20, 0, 0, $stageWidth, 120);
        imagedestroy($bogenScaled);
    }

    imagedestroy($bogen);
}

/* Logo original, ohne Veränderung */
$logo = @imagecreatefrompng($logoPath);
if ($logo !== false) {
    imagealphablending($logo, true);
    imagesavealpha($logo, true);

    $logoW = imagesx($logo);
    $logoH = imagesy($logo);

    imagecopy($canvas, $logo, 0, 0, 0, 0, $logoW, $logoH);
    imagedestroy($logo);
}

/* Collage rechts */
$collageX = $logoAreaWidth + $gap;
$collageY = 0;
$collageWidthF = (float)$collageWidth;
$collageHeightF = (float)$stageHeight;

$padding = 0.0;
$borderSize = 4.0;

/* Gleichmäßigere Verteilung über die Breite */
$zoneCount = max(1, $count);
$zoneWidth = $collageWidthF / $zoneCount;

$items = [];

foreach ($selected as $index => $img) {
    $originalW = max(1, $img['w']);
    $originalH = max(1, $img['h']);
    $ratio = $originalW / $originalH;

    $maxItemW = max(20.0, min($collageWidthF * 0.18, $zoneWidth * 1.35));
    $maxItemH = max(20.0, $collageHeightF * 0.80);

    $targetW = randomFloat(max(18.0, $maxItemW * 0.50), $maxItemW);
    $targetH = $targetW / $ratio;

    if ($targetH > $maxItemH) {
        $targetH = $maxItemH;
        $targetW = $targetH * $ratio;
    }

    if ($targetW > $maxItemW) {
        $targetW = $maxItemW;
        $targetH = $targetW / $ratio;
    }

    $rotation = randomFloat(-10, 10);

    [$boundW, $boundH] = rotateBounds(
        $targetW + ($borderSize * 2),
        $targetH + ($borderSize * 2),
        $rotation
    );

    if ($boundW > ($collageWidthF - ($padding * 2)) && $boundW > 0) {
        $scale = ($collageWidthF - ($padding * 2)) / $boundW;
        $targetW *= $scale;
        $targetH *= $scale;
        [$boundW, $boundH] = rotateBounds(
            $targetW + ($borderSize * 2),
            $targetH + ($borderSize * 2),
            $rotation
        );
    }

    if ($boundH > ($collageHeightF - ($padding * 2)) && $boundH > 0) {
        $scale = ($collageHeightF - ($padding * 2)) / $boundH;
        $targetW *= $scale;
        $targetH *= $scale;
        [$boundW, $boundH] = rotateBounds(
            $targetW + ($borderSize * 2),
            $targetH + ($borderSize * 2),
            $rotation
        );
    }

    $targetW = max(16.0, $targetW);
    $targetH = max(16.0, $targetH);

    $zoneStart = $index * $zoneWidth;
    $zoneEnd   = $zoneStart + $zoneWidth;

    $minX = max($padding, $zoneStart - ($boundW * 0.18));
    $maxX = min(
        $collageWidthF - $boundW - $padding,
        $zoneEnd - $boundW + ($boundW * 0.18)
    );

    if ($maxX < $minX) {
        $centerX = ($zoneStart + $zoneEnd - $boundW) / 2;
        $x = clampFloat($centerX, $padding, $collageWidthF - $boundW - $padding);
    } else {
        $x = randomFloat($minX, $maxX);
    }

    $minY = $padding;
    $maxY = max($padding, $collageHeightF - $boundH - $padding);
    $y = randomFloat($minY, $maxY);

    $items[] = [
        'file'    => $img['file'],
        'ext'     => $img['ext'],
        'x'       => (int)round($x),
        'y'       => (int)round($y),
        'w'       => (int)round($targetW),
        'h'       => (int)round($targetH),
        'rotate'  => $rotation,
        'z'       => $index + 1,
    ];
}

shuffle($items);

foreach ($items as $i => &$item) {
    $item['z'] = $i + 1;
}
unset($item);

usort($items, static function (array $a, array $b): int {
    return $a['z'] <=> $b['z'];
});

foreach ($items as $item) {
    $src = loadImageResource($item['file'], $item['ext']);
    if ($src === false) {
        continue;
    }

    imagealphablending($src, true);
    imagesavealpha($src, true);

    $srcW = imagesx($src);
    $srcH = imagesy($src);

    $frameW = $item['w'] + ((int)$borderSize * 2);
    $frameH = $item['h'] + ((int)$borderSize * 2);

    $frame = createTransparentCanvas($frameW, $frameH);
    if ($frame === false) {
        imagedestroy($src);
        continue;
    }

    $white = imagecolorallocatealpha($frame, 255, 255, 255, 0);
    imagefilledrectangle($frame, 0, 0, $frameW - 1, $frameH - 1, $white);

    imagecopyresampled(
        $frame,
        $src,
        (int)$borderSize,
        (int)$borderSize,
        0,
        0,
        $item['w'],
        $item['h'],
        $srcW,
        $srcH
    );

    imagedestroy($src);

    $transparent = imagecolorallocatealpha($frame, 0, 0, 0, 127);
    $rotated = imagerotate($frame, -$item['rotate'], $transparent);
    imagedestroy($frame);

    if ($rotated === false) {
        continue;
    }

    imagealphablending($rotated, true);
    imagesavealpha($rotated, true);

    $dstX = $collageX + $item['x'];
    $dstY = $collageY + $item['y'];

    imagecopy(
        $canvas,
        $rotated,
        $dstX,
        $dstY,
        0,
        0,
        imagesx($rotated),
        imagesy($rotated)
    );

    imagedestroy($rotated);
}

header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

imagepng($canvas);
imagedestroy($canvas);
exit;