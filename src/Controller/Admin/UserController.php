<?php
namespace App\Controller\Admin;

use App\Auth\IsGranted;
use App\Entity\UserEntity;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Validator\UserValidator;

final class UserController extends AdminController
{
    protected string $table = 'user';
    protected array $labels = [
        'gender' => 'masculine',
        'start-with-vowel' => true,
        'singular' => "utilisateur",
        'plural' => 'utilisateurs'
    ];

    #[IsGranted()]
    public function profile()
    {
        $title = "Modifier mon profil";

        /**
         * @var UserRepository
         */
        $repository = new $this->repository;
        $form_post = $repository->find_profile($_SESSION['auth']);

        $errors = [];

        if (!empty($_POST)):
            if (password_verify($_POST['current_password'], $form_post->get_password()) === true):
                $keys = ['login', 'email'];
                if (isset($_POST['new_password']) && $_POST['new_password'] !== ''):
                    $keys[] = 'new_password';
                endif;
                $repository->hydrate($form_post, $_POST, $keys);

                $validator = new UserValidator($_POST, $repository, [], $_SESSION['auth']);
                if ($validator->validate()):
                    $datas = [
                        'login' => $form_post->get_login(),
                        'email' => $form_post->get_email(),
                    ];
                    if (isset($_POST['new_password']) && $_POST['new_password'] !== ''):
                        $datas['password'] = password_hash($form_post->get_password(), HASH);
                    else:
                        $datas['password'] = $form_post->get_password();
                    endif;

                    $repository->update(
                        [$_SESSION['auth']],
                        $datas
                    );
                    header('Location: ' . $this->router->get_alto_router()->generate('profile'));
                else:
                    $errors = $validator->errors();
                endif;
            else:
                $errors['current_password'] = 'Le mot de passe est invalide.';
            endif;
        endif;

        $table = $this->table;
        $labels = $this->labels;
        return $this->render(
            view: 'admin/user/profile',
            data: compact('title', 'form_post', 'errors', 'table', 'labels')
        );
    }

    #[IsGranted('admin')]
    public function index()
    {
        $title = 'Utilisateurs';

        [$pagination, $posts] = (new $this->repository())->find_paginated();

        $link = $this->router->get_alto_router()->generate('admin-user');

        $table = $this->table;
        $labels = $this->labels;
        return $this->render(
            view: 'admin/user/index',
            data: compact('title', 'posts', 'pagination', 'link', 'table', 'labels')
        );
    }

    #[IsGranted('admin')]
    public function new()
    {
        $title = 'Nouvel utilisateur';

        $form_post = new UserEntity;

        /* Categories */
        $category_table = new CategoryRepository();
        $categories = $category_table->private_list();

        $errors = [];

        if (!empty($_POST)):
            $_POST['categories_ids'] ??= null;

            /**
             * @var UserRepository
             */
            $repository = new $this->repository;

            $repository->hydrate(
                entity: $form_post,
                datas: $_POST,
                keys: ['login', 'email', 'password', 'role', 'categories_ids']
            );
            $validator = new UserValidator($_POST, $repository, array_keys($categories), $form_post->get_id());

            if ($validator->validate()):
                $new_user = $repository->create_user(
                    user: $form_post,
                    categories_ids: $_POST['categories_ids']
                );
                header('Location: ' . $this->router->get_alto_router()->generate("admin-{$this->table}-edit", ['id' => $new_user]));
            else:
                $errors = $validator->errors();
            endif;

        endif;

        $table = $this->table;
        $labels = $this->labels;
        return $this->render(
            view: 'admin/user/new',
            data: compact('title', 'form_post', 'categories', 'errors', 'table', 'labels')
        );
    }

    #[IsGranted('admin')]
    public function edit()
    {
        $title = "Modifier l'utilisateur";

        /**
         * @var UserRepository
         */
        $repository = new $this->repository;
        /**
         * @var UserEntity
         */
        $form_post = $repository->find_to_edit($this->params['id']);

        /* Categories */
        $category_table = new CategoryRepository();
        $categories = $category_table->private_list();

        $errors = [];

        if (!empty($_POST)):
            $_POST['categories_ids'] ??= null;
            $keys = ['login', 'email', 'role', 'categories_ids'];
            if (isset($_POST['new_password']) && $_POST['new_password'] !== ''):
                $keys[] = 'new_password';
            endif;
            $repository->hydrate(
                entity: $form_post,
                datas: $_POST,
                keys: $keys
            );
            $validator = new UserValidator($_POST, $repository, array_keys($categories), $this->params['id']);
            if ($validator->validate()):
                $datas = [
                    'login' => $form_post->get_login(),
                    'email' => $form_post->get_email(),
                    'role' => $form_post->get_role()
                ];
                if (isset($_POST['new_password']) && $_POST['new_password'] !== ''):
                    $datas['password'] = password_hash($form_post->get_password(), HASH);
                else:
                    $datas['password'] = $form_post->get_password();
                endif;
                $repository->edit_user(
                    id: $this->params['id'],
                    datas: $datas,
                    categories_ids: $_POST['categories_ids']
                );
                header('Location: ' . $this->router->get_alto_router()->generate("admin-{$this->table}-edit", ['id' => $this->params['id']]));
            else:
                $errors = $validator->errors();
            endif;
        endif;

        $table = $this->table;
        $labels = $this->labels;
        return $this->render(
            view: 'admin/user/edit',
            data: compact('title', 'form_post', 'categories', 'errors', 'table', 'labels')
        );
    }
    public function delete(): void
    {
        /**
         * @var UserRepository
         */
        $repository = new $this->repository;
        $repository->delete([$this->params['id']]);
        header('Location: ' . $this->router->get_alto_router()->generate("admin-$this->table"));
    }

}