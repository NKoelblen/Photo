<?php

namespace App\Controller\Admin;

use App\Router\AbstractRouter;

class PageController extends AdminController
{
    public function dashboard()
    {
        $title = 'Bienvenue sur votre tableau de bord !';
        return $this->render('admin/page/dashboard', compact('title'));
    }
}