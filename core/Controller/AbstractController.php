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

    protected function render($view, $data = [])
    {
        $data['router'] = $this->router;
        try {
            ob_start();
            extract($data);
            require $this->view_path . $view . '.php';
            $content = ob_get_clean();
            require (dirname($this->view_path) . DIRECTORY_SEPARATOR . 'layout/' . $this->layout . '.php');
        } catch (ForbidenException $exception) {
            header("Location: {$router->get_alto_router()->generate('login')}?forbidden=1");
            exit();
        }
    }
}