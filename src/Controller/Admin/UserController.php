<?php
namespace App\Controller\Admin;

use App\Auth\IsGranted;

final class UserController extends AdminController
{
    protected string $table = 'user';
    protected array $labels = [
        'gender' => 'masculine',
        'start-with-vowel' => true,
        'singular' => "utilisateur",
        'plural' => 'utilisateurs'
    ];

    #[IsGranted()]
    public function profile()
    {
        $title = "Modifier mon profil";
        return $this->render('admin/user/profile', compact('title'));
    }

    #[IsGranted('admin')]
    public function index()
    {
        $title = 'Utilisateurs';
        return $this->render('admin/user/index', compact('title'));
    }

    #[IsGranted('admin')]
    public function new()
    {
        $title = 'Nouvel utilisateur';
        return $this->render('admin/user/new', compact('title'));
    }

    #[IsGranted('admin')]
    public function edit()
    {
        $title = "Modifier l'utilisateur";
        return $this->render('admin/user/edit', compact('title'));
    }

    #[IsGranted('admin')]
    public function delete()
    {

    }
}