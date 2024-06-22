<?php
namespace App\Controller\Admin;

use App\Attachment\PhotoAttachment;
use App\Auth\IsGranted;
use App\Entity\PhotoEntity;
use App\Helpers\ArrayHelper;
use App\Helpers\Text;
use App\Repository\AlbumRepository;
use App\Repository\CategoryRepository;
use App\Repository\LocationRepository;
use App\Repository\PhotoRepository;
use App\Validator\PhotoValidator;

#[IsGranted('admin')]
final class PhotoController extends PostController
{
    protected string $table = 'photo';
    protected array $labels = [
        'gender' => 'feminine',
        'start-with-vowel' => false,
        'singular' => "photo",
        'plural' => 'photos'
    ];

    public function index()
    {
        $title = 'Photos';

        /**
         * @var PhotoRepository
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

        $datas = $_GET;
        $params = ['page', 'index-status', 'edit', 'published', 'draft', 'trashed', 'delete'];
        foreach ($params as $param):
            if (isset($datas[$param])):
                unset($datas[$param]);
            endif;
        endforeach;

        [$pagination, $posts] = $repository->find_paginated($datas, $status);
        $link = $this->router->get_alto_router()->generate("admin-$this->table");

        $albums_list = (new AlbumRepository())->edit_photo_list();

        $category_repository = new CategoryRepository();
        $categories_filter = $category_repository->filter();
        $categories_list = $category_repository->edit_photo_list();

        $location_repository = new LocationRepository();
        $locations_filter = $location_repository->filter();
        $locations_list = $location_repository->edit_photo_list();

        $table = $this->table;

        $show_link = $this->router->get_alto_router()->generate($table);

        return $this->render(
            view: "admin/$table/index",
            data: array_merge(
                compact(
                    'title',
                    'posts',
                    'pagination',
                    'link',
                    'albums_list',
                    'categories_filter',
                    'categories_list',
                    'locations_filter',
                    'locations_list',
                    'status_count',
                    'status',
                    'table',
                    'show_link'
                ),
                ['labels' => $this->labels]
            )
        );
    }

    public function new(): void
    {
        if (!empty($_FILES)):
            $table = new PhotoRepository();
            $data = ArrayHelper::diverse_array($_FILES['image']);
            $photos = [];
            foreach ($data as $image):
                $entity = new PhotoEntity;
                $entity->set_image($image);
                $photo_name = PhotoAttachment::upload($entity);
                $entity
                    ->set_title($photo_name)
                    ->set_description($photo_name);
                $photos[] = $entity;
            endforeach;
            $photo_collection = [];
            foreach ($photos as $photo):
                $fields = ['title', 'slug', 'description', 'created_at', 'path'];
                $datas = [];
                foreach ($fields as $field):
                    if ($field === 'created_at'):
                        $datas['created_at'] = $photo->get_created_at()->format('Y-m-d H:i:s');
                    else:
                        $get_field = "get_$field";
                        $datas[$field] = $photo->$get_field();
                    endif;
                endforeach;
                $photo_collection[] = $datas;
            endforeach;
            $table->create_photos($photo_collection);
            $photo_nb = count($photos) > 1 ? 2 : 1;
            header('Location: ' . $this->router->get_alto_router()->generate("admin-$this->table") . "?index-status=draft&draft=$photo_nb");
        endif;
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

        if (isset($_POST['bulk'])):
            $this->bulk_edit($ids);
            exit;
        endif;

        $title = 'Modifier la photo';

        /**
         * @var PhotoRepository
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

        $form_post = $repository->find('id', $id);

        /* Locations */
        $location_table = new LocationRepository();
        $locations = $location_table->edit_photo_list();

        /* Categories */
        $category_table = new CategoryRepository();
        $categories = $category_table->edit_photo_list();

        /* Albums */
        $album_table = new AlbumRepository();
        $albums = $album_table->edit_photo_list();

        $errors = [];
        if (!empty($_POST) || !empty($_FILES)):

            $old_categories = $form_post->get_categories();
            $old_locations = $form_post->get_locations_ids();

            /**
             * Hydrate post
             */
            $_POST['slug'] = isset($_POST['title']) ? Text::slugify($_POST['title']) : null;
            $_POST['locations_ids'] = $_POST['locations_ids'] ?? null;
            $_POST['categories'] = $_POST['categories'] ?? null;
            $_POST['album_id'] = $_POST['album_id'] ?? null;

            $fields_to_hydrate = ['id'];
            if (!empty($_POST)):
                foreach ($_POST as $key => $value):
                    $get_value = "get_$key";
                    if ($form_post->$get_value() !== $value):
                        $fields_to_hydrate[] = $key;
                    endif;
                endforeach;
            endif;
            if (isset($_FILES['image'])):
                $fields_to_hydrate[] = 'image';
            endif;

            $datas = array_merge($_POST, isset($_FILES['image']) ? $_FILES : [], ['id' => $id]);
            $repository->hydrate(
                entity: $form_post,
                datas: $datas,
                keys: $fields_to_hydrate
            );

            /**
             * Upload files && Hydrate post with path & created_at
             */

            if (isset($_FILES['image'])):
                PhotoAttachment::upload($form_post);
            endif;

            $id = $form_post->get_id();

            /**
             * validate
             */
            $validator = new PhotoValidator($_POST, $repository, array_keys($categories), array_keys($albums), array_keys($locations), $id);
            if ($validator->validate()):

                /**
                 * Update post
                 */

                $datas_to_set = [];
                if (!empty($_POST)):
                    foreach ($_POST as $key => $value):
                        if ($key !== 'locations_ids' && $key !== 'categories'):
                            $get_value = "get_$key";
                            if ($key === 'created_at'):
                                $datas_to_set['created_at'] = $form_post->get_created_at()->format('Y-m-d H:i:s');
                            else:
                                $datas_to_set[$key] = $form_post->$get_value();
                            endif;
                        endif;
                    endforeach;
                endif;
                if (isset($FILES['image'])):
                    $datas_to_set['path'] = $form_post->get_path();
                    $datas_to_set['created_at'] = $form_post->get_created_at()->format('Y-m-d H:i:s');
                endif;
                if (!empty($ids) && !empty($datas_to_set)):
                    $repository->update(
                        ids: $ids,
                        datas: $datas_to_set
                    );
                endif;


                /** Update locations **/

                /**
                 * Attach locations
                 */
                $repository->attach_collection(
                    this_ids: $ids,
                    table: 'location',
                    collection_ids: $form_post->get_locations_ids()
                );

                /**
                 * Detach locations
                 */
                $locations_ids_to_remove = [];
                if (!is_null($form_post->get_locations_ids())):
                    foreach ($old_locations as $old_location):
                        if (!in_array($old_location, $form_post->get_locations_ids())):
                            $locations_ids_to_remove[] = $old_location;
                        endif;
                    endforeach;
                endif;
                $repository->detach_collection(
                    this_ids: $ids,
                    table: 'location',
                    remove_ids: $locations_ids_to_remove
                );


                /** Update categories **/
                $categories_ids = ['public' => [], 'private' => []];
                if (!is_null($form_post->get_categories())):
                    foreach ($form_post->get_categories() as $category):
                        switch ($category->get_private()) {
                            case 0:
                                $categories_ids['public'][] = $category->get_id();
                                break;
                            case 1:
                                $categories_ids['private'][] = $category->get_id();
                        }
                    endforeach;
                endif;

                /**
                 * Attach categories
                 */
                $insert = 0;
                $repository->attach_collection(
                    this_ids: $ids,
                    table: 'category',
                    collection_ids: $categories_ids['public']
                );
                $insert = $repository->attach_collection(
                    this_ids: $ids,
                    table: 'category',
                    collection_ids: $categories_ids['private']
                );

                /**
                 * Detach categories
                 */
                $categories_ids_to_remove = [];

                if ($old_categories):
                    foreach ($old_categories as $old_category):
                        if (!in_array($old_category->get_id(), array_merge($categories_ids['public'], $categories_ids['private']))):
                            $categories_ids_to_remove[] = $old_category->get_id();
                        endif;
                    endforeach;
                endif;
                $remove = $repository->detach_collection(
                    this_ids: $ids,
                    table: 'category',
                    remove_ids: $categories_ids_to_remove
                );


                /**
                 * Update photos, albums & locations visibility
                 */
                if ($insert > 0 || $remove > 0):
                    if ($insert > 0):
                        $repository->insert_private_ids(
                            ids: $ids,
                            private_ids: $categories_ids['private']
                        );
                    endif;
                    if ($remove > 0):
                        $repository->remove_private_ids(
                            ids: $ids,
                            private_ids: $categories_ids_to_remove
                        );
                    endif;
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
                $errors = $validator->errors();
            endif;
        endif;

        return $this->render(
            view: "admin/$table/edit",
            data: array_merge(
                compact('title', 'form_post', 'locations', 'categories', 'albums', 'errors', 'status_count', 'status', 'table'),
                ['labels' => $this->labels],
            )
        );
    }
    public function bulk_edit(array $ids)
    {
        $repository = new PhotoRepository();

        unset($_POST['bulk']);

        /**
         * Update photo
         */

        $datas_to_set = [];
        if (!empty($_POST)):
            foreach ($_POST as $key => $value):
                if ($value):
                    if ($key !== 'locations_ids' && $key !== 'categories'):
                        $datas_to_set[$key] = $value;
                    endif;
                endif;
            endforeach;
        endif;

        if (!empty($ids) && !empty($datas_to_set)):
            $repository->update(
                ids: $ids,
                datas: $datas_to_set
            );
        endif;

        /**
         * Hydrate
         */
        $post = new PhotoEntity();
        $repository->hydrate($post, $_POST, array_keys($_POST));

        if (isset($_POST['locations_ids'])):
            /**
             * Insert locations
             */
            $repository->attach_collection(
                this_ids: $ids,
                table: 'location',
                collection_ids: $post->get_locations_ids()
            );

            /**
             * Detach locations
             */
            $repository->detach_collection(
                this_ids: $ids,
                table: 'location',
                except_ids: $post->get_locations_ids()
            );
        endif;

        /**
         * Insert categories
         */
        if (isset($_POST['categories'])):
            $categories_ids = ['public' => [], 'private' => []];
            foreach ($post->get_categories() as $category):
                switch ($category->get_private()) {
                    case 0:
                        $categories_ids['public'][] = $category->get_id();
                        break;
                    case 1:
                        $categories_ids['private'][] = $category->get_id();
                }
            endforeach;

            /**
             * Attach categories
             */
            $repository->attach_collection(
                this_ids: $ids,
                table: 'category',
                collection_ids: $categories_ids['public']
            );
            $insert = $repository->attach_collection(
                this_ids: $ids,
                table: 'category',
                collection_ids: $categories_ids['private']
            );

            /**
             * Update photos, albums & locations visibility
             */
            if ($insert > 0):
                $repository->insert_private_ids(
                    ids: $ids,
                    private_ids: $categories_ids['private']
                );
            endif;
        endif;

        /**
         * Redirect
         */
        $_GET['edit'] = 2;
        $query_string = '?' . http_build_query($_GET);
        header('Location: ' . $this->router->get_alto_router()->generate("admin-$this->table") . $query_string);
    }

    public function delete(): void
    {
        if (isset($this->params['id'])):
            $ids[] = $this->params['id'];
        else:
            $ids = $_POST['bulk'];
        endif;

        /**
         * @var PhotoRepository
         */
        $repository = new $this->repository;

        $entities = $repository->find_all($ids);
        foreach ($entities as $entity):
            PhotoAttachment::detach($entity);
        endforeach;

        $repository->delete_posts($ids);

        $nb_ids = count($ids) > 1 ? 2 : 1;
        $status_count = $repository->count_by_status();
        $query = ['delete' => $nb_ids];
        if (isset($status_count['trashed'])):
            $query['index-status'] = 'trashed';
        endif;
        $query_string = '?' . http_build_query($query);
        header('Location: ' . $this->router->get_alto_router()->generate("admin-$this->table") . $query_string);

    }
}