<?php
namespace App\Controller;

final class UserController extends AppController
{
    public function login()
    {
        $title = 'Se connecter';
        return $this->render('auth/login', compact('title'));
    }

    public function logout()
    {

    }
}