<?php
namespace App\Controller\Admin;

use App\Entity\AlbumEntity;
use App\Helpers\Text;
use App\Repository\AlbumRepository;
use App\Validator\AlbumValidator;

final class AlbumController extends PostController
{
    protected string $table = 'album';
    protected array $labels = [
        'gender' => 'masculine',
        'start-with-vowel' => true,
        'singular' => "album",
        'plural' => 'albums'
    ];

    public function index()
    {
        $title = 'Albums';

        /**
         * @var AlbumRepository
         */
        $repository = new $this->repository;
        $status_count = $repository->count_by_status();
        if (empty($status_count)):
            $status = 'published';
        else:
            if (!isset($status_count['published'])):
                if (isset($status_count['draft'])):
                    $status = $_GET['index-status'] ?? 'draft';
                else:
                    $status = 'trashed';
                endif;
            else:
                $status = $_GET['index-status'] ?? 'published';
            endif;
        endif;

        $table = $this->table;

        $show_link = $this->router->get_alto_router()->generate($table);

        $data = array_merge(
            compact('title', 'status_count', 'status', 'table', 'show_link'),
            ['labels' => $this->labels],
            $this->index_part($status)
        );
        if ((!isset($_GET['index-status']) || $_GET['index-status'] === 'draft') && $status !== 'trashed'):
            $data = array_merge($data, $this->new_part());
        endif;

        return $this->render(
            view:
            (!isset($_GET['index-status']) || $_GET['index-status'] === 'draft') && $status !== 'trashed'
            ? "admin/$table/new"
            : "admin/$table/index",
            data: $data
        );
    }

    private function index_part(string $status): array
    {
        /**
         * @var AlbumRepository
         */
        $repository = new $this->repository;
        [$pagination, $posts] = $repository->find_paginated($status);
        $link = $this->router->get_alto_router()->generate("admin-$this->table");
        return compact('posts', 'pagination', 'link');
    }

    public function new_part(): array
    {
        $form_post = new AlbumEntity;
        $errors = [];

        /**
         * @var AlbumRepository
         */
        $repository = new $this->repository;

        if (!empty($_POST)):
            $_POST['slug'] = isset($_POST['title']) ? Text::slugify($_POST['title']) : null;
            $fields_to_hydrate = [];
            foreach ($_POST as $key => $value):
                $fields_to_hydrate[] = $key;
            endforeach;

            $repository->hydrate(
                entity: $form_post,
                datas: $_POST,
                keys: $fields_to_hydrate
            );

            $validator = new AlbumValidator($_POST, $repository);
            if ($validator->validate()):

                $datas_to_set = [];
                foreach ($_POST as $key => $value):
                    $get_value = "get_$key";
                    $datas_to_set[$key] = $form_post->$get_value();
                endforeach;

                $new_post = $repository->create($datas_to_set);

                $status = $form_post->get_status();
                $query = [$status => 1];
                if ($status !== 'published'):
                    $query['index-status'] = $status;
                endif;
                $query_string = '?' . http_build_query($query);
                header('Location: ' . $this->router->get_alto_router()->generate("admin-$this->table-edit", ['id' => $new_post]) . $query_string);
            else:
                $errors = $validator->errors();
            endif;
        endif;
        return compact('form_post', 'errors');
    }

    public function edit()
    {
        if (isset($this->params['id'])):
            $id = $this->params['id'];
            $ids = [$id];
        elseif (isset($_POST['bulk'])):
            $ids = $_POST['bulk'];
        endif;

        if (isset($_GET['status'])):
            $this->edit_status(
                ids: $ids,
                status: $_GET['status']
            );
            exit;
        endif;

        $title = 'Albums';

        /**
         * @var AlbumRepository
         */
        $repository = new $this->repository;

        $status_count = $repository->count_by_status();
        if (empty($status_count)):
            $status = 'published';
        else:
            if (!isset($status_count['published'])):
                if (isset($status_count['draft'])):
                    $status = $_GET['index-status'] ?? 'draft';
                else:
                    $status = 'trashed';
                endif;
            else:
                $status = $_GET['index-status'] ?? 'published';
            endif;
        endif;

        $table = $this->table;

        $form_post = $repository->find(
            field: 'id',
            value: $id
        );

        $show_link = $this->router->get_alto_router()->generate($table);

        $errors = [];
        if (!empty($_POST)):
            $_POST['slug'] = isset($_POST['title']) ? Text::slugify($_POST['title']) : null;

            $fields_to_hydrate = ['id'];
            foreach ($_POST as $key => $value):
                $get_value = "get_$key";
                if ($form_post->$get_value() != $value):
                    $fields_to_hydrate[] = $key;
                endif;
            endforeach;

            $repository->hydrate(
                entity: $form_post,
                datas: array_merge($_POST, ['id' => $id]),
                keys: $fields_to_hydrate
            );
            $id = $form_post->get_id();

            $validator = new AlbumValidator($_POST, $repository, $id);
            if ($validator->validate()):

                $datas_to_set = [];
                foreach ($_POST as $key => $value):
                    if ($key !== 'children_ids'):
                        $get_value = "get_$key";
                        $datas_to_set[$key] = $form_post->$get_value();
                    endif;
                endforeach;
                $repository->update(
                    ids: $ids,
                    datas: $datas_to_set
                );

                $status = $form_post->get_status();
                $query = [$status => 1];
                if ($status !== 'published'):
                    $query['index-status'] = $status;
                endif;
                $query_string = '?' . http_build_query($query);
                header('Location: ' . $this->router->get_alto_router()->generate("admin-$table-edit", ['id' => $id]) . $query_string);
            else:
                $errors = $validator->errors();
            endif;
        endif;

        return $this->render(
            view: "admin/$table/edit",
            data: array_merge(
                compact('title', 'form_post', 'errors', 'status_count', 'status', 'table', 'show_link'),
                ['labels' => $this->labels],
                $this->index_part($status)
            )
        );
    }
}