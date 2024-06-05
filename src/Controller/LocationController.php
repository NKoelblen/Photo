<?php
namespace App\Controller;

final class LocationController extends RecursiveController
{
    public function index()
    {
        $title = 'Emplacements';
        return $this->render('location/index', compact('title'));

    }

    public function show()
    {
        $title = 'Emplacement';
        return $this->render('location/show', compact('title'));

    }
}