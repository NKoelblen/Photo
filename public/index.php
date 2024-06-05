<?php
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require_once '../vendor/autoload.php';
require_once '../src/config.php';

$whoops = new Run();
$whoops->pushHandler(new PrettyPageHandler());
$whoops->register();