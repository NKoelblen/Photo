<?php

namespace App\Controller;

use App\Router\AbstractRouter;

abstract class AppController extends AbstractController
{
    protected string $table = '';
    protected string $repository = '';

    public function __construct(AbstractRouter $router, string $view_path, array $params)
    {
        parent::__construct($router, $view_path, $params);
        $this->repository = 'App\Repository\\' . ucfirst($this->table) . 'Repository';
    }
}