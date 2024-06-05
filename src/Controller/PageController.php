<?php

namespace App\Controller;

class PageController extends AppController
{
    public function home()
    {
        $title = 'Hello Word !';
        return $this->render('page/home', compact('title'));
    }

    public function e404()
    {
        $title = 'Error 404';
        return $this->render('page/e404', compact('title'));
    }
}