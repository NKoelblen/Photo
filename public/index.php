<?php
use App\Router\AlbumRouter;
use App\Router\AppRouter;
use App\Router\CategoryRouter;
use App\Router\LocationRouter;
use App\Router\PageRouter;
use App\Router\PhotoRouter;
use App\Router\UserRouter;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require_once '../vendor/autoload.php';
require_once '../src/config.php';

$whoops = new Run();
$whoops->pushHandler(new PrettyPageHandler());
$whoops->register();

define('UPLOAD_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'uploads');
define('DEBUG_TIME', microtime(true));

$router = (new AppRouter())->add_routes()->run();