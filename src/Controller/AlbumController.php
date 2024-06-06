<?php
namespace App\Controller;

use App\Repository\AlbumRepository;

final class AlbumController extends PostController
{
    public function index()
    {
        $title = 'Albums';

        [$pagination, $posts] = (new AlbumRepository())->find_paginated_allowed_albums();
        $link = $this->router->get_alto_router()->generate('albums');
        $route = 'album';

        return $this->render('album/index', compact('title', 'posts', 'pagination', 'link', 'route'));
    }
    public function show()
    {
        $slug = $this->params['slug'];
        $post = (new AlbumRepository())->find_allowed_album('slug', $slug);
        $title = $post->get_title();

        return $this->render('album/show', compact('title', 'post'));
    }
}