<?php
namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\CategoryRepository;
use App\Repository\LocationRepository;
use App\Repository\PhotoRepository;

final class AlbumController extends PostController
{
    protected string $table = 'album';

    public function index()
    {
        $title = 'Albums';

        [$pagination, $posts] = (new $this->repository)->find_paginated_allowed_albums();
        $table = $this->table;
        $link = $this->router->get_alto_router()->generate($table);

        return $this->render('album/index', compact('title', 'posts', 'pagination', 'link', 'table'));
    }
    public function show()
    {
        $slug = $this->params['slug'];
        $post = (new $this->repository)->find_allowed_album('slug', $slug);
        $title = $post->get_title();

        $datas = array_merge(['album_id' => $post->get_id()], $_GET);
        if (isset($datas['page'])):
            unset($datas['page']);
        endif;

        /* Photos */
        [$pagination, $photos] = (new PhotoRepository())->find_paginated_allowed_photos($datas);

        $table = $this->table;
        $link = $this->router->get_alto_router()->generate("$table-show", compact('slug'));

        $filter_locations = (new LocationRepository())->list_allowed();
        $filter_categories = (new CategoryRepository())->list_allowed();

        return $this->render('album/show', compact('title', 'post', 'table', 'photos', 'pagination', 'link', 'filter_locations', 'filter_categories'));
    }
}