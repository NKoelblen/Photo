<?php
namespace App\Controller;

use App\Repository\LocationRepository;

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
        $link = $this->router->get_alto_router()->generate('locations');
        $route = $this->table;
        $markers = $repository->find_allowed_orphans_locations();
        return $this->render('location/index', compact('title', 'posts', 'link', 'route', 'markers'));
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
        $route = $this->table;
        if ($post->get_children()):
            $markers = $repository->find_allowed_descendant_orphans_locations($post->get_id());
        else:
            $markers = $repository->find_allowed_siblings_orphans_locations($post->get_parent_id());
        endif;
        return $this->render('location/show', compact('title', 'post', 'route', 'markers'));

    }
}