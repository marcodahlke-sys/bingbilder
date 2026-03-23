<?php

declare(strict_types=1);

class TagService
{
    public function extract(string $text): array
    {
        $words = preg_split('/[^\p{L}\p{N}-]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
        $words = array_values(array_unique(array_filter($words, fn($w) => mb_strlen($w) > 2)));
        sort($words);
        return $words;
    }
}
