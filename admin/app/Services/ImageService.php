<?php

declare(strict_types=1);

class ImageService
{
    public function __construct(private array $config)
    {
    }

    public function uploadPath(string $filename): string
    {
        return $this->config['paths']['uploads'] . '/' . $filename;
    }

    public function thumbPath(string $filename): string
    {
        return $this->config['paths']['thumbs'] . '/' . $filename;
    }

    public function tempPath(string $filename): string
    {
        return $this->config['paths']['temp'] . '/' . $filename;
    }

    public function publicUploadUrl(string $filename): string
    {
        return 'uploads/' . rawurlencode($filename);
    }

    public function publicThumbUrl(string $filename): string
    {
        return 'thumbs/' . rawurlencode($filename);
    }

    public function moveTempToUploads(string $tempName, int $timestamp): string
    {
        $ext = strtolower(pathinfo($tempName, PATHINFO_EXTENSION));
        $finalName = $timestamp . ($ext ? '.' . $ext : '');
        rename($this->tempPath($tempName), $this->uploadPath($finalName));
        return $finalName;
    }

    public function ensureThumb(string $filename, int $width = 500): void
    {
        $source = $this->uploadPath($filename);
        $target = $this->thumbPath($filename);
        if (is_file($target) || !is_file($source)) {
            return;
        }
        $info = @getimagesize($source);
        if (!$info) {
            return;
        }
        [$srcW, $srcH, $type] = $info;
        $dstH = (int) round(($width / $srcW) * $srcH);
        $create = match ($type) {
            IMAGETYPE_JPEG => 'imagecreatefromjpeg',
            IMAGETYPE_PNG => 'imagecreatefrompng',
            IMAGETYPE_WEBP => 'imagecreatefromwebp',
            default => null,
        };
        $save = match ($type) {
            IMAGETYPE_JPEG => fn($img) => imagejpeg($img, $target, 85),
            IMAGETYPE_PNG => fn($img) => imagepng($img, $target),
            IMAGETYPE_WEBP => fn($img) => imagewebp($img, $target, 85),
            default => null,
        };
        if (!$create || !$save) {
            return;
        }
        $src = $create($source);
        $dst = imagecreatetruecolor($width, $dstH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $dstH, $srcW, $srcH);
        $save($dst);
        imagedestroy($src);
        imagedestroy($dst);
    }
}
