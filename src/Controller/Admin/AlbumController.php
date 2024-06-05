<?php
namespace App\Controller\Admin;

final class AlbumController extends PostController
{
    protected string $table = 'album';

    public function index()
    {

    }
    public function trash_index()
    {

    }

    public function new()
    {
        $title = 'Nouvel album';
        return $this->render('admin/album/new', compact('title'));
    }

    public function edit()
    {
        $title = "Modifier l'album";
        return $this->render('admin/album/edit', compact('title'));
    }
    public function bulk_edit()
    {

    }

    public function trash()
    {

    }
    public function bulk_trash()
    {

    }

    public function restore()
    {

    }
    public function bulk_restore()
    {

    }

    public function delete()
    {

    }
    public function bulk_delete()
    {

    }
}