<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

require __DIR__ . '/app/Helpers/functions.php';
require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/app/Core/Session.php';
require __DIR__ . '/app/Core/Database.php';
require __DIR__ . '/app/Core/View.php';
require __DIR__ . '/app/Core/Controller.php';
require __DIR__ . '/app/Core/Auth.php';
require __DIR__ . '/app/Core/Router.php';
require __DIR__ . '/app/Core/App.php';

require __DIR__ . '/app/Repositories/UserRepository.php';
require __DIR__ . '/app/Repositories/FileRepository.php';
require __DIR__ . '/app/Repositories/DescriptionRepository.php';
require __DIR__ . '/app/Repositories/TagRepository.php';
require __DIR__ . '/app/Repositories/CategoryRepository.php';
require __DIR__ . '/app/Repositories/LikeRepository.php';
require __DIR__ . '/app/Repositories/CounterRepository.php';

require __DIR__ . '/app/Services/AuthService.php';
require __DIR__ . '/app/Services/CalendarService.php';
require __DIR__ . '/app/Services/ImageService.php';
require __DIR__ . '/app/Services/TagService.php';
require __DIR__ . '/app/Services/CounterService.php';
require __DIR__ . '/app/Services/BingDownloadService.php';

require __DIR__ . '/app/Controllers/AuthController.php';
require __DIR__ . '/app/Controllers/HomeController.php';
require __DIR__ . '/app/Controllers/EditController.php';
require __DIR__ . '/app/Controllers/UploadController.php';
require __DIR__ . '/app/Controllers/FilesController.php';
require __DIR__ . '/app/Controllers/FileCheckController.php';
require __DIR__ . '/app/Controllers/TagsController.php';
require __DIR__ . '/app/Controllers/StatsController.php';
require __DIR__ . '/app/Controllers/BingController.php';
require __DIR__ . '/app/Controllers/SearchController.php';

$app = new \App\Core\App($appConfig, $pdo);
$app->run();