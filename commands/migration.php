<?php
use App\Migration\Album;
use App\Migration\Auth;
use App\Migration\Category;
use App\Migration\Location;
use App\Migration\Photo;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/src/config.php';

Album::up();
Category::up();
Location::up();
Photo::up();
Auth::up();