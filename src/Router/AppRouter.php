<?php
namespace App\Router;

use App\Auth\IsGranted;
use App\Security\ForbidenException;
use ReflectionClass;

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
            ],
            [
                'method' => 'GET|POST',
                'route' => "/profile",
                'target' => $this->admin_namespace . 'UserController#profile',
                'name' => 'profile'
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
        $auth_actions = ['login', 'logout'];
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
        try {
            $qwerty = new $controller($this, $this->view_path, $params);
            $reflection = new ReflectionClass($qwerty::class);
            $granted_attributes = $reflection->getAttributes(IsGranted::class);
            if (!empty($granted_attributes)):
                $granted_attributes[0]->newInstance();
            endif;
            foreach ($reflection->getMethods() as $method):
                if ($method->name === $action):
                    $granted_attributes = $method->getAttributes(IsGranted::class);
                    if (!empty($granted_attributes)):
                        $granted_attributes[0]->newInstance();
                    endif;
                endif;
            endforeach;
            $qwerty->$action();
            return $this;
        } catch (ForbidenException $exception) {
            header("Location: {$this->get_alto_router()->generate('login')}?forbidden=1");
            exit();
        }
    }
}