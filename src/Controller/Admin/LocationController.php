<?php
namespace App\Controller\Admin;

use App\Entity\LocationEntity;
use App\Helpers\Text;
use App\Repository\LocationRepository;
use App\Validator\LocationValidator;

final class LocationController extends RecursiveController
{
    protected string $table = 'location';

    public function index()
    {
        [$pagination, $posts] = (new $this->repository)->find_paginated_locations();
        $link = $this->router->get_alto_router()->generate("new_{$this->table}");
        $route = ['singular' => 'location', 'plural' => 'locations'];
        return compact('posts', 'pagination', 'link', 'route');
    }
    public function trash_index()
    {
        $title = 'Emplacements';
        [$pagination, $posts] = (new $this->repository)->find_paginated_trashed_locations();
        $link = $this->router->get_alto_router()->generate('locations');
        $route = ['singular' => 'location', 'plural' => 'locations'];
        return $this->render("admin/collection/index", compact('title', 'posts', 'pagination', 'link', 'route'));
    }

    public function new()
    {
        $title = 'Emplacements';
        $form_post = new LocationEntity;
        /**
         * @var LocationRepository
         */
        $repository = new $this->repository;
        $list = $repository->list_locations();
        $success = false;
        $errors = [];
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
                $new_post = $repository->create_location(
                    $datas_to_set,
                    $_POST['children_ids'] ?? null
                );
                header('Location: ' . $this->router->get_alto_router()->generate("edit_{$this->table}", ['id' => $new_post]) . '?success=1');
            else:
                $errors = $validator->errors();
            endif;
        endif;
        return $this->render("admin/location/new", array_merge(compact('title', 'form_post', 'list', 'success', 'errors'), $this->index()));
    }

    public function edit()
    {
        $title = "Emplacements";
        $id = $this->params['id'];
        /**
         * @var LocationRepository
         */
        $repository = new $this->repository;
        $form_post = $repository->find_location('id', $id);
        $list = $repository->list_locations();
        $success = false;
        if (isset($_GET['success'])):
            $success = true;
        endif;
        $errors = [];
        if (!empty($_POST)):
            $fields_to_hydrate = [];
            foreach ($_POST as $key => $value):
                $fields_to_hydrate[] = $key;
                if ($key !== 'children_ids'):
                    $get_value = "get_$key";
                    $datas_to_set[$key] = $form_post->$get_value();
                endif;
            endforeach;
            $repository->hydrate($form_post, $_POST, $fields_to_hydrate);
            $validator = new LocationValidator($_POST, [], $repository, $form_post->get_id());
            if ($validator->validate()):
                $datas_to_set = [];
                foreach ($_POST as $key => $value):
                    if ($key !== 'children_ids'):
                        $get_value = "get_$key";
                        $datas_to_set[$key] = $form_post->$get_value();
                    endif;
                endforeach;
                $repository->update_locations(
                    [$form_post->get_id()],
                    $datas_to_set,
                    $_POST['children_ids'] ?? null
                );
                $success = true;
            else:
                $errors = $validator->errors();
            endif;
        endif;
        return $this->render("admin/location/edit", array_merge(compact('title', 'form_post', 'list', 'success', 'errors'), $this->index()));
    }

    public function trash()
    {
        /**
         * @var LocationRepository
         */
        $repository = new $this->repository;
        $posts = $repository->find_locations([$this->params['id']]);
        foreach ($posts as $post):
            if ($post->get_children_ids()):
                $repository->update_locations(
                    $post->get_children_ids(),
                    ['parent_id' => $post->get_parent_id()]
                );
            endif;
        endforeach;
        $repository->update_locations(
            [$this->params['id']],
            [
                'status' => 'trashed',
                'parent_id' => null
            ],
            null,
            "Impossible de mettre la publication {$this->params['id']} à la corbeille."
        );
        header('Location: ' . $this->router->get_alto_router()->generate("new_$this->table") . '?trash=1');
    }
    public function bulk_trash()
    {
        /**
         * @var LocationRepository
         */
        $repository = new $this->repository;
        $posts = $repository->find_locations($_POST['bulk']);
        foreach ($posts as $post):
            if ($post->get_children_ids()):
                $repository->update_locations(
                    $post->get_children_ids(),
                    ['parent_id' => $post->get_parent_id()]
                );
            endif;
        endforeach;
        $ids_list = htmlentities(implode(', ', $_POST['bulk']));
        $repository->update_locations(
            $_POST['bulk'],
            [
                'status' => 'trashed',
                'parent_id' => null
            ],
            null,
            "Impossible de mettre les publications $ids_list à la corbeille."
        );
        header('Location: ' . $this->router->get_alto_router()->generate("new_$this->table") . '?trash=2');
    }

    public function restore(): void
    {
        /**
         * @var LocationRepository
         */
        $repository = new $this->repository;
        $repository->update_locations(
            [$this->params['id']],
            ['status' => 'published'],
            null,
            "Impossible de restaurer la publication {$this->params['id']}."
        );
        header('Location: ' . $this->router->get_alto_router()->generate("new_$this->table") . '?restore=1');
    }
    public function bulk_restore(): void
    {
        /**
         * @var LocationRepository
         */
        $repository = new $this->repository;
        $ids_list = htmlentities(implode(', ', $_POST['bulk']));
        $repository->update_locations(
            $_POST['bulk'],
            ['status' => 'published'],
            null,
            "Impossible de restaurer les publications $ids_list."
        );
        header('Location: ' . $this->router->get_alto_router()->generate("new_$this->table") . '?restore=2');
    }

    public function delete(): void
    {
        /**
         * @var LocationRepository
         */
        $repository = new $this->repository;
        $repository->delete_locations([$this->params['id']]);
        header('Location: ' . $this->router->get_alto_router()->generate("new_$this->table") . '?delete=1');
    }
    public function bulk_delete(): void
    {
        /**
         * @var LocationRepository
         */
        $repository = new $this->repository;
        $repository->delete_locations($_POST['bulk']);
        header('Location: ' . $this->router->get_alto_router()->generate("new_$this->table") . '?delete=2');
    }
}