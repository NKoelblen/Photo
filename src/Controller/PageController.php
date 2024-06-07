<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\LocationRepository;

class PageController extends AppController
{
    public function home()
    {
        $title = 'Hello Word !';

        $albums = (new AlbumRepository())->find_allowed_albums('title ASc', 8);
        $album_link = $this->router->get_alto_router()->generate('albums');
        $album_route = 'album';

        $location_repository = new LocationRepository();
        $locations = $location_repository->find_allowed_roots_locations();
        $location_link = $this->router->get_alto_router()->generate('locations');
        $location_route = 'location';
        $markers = $location_repository->find_allowed_orphans_locations();

        return $this->render(
            'page/home',
            compact(
                'title',
                'albums',
                'album_link',
                'album_route',
                'locations',
                'location_link',
                'location_route',
                'markers'
            )
        );
    }

    public function e404()
    {
        $title = 'Error 404';
        return $this->render('page/e404', compact('title'));
    }
}