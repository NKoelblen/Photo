<?php
namespace App\Controller\Admin;

use App\Entity\CategoryEntity;
use App\Helpers\Text;
use App\Repository\CategoryRepository;
use App\Repository\PhotoRepository;
use App\Validator\CategoryValidator;

final class CategoryController extends RecursiveController
{
    protected string $table = 'category';
    protected array $labels = [
        'gender' => 'feminine',
        'start-with-vowel' => false,
        'singular' => "catégorie",
        'plural' => 'catégories'
    ];

    public function index()
    {
        $title = 'Catégories';

        /**
         * @var CategoryRepository
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
         * @var CategoryRepository
         */
        $repository = new $this->repository;
        if ($status === 'published'):
            [$pagination, $posts] = $repository->find_paginated_recursives(columns: ['private']);
        else:
            [$pagination, $posts] = $repository->find_paginated(
                status: $status,
                columns: ['id', 'title', 'slug', 'private'],
                order: 'title'
            );
        endif;
        $link = $this->router->get_alto_router()->generate("admin-$this->table");
        return compact('posts', 'pagination', 'link');
    }

    public function new_part(): array
    {
        $form_post = new CategoryEntity;
        $errors = [];

        /**
         * @var CategoryRepository
         */
        $repository = new $this->repository;

        $list = $repository->form_list();

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

            $validator = new CategoryValidator($_POST, [], $repository);
            if ($validator->validate()):

                $datas_to_set = [];
                foreach ($_POST as $key => $value):
                    if ($key !== 'children_ids'):
                        $get_value = "get_$key";
                        $datas_to_set[$key] = $form_post->$get_value();
                    endif;
                endforeach;

                $new_post = $repository->create_recursive(
                    datas: $datas_to_set,
                    children_ids: $_POST['children_ids'] ?? null
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
        /**
         * Set ids
         */
        if (isset($this->params['id'])):
            $id = $this->params['id'];
            $ids = [$id];
        elseif (isset($_POST['bulk'])):
            $ids = $_POST['bulk'];
        endif;

        /**
         * @var CategoryRepository
         */
        $repository = new $this->repository;

        /**
         * Edit status if $_GET['status']
         */
        if (isset($_GET['status'])):
            if ($_GET['status'] === 'trashed'):
                $this->trash($ids);
            elseif ($_GET['status'] === 'draft'):
                $this->draft(
                    ids: $ids,
                    datas: ['parent_id' => null]
                );
            else:
                $this->edit_status($ids, $_GET['status']);
                $photo_repository = new PhotoRepository();
                $private_categories_ids = $repository->private_ids_list($ids);
                if (!empty($private_categories_ids)):
                    $photos_ids = $photo_repository->list_by_categories($private_categories_ids);
                    if (!empty($photos_ids)):
                        $photo_repository->insert_private_ids(
                            ids: $photos_ids,
                            private_ids: $private_categories_ids
                        );
                    endif;
                endif;
            endif;
            exit;
        endif;

        $title = "Catégories";

        /**
         * Get status
         */
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

        /**
         * Get form datas
         */
        $form_post = $repository->find(
            columns: ['private'],
            field: 'id',
            value: $id
        );
        $list = $repository->form_list();

        $show_link = $this->router->get_alto_router()->generate($table);

        $errors = [];

        /**
         * Edit post
         */
        if (!empty($_POST)):

            /**
             * Hydrate post
             */
            $_POST['slug'] = isset($_POST['title']) ? Text::slugify($_POST['title']) : null;
            $_POST['parent'] = $_POST['parent'] ?? null;
            $_POST['children'] = $_POST['children'] ?? null;

            if (!is_null($_POST['parent']) && json_decode($_POST['parent'], true)['private'] === 1):
                $_POST['private'] = 1;
            else:
                $_POST['private'] = $_POST['private'] ?? 0;
            endif;

            $old_children = $form_post->get_children();

            $fields_to_hydrate = ['id'];
            foreach ($_POST as $key => $value):
                $get_value = "get_$key";
                if ($form_post->$get_value() !== $value):
                    $fields_to_hydrate[] = $key;
                endif;
            endforeach;
            $repository->hydrate(
                entity: $form_post,
                datas: array_merge($_POST, ['id' => $id]),
                keys: $fields_to_hydrate
            );
            $id = $form_post->get_id();

            /**
             * Validate form...
             */
            $validator = new CategoryValidator($_POST, array_keys($list), $repository, $id);
            if ($validator->validate()):

                /**
                 * Update post
                 */
                $datas_to_set = [];
                $children_ids = [];
                foreach ($_POST as $key => $value):
                    if ($key !== 'children'):
                        if ($key === 'parent'):
                            $datas_to_set['parent_id'] = !is_null($form_post->get_parent()) ? $form_post->get_parent()->get_id() : null;
                        else:
                            $get_value = "get_$key";
                            $datas_to_set[$key] = $form_post->$get_value();
                            if ($key === 'title'):
                                $datas_to_set['slug'] = $form_post->get_slug();
                            endif;
                        endif;
                    else:
                        if (!is_null($form_post->get_children())):
                            foreach ($value as $item):
                                $children_ids[] = json_decode($item, true)['id'];
                            endforeach;
                        endif;
                    endif;
                endforeach;
                $descendants_ids = $repository->update_categories(
                    ids: $ids,
                    datas: $datas_to_set,
                    children_ids: $children_ids
                );

                /**
                 * Remove children
                 */
                $children_to_remove = [];
                if ($old_children):
                    foreach ($old_children as $old_child):
                        if (!in_array($old_child->get_id(), $children_ids)):
                            $children_to_remove[] = $old_child->get_id();
                        endif;
                    endforeach;
                endif;
                if (!empty($children_to_remove)):
                    $repository->update_recursives(
                        ids: $children_to_remove,
                        datas: ['parent_id' => null],
                    );
                endif;

                /**
                 * Update photos, albums & locations
                 */
                if (isset($datas_to_set['private'])):

                    $photo_repository = new PhotoRepository();
                    switch ($datas_to_set['private']) {
                        case 1:
                            $category_ids = array_merge($ids, $descendants_ids);
                            $photos_ids = $photo_repository->list_by_categories($category_ids);
                            $photo_repository->insert_private_ids(
                                ids: $photos_ids,
                                private_ids: $category_ids
                            );
                            break;
                        case 0:
                            $photos_ids = $photo_repository->list_by_categories($ids);
                            $photo_repository->remove_private_ids(
                                ids: $photos_ids,
                                private_ids: $ids
                            );
                    }
                endif;


                /**
                 * Redirect
                 */
                $status = $form_post->get_status();
                $query = [$status => 1];
                if ($status !== 'published'):
                    $query['index-status'] = $status;
                endif;
                $query_string = '?' . http_build_query($query);
                header('Location: ' . $this->router->get_alto_router()->generate("admin-$table-edit", ['id' => $id]) . $query_string);

            else:
                /**
                 * ... or display errors
                 */
                $errors = $validator->errors();
            endif;

        endif;

        /**
         * Render view
         */
        return $this->render(
            view: "admin/$table/edit",
            data: array_merge(
                compact('title', 'form_post', 'list', 'errors', 'status_count', 'status', 'table', 'show_link'),
                ['labels' => $this->labels],
                $this->index_part($status)
            )
        );
    }

    public function draft(array $ids, array $datas = []): void
    {
        /**
         * @var CategoryRepository
         */
        $repository = new $this->repository;
        /**
         * @var CategoryEntity[]
         */
        $posts = $repository->find_all($ids);
        foreach ($posts as $post):
            if ($post->get_children_ids()):
                $repository->update_recursives(
                    ids: $post->get_children_ids(),
                    datas: ['parent_id' => $post->get_parent_id()]
                );
            endif;
        endforeach;
        $this->edit_status(
            ids: $ids,
            status: 'draft',
            datas: $datas
        );

        $photo_repository = new PhotoRepository();
        $private_categories_ids = $repository->private_ids_list($ids);
        if (!empty($private_categories_ids)):
            $photos_ids = $photo_repository->list_by_categories($private_categories_ids);
            if (!empty($photos_ids)):
                $photo_repository->remove_private_ids(
                    ids: $photos_ids,
                    private_ids: $private_categories_ids
                );
            endif;
        endif;
    }

    public function trash(array $ids): void
    {
        /**
         * @var CategoryRepository
         */
        $repository = new $this->repository;
        $posts = $repository->find_all($ids);
        foreach ($posts as $post):
            if ($post->get_children_ids()):
                $repository->update_recursives(
                    ids: $post->get_children_ids(),
                    datas: ['parent_id' => $post->get_parent_id()]
                );
            endif;
        endforeach;
        $this->edit_status($ids, 'trashed', ['parent_id' => null]);

        $photo_repository = new PhotoRepository();
        $private_categories_ids = $repository->private_ids_list($ids);
        if (!empty($private_categories_ids)):
            $photos_ids = $photo_repository->list_by_categories($private_categories_ids);
            if (!empty($photos_ids)):
                $photo_repository->remove_private_ids(
                    ids: $photos_ids,
                    private_ids: $private_categories_ids
                );
            endif;
        endif;
    }

}