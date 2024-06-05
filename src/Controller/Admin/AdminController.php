<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Router\AbstractRouter;

class AdminController extends AppController
{
    protected string $table = '';
    protected string $layout = 'admin';
    protected string $repository = '';

    public function __construct(AbstractRouter $router, string $view_path, array $params)
    {
        parent::__construct($router, $view_path, $params);
        $this->repository = 'App\Repository\\' . ucfirst($this->table) . 'Repository';
    }

    public function index()
    {

    }

    public function new()
    {

    }

    public function edit()
    {

    }

    public function delete()
    {

    }
}