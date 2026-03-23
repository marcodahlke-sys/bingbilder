<?php

declare(strict_types=1);

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url)
    {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('formatBytesToMbString')) {
    function formatBytesToMbString(int $bytes): string
    {
        $sizeMb = round($bytes / 1024 / 1024, 2);
        return number_format($sizeMb, 2, '.', '') . ' MB';
    }
}

if (!function_exists('parseGermanDateToTimestamp')) {
    function parseGermanDateToTimestamp(string $date): ?int
    {
        $date = trim($date);

        if ($date === '') {
            return null;
        }

        $dt = DateTime::createFromFormat('d.m.Y', $date);

        if (!$dt) {
            return null;
        }

        return $dt->setTime(0, 0, 0)->getTimestamp();
    }
}

if (!function_exists('imageUrl')) {
    function imageUrl(string $name): string
    {
        return 'uploads/' . ltrim($name, '/');
    }
}

if (!function_exists('thumbUrl')) {
    function thumbUrl(string $name): string
    {
        return 'thumbs/' . ltrim($name, '/');
    }
}

if (!function_exists('tempUrl')) {
    function tempUrl(string $name): string
    {
        return 'temp/' . ltrim($name, '/');
    }
}