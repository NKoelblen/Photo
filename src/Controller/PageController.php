<?php

namespace App\Controller;

use App\Repository\AlbumRepository;

class PageController extends AppController
{
    public function home()
    {
        $title = 'Hello Word !';

        $albums = (new AlbumRepository())->find_allowed_albums('title ASc', 8);
        $link = $this->router->get_alto_router()->generate('albums');
        $route = 'album';

        return $this->render('page/home', compact('title', 'albums', 'link', 'route'));
    }

    public function e404()
    {
        $title = 'Error 404';
        return $this->render('page/e404', compact('title'));
    }
}