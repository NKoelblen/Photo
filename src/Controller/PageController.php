<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\LocationRepository;
use App\Repository\YearRepository;

class PageController extends AppController
{
    public function home()
    {
        $title = 'Hello Word !';

        $albums = (new AlbumRepository())->find_allowed_albums('date_from DESC', 8);
        $album_controller = 'album';

        $location_repository = new LocationRepository();
        $locations = $location_repository->find_allowed_roots_locations();
        $location_controller = 'location';
        $markers = $location_repository->find_allowed_orphans_locations();

        $years = (new YearRepository())->find_years();
        $years_controller = 'photo';

        return $this->render(
            'page/home',
            compact(
                'title',
                'albums',
                'album_controller',
                'locations',
                'location_controller',
                'years',
                'years_controller',
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