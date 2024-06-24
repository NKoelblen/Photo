<?php
use App\Router\AppRouter;
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