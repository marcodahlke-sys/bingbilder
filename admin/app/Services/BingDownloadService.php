<?php

declare(strict_types=1);

class BingDownloadService
{
    public function __construct(private Database $db, private array $config)
    {
    }

    public function run(): string
    {
        return "Bing-Download Stub: Hier kommt deine bestehende Download-Logik hinein.\n";
    }
}
