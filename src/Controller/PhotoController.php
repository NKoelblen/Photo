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
        [$pagination, $photos] = (new $this->repository)->find_paginated_allowed($datas);

        $table = $this->table;
        $link = $this->router->get_alto_router()->generate($table);
        $edit_link = $this->router->get_alto_router()->generate("admin-$table");

        $locations_datas = $datas;
        if (isset($locations_datas['location_id'])):
            unset($locations_datas['location_id']);
        endif;
        $filter_locations = (new LocationRepository())->filter_allowed($locations_datas);

        $categories_datas = $datas;
        if (isset($categories_datas['category_id'])):
            unset($categories_datas['category_id']);
        endif;
        $filter_categories = (new CategoryRepository())->filter_allowed($categories_datas);

        return $this->render(
            view: 'photo/index',
            data: compact('title', 'photos', 'pagination', 'link', 'table', 'filter_locations', 'filter_categories', 'edit_link')
        );
    }
}