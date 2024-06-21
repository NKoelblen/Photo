<?php

namespace App\Controller\Admin;

use App\Router\AbstractRouter;

class PageController extends AdminController
{
    public function dashboard()
    {
        $title = 'Bienvenue sur votre tableau de bord !';
        return $this->render(
            view: 'admin/page/dashboard',
            data: compact('title')
        );
    }
}