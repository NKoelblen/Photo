<?php
namespace App\Controller;

final class CategoryController extends RecursiveController
{
    public function index()
    {
        $title = 'Catégories';
        return $this->render('category/index', compact('title'));
    }

    public function show()
    {
        $title = 'Catégorie';
        return $this->render('category/show', compact('title'));
    }
}