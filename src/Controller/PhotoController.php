<?php
namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\LocationRepository;

final class PhotoController extends AppController
{
    protected string $table = 'photo';

    public function index()
    {
        $title = 'Photos';

        $datas = $_GET;
        if (isset($datas['page'])):
            unset($datas['page']);
        endif;

        /* Photos */
        [$pagination, $photos] = (new $this->repository)->find_paginated_allowed_photos($datas);

        $table = $this->table;
        $link = $this->router->get_alto_router()->generate($table);

        $filter_locations = (new LocationRepository())->list_allowed();
        $filter_categories = (new CategoryRepository())->list_allowed();

        return $this->render('photo/index', compact('title', 'photos', 'pagination', 'link', 'table', 'filter_locations', 'filter_categories'));
    }
}