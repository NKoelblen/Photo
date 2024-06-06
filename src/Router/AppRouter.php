<?php

namespace App\Router;

class AppRouter extends AbstractRouter
{
    protected string $e404 = 'App\Controller\PageController#e404';
    public function __construct()
    {
        parent::__construct();
        $this->view_path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'view/templates/';
    }
    public function routes()
    {
        $this->alto_router->map('GET', '/', 'App\Controller\PageController#home', 'home');

        $this->alto_router->map('GET|POST', '/login', 'App\Controller\UserController#login', 'login');
        $this->alto_router->map('POST', '/logout', 'App\Controller\UserController#logout', 'logout');

        $this->alto_router->map('GET', '/admin', 'App\Controller\Admin\PageController#dashboard', 'admin');

        $posts = [
            ['singular' => 'album', 'plural' => 'albums'],
            ['singular' => 'photo', 'plural' => 'photos'],
            ['singular' => 'location', 'plural' => 'locations'],
            ['singular' => 'category', 'plural' => 'categories'],
        ];
        foreach ($posts as $post):
            $namespace = 'App\Controller\\';
            $class = ucfirst($post['singular']) . 'Controller';

            $root = "/{$post['plural']}";
            $single_root = "$root/[*:slug]";
            $controller = $namespace . $class;

            $this->alto_router->map('GET', $root, $controller . '#index', $post['plural']);
            $this->alto_router->map('GET', $single_root, $controller . '#show', $post['singular']);

            $admin_root = "/admin{$root}";
            $single_admin__root = "$admin_root/[i:id]";
            $admin_controller = $namespace . 'Admin\\' . $class;

            if ($post['singular'] === 'photo'):
                $this->alto_router->map('GET|POST', $admin_root, $admin_controller . '#index', "admin_{$post['singular']}");
            endif;
            $this->alto_router->map('GET|POST', "$admin_root/trash", $admin_controller . '#trash_index', "{$post['plural']}_trash");
            $this->alto_router->map('GET|POST', "$admin_root/new", $admin_controller . '#new', "new_{$post['singular']}");
            $this->alto_router->map('GET|POST', $single_admin__root, $admin_controller . '#edit', "edit_{$post['singular']}");
            if ($post['singular'] === 'category' || $post['singular'] === 'photo'):
                $this->alto_router->map('GET|POST', "$admin_root/bulk_edit", $admin_controller . '#bulk_edit', "bulk_edit_{$post['plural']}");
            endif;
            $this->alto_router->map('POST', "$single_admin__root/trash", $admin_controller . '#trash', "trash_{$post['singular']}");
            $this->alto_router->map('POST', "$admin_root/bulk-trash", $admin_controller . '#bulk_trash', "bulk_trash_{$post['plural']}");
            $this->alto_router->map('POST', "$single_admin__root/restore", $admin_controller . '#restore', "restore_{$post['singular']}");
            $this->alto_router->map('POST', "$admin_root/bulk-restore", $admin_controller . '#bulk_restore', "bulk_restore_{$post['plural']}");
            $this->alto_router->map('POST', "$single_admin__root/delete", $admin_controller . '#delete', "delete_{$post['singular']}");
            $this->alto_router->map('POST', "$admin_root/bulk-delete", $admin_controller . '#bulk_delete', "bulk_delete_{$post['plural']}");
        endforeach;

        $this->alto_router->map('GET', '/admin/users', 'App\Controller\Admin\UserController#index', 'admin_user');
        $this->alto_router->map('GET|POST', '/admin/users/new', 'App\Controller\Admin\UserController#new', 'new_user');
        $this->alto_router->map('GET|POST', '/admin/users/profile-[i:id]', 'App\Controller\Admin\UserController#profile', 'edit_profile');
        $this->alto_router->map('GET|POST', '/admin/users/[i:id]', 'App\Controller\Admin\UserController#edit', 'edit_user');
        $this->alto_router->map('POST', '/admin/users/[i:id]/delete', 'App\Controller\Admin\UserController#delete', 'delete_user');

        $this->alto_router->map('GET', '/404', 'App\Controller\PageController#e404', 'e404');

        return $this;
    }
}