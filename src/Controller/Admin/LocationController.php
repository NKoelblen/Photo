<?php
namespace App\Controller\Admin;

final class LocationController extends RecursiveController
{
    protected string $table = 'location';

    public function index()
    {

    }
    public function trash_index()
    {

    }

    public function new()
    {
        $title = 'Nouvel emplacement';
        return $this->render('admin/location/new', compact('title'));
    }

    public function edit()
    {
        $title = "Modifier l'emplacement";
        return $this->render('admin/location/edit', compact('title'));
    }
    public function bulk_edit()
    {

    }

    public function trash()
    {

    }
    public function bulk_trash()
    {

    }

    public function restore()
    {

    }
    public function bulk_restore()
    {

    }

    public function delete()
    {

    }
    public function bulk_delete()
    {

    }
}