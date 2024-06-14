<?php
namespace App\Controller\Admin;

use App\Entity\LocationEntity;
use App\Helpers\Text;
use App\Repository\LocationRepository;
use App\Validator\LocationValidator;

final class LocationController extends RecursiveController
{
    protected string $table = 'location';
    protected array $labels = [
        'gender' => 'masculine',
        'start-with-vowel' => true,
        'singular' => "emplacement",
        'plural' => 'emplacements'
    ];

    public function index()
    {
        $title = 'Emplacements';

        /**
         * @var LocationRepository
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
        $data = array_merge(
            compact('title', 'status_count', 'status', 'table'),
            ['labels' => $this->labels],
            $this->index_part($status)
        );
        if ((!isset($_GET['index-status']) || $_GET['index-status'] === 'draft') && $status !== 'trashed'):
            $data = array_merge($data, $this->new_part());
        endif;

        return $this->render(
            (!isset($_GET['index-status']) || $_GET['index-status'] === 'draft') && $status !== 'trashed' ? "admin/$table/new" : "admin/$table/index",
            $data
        );
    }

    private function index_part(string $status): array
    {
        /**
         * @var LocationRepository
         */
        $repository = new $this->repository;
        [$pagination, $posts] = $repository->find_paginated_locations($status);
        $link = $this->router->get_alto_router()->generate("admin-$this->table");
        return compact('posts', 'pagination', 'link');
    }

    public function new_part(): array
    {
        $form_post = new LocationEntity;
        $errors = [];

        /**
         * @var LocationRepository
         */
        $repository = new $this->repository;

        $list = $repository->list_locations();

        if (!empty($_POST)):
            $_POST['slug'] = isset($_POST['title']) ? Text::slugify($_POST['title']) : null;
            $fields_to_hydrate = [];
            foreach ($_POST as $key => $value):
                $fields_to_hydrate[] = $key;
            endforeach;

            $repository->hydrate($form_post, $_POST, $fields_to_hydrate);

            $validator = new LocationValidator($_POST, [], $repository);
            if ($validator->validate()):

                $datas_to_set = [];
                foreach ($_POST as $key => $value):
                    if ($key !== 'children_ids'):
                        $get_value = "get_$key";
                        $datas_to_set[$key] = $form_post->$get_value();
                    endif;
                endforeach;

                $new_post = $repository->create_recursive(
                    $datas_to_set,
                    $_POST['children_ids'] ?? null
                );

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
        return compact('form_post', 'list', 'errors');
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
            if ($_GET['status'] === 'trashed'):
                $this->trash($ids);
                exit;
            elseif ($_GET['status'] === 'draft'):
                $this->draft($ids, ['parent_id' => null]);
                exit;
            else:
                $this->edit_status($ids, $_GET['status']);
                exit;
            endif;
        endif;

        $title = "Emplacements";

        /**
         * @var LocationRepository
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

        $form_post = $repository->find_location('id', $id);
        $list = $repository->list_locations();
        $errors = [];
        if (!empty($_POST)):
            $fields_to_hydrate = ['id'];
            foreach ($_POST as $key => $value):
                $get_value = "get_$key";
                if ($form_post->$get_value() != $value):
                    $fields_to_hydrate[] = $key;
                endif;
            endforeach;
            $repository->hydrate($form_post, array_merge($_POST, ['id' => $id]), $fields_to_hydrate);
            $id = $form_post->get_id();

            $validator = new LocationValidator($_POST, [], $repository, $id);
            if ($validator->validate()):

                $datas_to_set = [];
                foreach ($_POST as $key => $value):
                    if ($key !== 'children_ids'):
                        $get_value = "get_$key";
                        $datas_to_set[$key] = $form_post->$get_value();
                    endif;
                endforeach;
                $repository->update_recursives(
                    [$id],
                    $datas_to_set,
                    $_POST['children_ids'] ?? null
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
            "admin/$table/edit",
            array_merge(
                compact('title', 'form_post', 'list', 'errors', 'status_count', 'status', 'table'),
                ['labels' => $this->labels],
                $this->index_part($status)
            )
        );
    }
}