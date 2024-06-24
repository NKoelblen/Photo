<?php

namespace App\Controller;

use App\Router\AbstractRouter;
use App\Security\ForbidenException;

abstract class AbstractController
{
    protected AbstractRouter $router;
    protected string $view_path = '';
    protected array $params = [];
    protected string $layout = 'default';

    public function __construct(AbstractRouter $router, string $view_path, array $params)
    {
        $this->router = $router;
        $this->view_path = $view_path;
        $this->params = $params;
    }

    protected function render(string $view, array $data = [])
    {
        $data['router'] = $this->router;
        ob_start();
        extract($data);
        require $this->view_path . $view . '.php';
        $content = ob_get_clean();
        require (dirname($this->view_path) . DIRECTORY_SEPARATOR . 'layouts/' . $this->layout . '.php');
    }
}