<?php
namespace App\Controller\Admin;

final class CategoryController extends RecursiveController
{
    protected string $table = 'category';

    public function index()
    {

    }
    public function trash_index()
    {

    }

    public function new()
    {
        $title = 'Nouvelle catégorie';
        return $this->render('admin/category/new', compact('title'));
    }

    public function edit()
    {
        $title = 'Modifier la catégorie';
        return $this->render('admin/category/edit', compact('title'));
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