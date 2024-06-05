<?php
namespace App\Controller;

final class AlbumController extends PostController
{
    public function index()
    {
        $title = 'Albums';
        return $this->render('album/index', compact('title'));
    }
    public function show()
    {
        $title = 'Album';
        return $this->render('album/show', compact('title'));
    }
}