<?php
namespace App\Router;

class AppRouter extends AbstractRouter
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add_routes()
    {
        $routes = [
            [
                'method' => 'GET',
                'route' => '/',
                'target' => $this->namespace . 'PageController#home',
                'name' => 'home'
            ],
            [
                'method' => 'GET',
                'route' => '/admin',
                'target' => $this->admin_namespace . 'PageController#dashboard',
                'name' => 'admin'
            ]
        ];
        $controllers = ['photo', 'location', 'category', 'album', 'user'];
        $actions = ['new', 'edit', 'delete'];
        foreach ($controllers as $controller):
            $class = $this->namespace . ucfirst($controller) . 'Controller';
            $admin_class = $this->admin_namespace . ucfirst($controller) . 'Controller';
            $routes[] = [
                'method' => 'GET',
                'route' => "/$controller",
                'target' => "$class#index",
                'name' => "$controller"
            ];
            $routes[] = [
                'method' => 'GET',
                'route' => "/$controller/[*:slug]",
                'target' => "$class#show",
                'name' => "$controller-show"
            ];
            $routes[] = [
                'method' => 'GET|POST',
                'route' => "/admin/$controller",
                'target' => "$admin_class#index",
                'name' => "admin-$controller"
            ];
            foreach ($actions as $action):
                $routes[] = [
                    'method' => 'GET|POST',
                    'route' => "/admin/$controller/$action/[i:id]?",
                    'target' => "$admin_class#$action",
                    'name' => "admin-$controller-$action"
                ];
            endforeach;
        endforeach;
        $auth_actions = ['login', 'logout', 'profile'];
        foreach ($auth_actions as $auth_action):
            $routes[] = [
                'method' => 'GET|POST',
                'route' => "/$auth_action",
                'target' => $this->namespace . 'UserController#' . $auth_action,
                'name' => $auth_action
            ];
        endforeach;

        foreach ($routes as $route):
            $this->alto_router->map($route['method'], $route['route'], $route['target'], $route['name']);
        endforeach;
        return $this;
    }

    public function run()
    {
        $match = $this->alto_router->match();
        $target = $match['target'] ?? $this->namespace . 'PageController#e404';
        $params = $match['params'] ?? [];
        [$controller, $action] = explode('#', $target, 2);
        (new $controller($this, $this->view_path, $params))->$action();
        return $this;
    }
}