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
        $posts = $repository->find_allowed_roots_locations();
        $markers = $repository->find_allowed_orphans_locations();
        $table = $this->table;
        return $this->render('location/index', compact('title', 'posts', 'markers', 'table'));
    }

    public function show()
    {
        $slug = $this->params['slug'];
        /**
         * @var LocationRepository
         */
        $repository = new $this->repository;
        $post = $repository->find_allowed_location('slug', $slug);
        $title = $post->get_title();
        if ($post->get_children()):
            $markers = $repository->find_allowed_descendant_orphans_locations($post->get_id());
        else:
            $markers = $repository->find_allowed_siblings_orphans_locations($post->get_parent_id());
        endif;

        $datas = array_merge(['location_id' => $post->get_id()], $_GET);
        if (isset($datas['page'])):
            unset($datas['page']);
        endif;

        /* Photos */
        [$pagination, $photos] = (new PhotoRepository())->find_paginated_allowed_photos($datas);

        $table = $this->table;
        $link = $this->router->get_alto_router()->generate("$table-show", compact('slug'));

        $filter_categories = (new CategoryRepository())->list_allowed();

        return $this->render('location/show', compact('title', 'post', 'markers', 'table', 'photos', 'pagination', 'link', 'filter_categories'));
    }
}