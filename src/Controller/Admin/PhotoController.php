<?php
namespace App\Controller\Admin;

final class PhotoController extends PostController
{
    protected string $table = 'photo';

    public function index()
    {
        $title = 'Photos';
        return $this->render('admin/photo/index', compact('title'));
    }

    public function trash_index()
    {

    }

    public function new()
    {

    }

    public function edit()
    {
        $title = 'Modifier la photo';
        return $this->render('admin/photo/edit', compact('title'));
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