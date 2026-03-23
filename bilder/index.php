<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

incrementPageCounter();

handleLikeAjaxRequest();
handleThemeAjaxRequest();

try {
    switch (getPage()) {
        case 'home':
            require __DIR__ . '/app/views/pages/home.php';
            break;

        case 'blog':
            require __DIR__ . '/app/views/pages/blog.php';
            break;

        case 'categories':
            require __DIR__ . '/app/views/pages/categories.php';
            break;

        case 'category':
            require __DIR__ . '/app/views/pages/category.php';
            break;

        case 'detail':
            require __DIR__ . '/app/views/pages/detail.php';
            break;

        case 'search':
            require __DIR__ . '/app/views/pages/search.php';
            break;

        case 'archive':
            require __DIR__ . '/app/views/pages/archive.php';
            break;

        case 'slideshow':
            require __DIR__ . '/app/views/pages/slideshow.php';
            break;

        case 'timeline':
            require __DIR__ . '/app/views/pages/timeline.php';
            break;

        default:
            http_response_code(404);
            require __DIR__ . '/app/views/pages/not-found.php';
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    $errorMessage = $e->getMessage();
    require __DIR__ . '/app/views/pages/error.php';
}