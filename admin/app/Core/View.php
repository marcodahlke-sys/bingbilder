<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = BASE_PATH . '/app/Views/' . $view . '.php';

        if (!is_file($viewFile)) {
            http_response_code(500);
            exit('View nicht gefunden: ' . $viewFile);
        }

        $isLoginView = $view === 'auth/login';

        require BASE_PATH . '/app/Views/layout/header.php';

        if (!$isLoginView) {
            require BASE_PATH . '/app/Views/layout/topbar.php';
            require BASE_PATH . '/app/Views/layout/sidebar.php';
        }

        require $viewFile;
        require BASE_PATH . '/app/Views/layout/footer.php';
    }
}