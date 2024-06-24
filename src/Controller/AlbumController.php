<?php
namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\LocationRepository;
use App\Repository\PhotoRepository;

final class AlbumController extends PostController
{
    protected string $table = 'album';

    public function index()
    {
        $title = 'Albums';

        [$pagination, $posts] = (new $this->repository)->find_paginated_allowed();
        $table = $this->table;
        $link = $this->router->get_alto_router()->generate($table);
        $edit_link = $this->router->get_alto_router()->generate("admin-$table");

        return $this->render(
            view: 'album/index',
            data: compact('title', 'posts', 'pagination', 'link', 'edit_link', 'table')
        );
    }
    public function show()
    {
        $slug = $this->params['slug'];
        $post = (new $this->repository)->find_allowed('slug', $slug);
        $title = $post->get_title();

        $datas = array_merge(['album_id' => $post->get_id()], $_GET);
        if (isset($datas['page'])):
            unset($datas['page']);
        endif;

        /* Photos */
        [$pagination, $photos] = (new PhotoRepository())->find_paginated_allowed(filters: $datas);

        $table = $this->table;
        $link = $this->router->get_alto_router()->generate("$table-show", compact('slug'));
        $edit_link = $this->router->get_alto_router()->generate("admin-$table-edit", ['id' => $post->get_id()]);

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
            view: 'album/show',
            data: compact('title', 'post', 'table', 'photos', 'pagination', 'link', 'edit_link', 'filter_locations', 'filter_categories')
        );
    }
}