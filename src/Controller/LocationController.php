<?php
namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\LocationRepository;
use App\Repository\PhotoRepository;

final class LocationController extends RecursiveController
{
    protected string $table = 'location';

    public function index()
    {
        $title = 'Emplacements';
        /**
         * @var LocationRepository
         */
        $repository = new $this->repository;
        $posts = $repository->find_allowed_roots();
        $markers = $repository->find_allowed_orphans();
        $table = $this->table;
        $edit_link = $this->router->get_alto_router()->generate("admin-$table");
        return $this->render(
            view: "$this->table/index",
            data: compact('title', 'posts', 'markers', 'edit_link', 'table')
        );
    }

    public function show()
    {
        $slug = $this->params['slug'];
        /**
         * @var LocationRepository
         */
        $repository = new $this->repository;
        $post = $repository->find_allowed(
            field: 'slug',
            value: $slug
        );
        $title = $post->get_title();
        if ($post->get_children()):
            $parent_id = $post->get_id();
        else:
            $parent_id = $post->get_parent_id();
        endif;
        $markers = $repository->find_allowed_descendant_orphans($parent_id);

        $datas = array_merge(['location_id' => $post->get_id()], $_GET);
        if (isset($datas['page'])):
            unset($datas['page']);
        endif;

        /* Photos */
        [$pagination, $photos] = (new PhotoRepository())->find_paginated_allowed(filters: $datas);

        $table = $this->table;
        $link = $this->router->get_alto_router()->generate("$table-show", compact('slug'));
        $edit_link = $this->router->get_alto_router()->generate("admin-$table-edit", ['id' => $post->get_id()]);

        $categories_datas = $datas;
        if (isset($categories_datas['category_id'])):
            unset($categories_datas['category_id']);
        endif;
        $filter_categories = (new CategoryRepository())->filter_allowed(filters: $categories_datas);

        return $this->render(
            view: 'location/show',
            data: compact('title', 'post', 'markers', 'table', 'photos', 'pagination', 'link', 'edit_link', 'filter_categories')
        );
    }
}