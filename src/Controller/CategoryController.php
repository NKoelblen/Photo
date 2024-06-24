<?php
namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\LocationRepository;
use App\Repository\PhotoRepository;

final class CategoryController extends RecursiveController
{
    protected string $table = 'category';

    public function index()
    {
        $title = 'Catégories';
        /**
         * @var CategoryRepository
         */
        $repository = new $this->repository;
        $posts = $repository->find_allowed_roots();

        $table = $this->table;
        $edit_link = $this->router->get_alto_router()->generate("admin-$table");
        return $this->render(
            view: "$table/index",
            data: compact('title', 'posts', 'table', 'edit_link')
        );
    }

    public function show()
    {
        $slug = $this->params['slug'];
        /**
         * @var CategoryRepository
         */
        $repository = new $this->repository;
        $post = $repository->find_allowed(
            field: 'slug',
            value: $slug
        );
        $title = $post->get_title();

        $table = $this->table;

        $datas = array_merge(['category_id' => $post->get_id()], $_GET);
        if (isset($datas['page'])):
            unset($datas['page']);
        endif;

        /* Photos */
        [$pagination, $photos] = (new PhotoRepository())->find_paginated_allowed(filters: $datas);

        $link = $this->router->get_alto_router()->generate("$table-show", compact('slug'));
        $edit_link = $this->router->get_alto_router()->generate("admin-$table-edit", ['id' => $post->get_id()]);

        $locations_datas = $datas;
        if (isset($locations_datas['location_id'])):
            unset($locations_datas['location_id']);
        endif;
        $filter_locations = (new LocationRepository())->filter_allowed($locations_datas);

        return $this->render(
            view: "$table/show",
            data: compact('title', 'post', 'table', 'photos', 'pagination', 'link', 'edit_link', 'filter_locations')
        );
    }
}