<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class App
{
    /**
     * @var array
     */
    private $appConfig;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var Router
     */
    private $router;

    public function __construct(array $appConfig, PDO $pdo)
    {
        $this->appConfig = $appConfig;
        $this->pdo = $pdo;
        $this->router = new Router();

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        $this->router->get('login', '\\App\\Controllers\\AuthController', 'login');
        $this->router->get('logout', '\\App\\Controllers\\AuthController', 'logout');
        $this->router->get('home', '\\App\\Controllers\\HomeController', 'index');
        $this->router->get('upload', '\\App\\Controllers\\UploadController', 'index');
        $this->router->get('rename', '\\App\\Controllers\\FilesController', 'rename');
        $this->router->get('files', '\\App\\Controllers\\FilesController', 'listing');
        $this->router->get('tags', '\\App\\Controllers\\TagsController', 'index');
        $this->router->get('empty-tags', '\\App\\Controllers\\TagsController', 'empty');
        $this->router->get('downloads', '\\App\\Controllers\\StatsController', 'downloads');
        $this->router->get('likes', '\\App\\Controllers\\StatsController', 'likes');
        $this->router->get('bing-download', '\\App\\Controllers\\BingController', 'index');
        $this->router->get('edit', '\\App\\Controllers\\EditController', 'index');
        $this->router->get('tagcloud', '\\App\\Controllers\\TagsController', 'tagcloud');
        $this->router->get('search', '\\App\\Controllers\\SearchController', 'index');
        $this->router->get('file-check', '\\App\\Controllers\\FileCheckController', 'index');
    }

    public function run(): void
    {
        Session::start();

        $page = isset($_GET['page']) && is_string($_GET['page']) ? $_GET['page'] : 'home';
        $route = $this->router->resolve($page);

        if (empty($route)) {
            http_response_code(404);
            exit('Route nicht gefunden.');
        }

        list($controllerClass, $method) = $route;

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            exit('Controller nicht gefunden: ' . $controllerClass);
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            http_response_code(500);
            exit('Methode nicht gefunden: ' . $controllerClass . '::' . $method);
        }

        $controller->$method();
    }
}