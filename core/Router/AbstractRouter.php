<?php

namespace App\Router;

use AltoRouter;

class AbstractRouter
{
    protected AltoRouter $alto_router;
    protected string $view_path;
    protected string $e404;
    public function __construct()
    {
        $this->alto_router = new AltoRouter();
    }

    public function get_alto_router()
    {
        return $this->alto_router;
    }

    public function run()
    {
        $router = $this;
        $view_path = $this->view_path;
        $match = $this->alto_router->match();
        $target = $match['target'] ?? $this->e404;
        $params = $match['params'] ?? [];
        [$controller, $method] = explode('#', $target, 2);
        (new $controller($router, $view_path, $params))->$method();
        return $this;
    }
}