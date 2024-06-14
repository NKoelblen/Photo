<?php
namespace App\Controller\Admin;

final class CategoryController extends RecursiveController
{
    protected string $table = 'category';
    protected array $labels = [
        'gender' => 'feminine',
        'start-with-vowel' => false,
        'singular' => "catégorie",
        'plural' => 'catégories'
    ];

    public function index()
    {

    }
    public function index_trash()
    {

    }

    public function new()
    {
        $title = 'Nouvelle catégorie';
        return $this->render(
            'admin/category/new',
            compact('title')
        );
    }

    public function edit()
    {
        $title = 'Modifier la catégorie';
        return $this->render(
            'admin/category/edit',
            compact('title')
        );
    }
    public function bulk_edit()
    {

    }
}