<?php
use App\Migration\Album;
use App\Migration\Auth;
use App\Migration\Category;
use App\Migration\Location;
use App\Migration\Photo;

require_once './vendor/autoload.php';
require_once './src/config.php';

Album::up();
Category::up();
Location::up();
Photo::up();
Auth::up();