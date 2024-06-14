<?php

namespace App\Router;

use AltoRouter;

class AbstractRouter
{
    protected AltoRouter $alto_router;
    protected string $view_path = '';
    protected string $namespace = 'App\Controller\\';
    protected string $admin_namespace = '';
    protected string $class = '';
    protected string $controller = '';

    public function __construct()
    {
        $this->alto_router = new AltoRouter();
        $this->view_path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'view/templates/';
        $this->admin_namespace = "{$this->namespace}Admin\\";
        $this->controller = $this->namespace . $this->class;
    }

    public function get_alto_router()
    {
        return $this->alto_router;
    }
}