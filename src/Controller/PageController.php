<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\CategoryRepository;
use App\Repository\LocationRepository;
use App\Repository\YearRepository;

class PageController extends AppController
{
    public function home()
    {
        $title = 'Hello Word !';

        $albums = (new AlbumRepository())->find_home_allowed(per_page: 8);
        $album_controller = 'album';

        $location_repository = new LocationRepository();
        $locations = $location_repository->find_allowed_roots();
        $location_controller = 'location';
        $markers = $location_repository->find_allowed_orphans();

        $category_repository = new CategoryRepository();
        $categories = $category_repository->find_allowed_roots();
        $category_controller = 'category';

        $years = (new YearRepository())->find_years();
        $years_controller = 'photo';

        return $this->render(
            view: 'page/home',
            data: compact('title', 'albums', 'album_controller', 'locations', 'location_controller', 'categories', 'category_controller', 'years', 'years_controller', 'markers')
        );
    }

    public function e404()
    {
        $title = 'Error 404';
        return $this->render(
            view: 'page/e404',
            data: compact('title')
        );
    }
}