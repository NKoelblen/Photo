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
        $posts = $repository->find_allowed_roots_categories();
        $table = $this->table;
        return $this->render("$table/index", compact('title', 'posts', 'table'));
    }

    public function show()
    {
        $slug = $this->params['slug'];
        /**
         * @var CategoryRepository
         */
        $repository = new $this->repository;
        $post = $repository->find_allowed_category('slug', $slug);
        $title = $post->get_title();

        $table = $this->table;

        $datas = array_merge(["{$table}_id" => $post->get_id()], $_GET);
        if (isset($datas['page'])):
            unset($datas['page']);
        endif;

        /* Photos */
        [$pagination, $photos] = (new PhotoRepository())->find_paginated_allowed_photos($datas);

        $link = $this->router->get_alto_router()->generate("$table-show", compact('slug'));

        $filter_locations = (new LocationRepository())->list_allowed();

        return $this->render("$table/show", compact('title', 'post', 'table', 'photos', 'pagination', 'link', 'filter_locations'));
    }
}