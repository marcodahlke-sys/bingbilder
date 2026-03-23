<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $page, string $controller, string $method): void
    {
        $this->routes[$page] = [$controller, $method];
    }

    public function resolve(string $page): array
    {
        return $this->routes[$page] ?? $this->routes['home'] ?? [];
    }
}