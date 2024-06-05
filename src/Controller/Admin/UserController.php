<?php
namespace App\Controller\Admin;

final class UserController extends AdminController
{
    public function index()
    {
        $title = 'Utilisateurs';
        return $this->render('admin/user/index', compact('title'));
    }

    public function new()
    {
        $title = 'Nouvel utilisateur';
        return $this->render('admin/user/new', compact('title'));
    }

    public function edit()
    {
        $title = "Modifier l'utilisateur";
        return $this->render('admin/user/edit', compact('title'));
    }

    public function delete()
    {

    }
}