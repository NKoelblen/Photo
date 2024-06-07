<?php
namespace App\Controller\Admin;

use App\Entity\AlbumEntity;
use App\Repository\AlbumRepository;
use App\Validator\AlbumValidator;

final class AlbumController extends PostController
{
    protected string $table = 'album';

    public function index(): array
    {
        [$pagination, $posts] = (new $this->repository)->find_paginated_albums();
        $link = $this->router->get_alto_router()->generate("new_{$this->table}");
        $route = ['singular' => 'album', 'plural' => 'albums'];
        return compact('posts', 'pagination', 'link', 'route');
    }
    public function trash_index()
    {
        $title = 'Albums';
        [$pagination, $posts] = (new $this->repository)->find_paginated_albums('trashed');
        $link = $this->router->get_alto_router()->generate('albums');
        $route = ['singular' => 'album', 'plural' => 'albums'];
        return $this->render("admin/collection/index", compact('title', 'posts', 'pagination', 'link', 'route'));
    }

    public function new()
    {
        $title = 'Albums';
        $form_post = new AlbumEntity;
        $success = false;
        $errors = [];
        if (!empty($_POST)):
            /**
             * @var AlbumRepository
             */
            $repository = new $this->repository;
            $repository->hydrate($form_post, $_POST, ['title']);
            $validator = new AlbumValidator($_POST, $repository);
            if ($validator->validate()):
                $new_post = $repository->create_album([
                    'title' => $form_post->get_title(),
                    'slug' => $form_post->get_slug()
                ]);
                header('Location: ' . $this->router->get_alto_router()->generate("edit_{$this->table}", ['id' => $new_post]) . '?success=1');
            else:
                $errors = $validator->errors();
            endif;
        endif;
        return $this->render("admin/collection/new", array_merge(compact('title', 'form_post', 'success', 'errors'), $this->index()));
    }

    public function edit()
    {
        $title = 'Albums';
        $id = $this->params['id'];
        /**
         * @var AlbumRepository
         */
        $repository = new $this->repository;
        $form_post = $repository->find_album('id', $id);
        $success = false;
        if (isset($_GET['success'])):
            $success = true;
        endif;
        $errors = [];
        if (!empty($_POST)):
            $repository->hydrate($form_post, $_POST, ['title']);
            $validator = new AlbumValidator($_POST, $repository, $form_post->get_id());
            if ($validator->validate()):
                $repository->update_albums(
                    [$form_post->get_id()],
                    [
                        'title' => $form_post->get_title(),
                        'slug' => $form_post->get_slug(),
                    ]
                );
                $success = true;
            else:
                $errors = $validator->errors();
            endif;
        endif;
        return $this->render("admin/collection/edit", array_merge(compact('title', 'form_post', 'success', 'errors'), $this->index()));
    }

    public function trash(): void
    {
        /**
         * @var AlbumRepository
         */
        $repository = new $this->repository;
        $repository->update_albums(
            [$this->params['id']],
            ['status' => 'trashed'],
            "Impossible de mettre la publication {$this->params['id']} à la corbeille."
        );
        header('Location: ' . $this->router->get_alto_router()->generate("new_$this->table") . '?trash=1');
    }
    public function bulk_trash(): void
    {
        /**
         * @var AlbumRepository
         */
        $repository = new $this->repository;
        $ids_list = htmlentities(implode(', ', $_POST['bulk']));
        $repository->update_albums(
            $_POST['bulk'],
            ['status' => 'trashed'],
            "Impossible de mettre les publications $ids_list à la corbeille."
        );
        header('Location: ' . $this->router->get_alto_router()->generate("new_$this->table") . '?trash=2');
    }

    public function restore(): void
    {
        /**
         * @var AlbumRepository
         */
        $repository = new $this->repository;
        $repository->update_albums(
            [$this->params['id']],
            ['status' => 'published'],
            "Impossible de restaurer la publication {$this->params['id']}."
        );
        header('Location: ' . $this->router->get_alto_router()->generate("new_$this->table") . '?restore=1');
    }
    public function bulk_restore(): void
    {
        /**
         * @var AlbumRepository
         */
        $repository = new $this->repository;
        $ids_list = htmlentities(implode(', ', $_POST['bulk']));
        $repository->update_albums(
            $_POST['bulk'],
            ['status' => 'published'],
            "Impossible de restaurer les publications $ids_list."
        );
        header('Location: ' . $this->router->get_alto_router()->generate("new_$this->table") . '?restore=2');
    }

    public function delete(): void
    {
        /**
         * @var AlbumRepository
         */
        $repository = new $this->repository;
        $repository->delete_albums([$this->params['id']]);
        header('Location: ' . $this->router->get_alto_router()->generate("new_$this->table") . '?delete=1');
    }
    public function bulk_delete(): void
    {
        /**
         * @var AlbumRepository
         */
        $repository = new $this->repository;
        $repository->delete_albums($_POST['bulk']);
        header('Location: ' . $this->router->get_alto_router()->generate("new_$this->table") . '?delete=2');
    }
}