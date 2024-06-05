<?php
namespace App\Controller;

final class PhotoController extends AppController
{
    public function index()
    {
        $title = 'Photos';
        return $this->render('photo/index', compact('title'));
    }
}