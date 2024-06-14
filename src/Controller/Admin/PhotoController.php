<?php
namespace App\Controller\Admin;

use App\Attachment\PhotoAttachment;
use App\Entity\PhotoEntity;
use App\Helpers\ArrayHelper;
use App\Repository\AlbumRepository;
use App\Repository\CategoryRepository;
use App\Repository\LocationRepository;
use App\Repository\PhotoRepository;
use App\Validator\PhotoValidator;

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
        $params = ['page', 'index-status', 'edit', 'published', 'draft', 'trashed'];
        foreach ($params as $param):
            if (isset($datas[$param])):
                unset($datas[$param]);
            endif;
        endforeach;

        [$pagination, $posts] = $repository->find_paginated_photos($datas, $status);
        $link = $this->router->get_alto_router()->generate("admin-$this->table");

        $filter_album = (new AlbumRepository())->list_albums();
        $filter_categories = (new CategoryRepository())->list_for_edit_photo();
        $filter_locations = (new LocationRepository())->list_for_edit_photo();

        $table = $this->table;

        return $this->render(
            "admin/$table/index",
            array_merge(
                compact('title', 'posts', 'pagination', 'link', 'filter_album', 'filter_categories', 'filter_locations', 'status_count', 'status', 'table'),
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
            $this->edit_status($ids, $_GET['status']);
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

        $form_post = $repository->find_photo('id', $id);

        /* Locations */
        $location_table = new LocationRepository();
        $locations = $location_table->list_for_edit_photo();

        /* Categories */
        $category_table = new CategoryRepository();
        $categories = $category_table->list_for_edit_photo();

        /* Albums */
        $album_table = new AlbumRepository();
        $albums = $album_table->list_albums();

        $errors = [];
        if (!empty($_POST) || !empty($_FILES)):

            $old_categories = $form_post->get_categories_ids();
            $old_locations = $form_post->get_locations_ids();

            /**
             * hydrate PhotoEntity
             */

            $fields_to_hydrate = ['id'];
            if (!empty($_POST)):
                foreach ($_POST as $key => $value):
                    $get_value = "get_$key";
                    if ($form_post->$get_value() != $value):
                        $fields_to_hydrate[] = $key;
                    endif;
                endforeach;
            endif;
            if (isset($_FILES['image'])):
                $fields_to_hydrate[] = 'image';
            endif;

            $datas = array_merge($_POST, isset($_FILES['image']) ? $_FILES : [], ['id' => $id]);
            $repository->hydrate($form_post, $datas, $fields_to_hydrate);

            /**
             * upload files && hydrate PhotoEntity with path & created_at
             */

            if (isset($_FILES['image'])):
                PhotoAttachment::upload($form_post);
            endif;

            $id = $form_post->get_id();

            $validator = new PhotoValidator($_POST, $repository, array_keys($categories), array_keys($albums), array_keys($locations), $id);
            if ($validator->validate()):

                /**
                 * update photo
                 */

                $datas_to_set = [];
                if (!empty($_POST)):
                    foreach ($_POST as $key => $value):
                        if ($key !== 'locations_ids' && $key !== 'categories_ids'):
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
                    $repository->update_posts(
                        [$id],
                        $datas_to_set
                    );
                endif;

                /**
                 * update locations/categories
                 */

                $collections = [];
                if (isset($fields_to_hydrate['locations_ids'])):
                    foreach ($form_post->get_locations_ids() as $location_id):
                        if (!in_array($location_id, $old_locations)):
                            $collections['location'][] = $location_id;
                        endif;
                    endforeach;
                endif;
                if (isset($fields_to_hydrate['categories_ids'])):
                    foreach ($form_post->get_categories_ids() as $category_id):
                        if (!in_array($category_id, $old_categories)):
                            $collections['category'][] = $category_id;
                        endif;
                    endforeach;
                endif;
                if (!empty($collections)):
                    foreach ($collections as $table => $collections_ids):
                        $repository->insert_items(
                            [$id],
                            $table,
                            $collections_ids
                        );
                        $repository->remove_items(
                            [$id],
                            $table,
                            $collections_ids
                        );
                    endforeach;
                endif;

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
         * update photo
         */

        $datas_to_set = [];
        if (!empty($_POST)):
            foreach ($_POST as $key => $value):
                if ($value):
                    if ($key !== 'locations_ids' && $key !== 'categories_ids'):
                        $datas_to_set[$key] = $value;
                    endif;
                endif;
            endforeach;
        endif;

        if (!empty($ids) && !empty($datas_to_set)):
            $repository->update_posts(
                $ids,
                $datas_to_set
            );
        endif;

        /**
         * update locations/categories
         */

        $collections = [];
        if (isset($_POST['locations_ids'])):
            $collections['location'] = explode(',', $_POST['locations_ids']);
        endif;
        if (isset($_POST['categories_ids'])):
            $collections['category'] = explode(',', $_POST['categories_ids']);
        endif;
        if (!empty($collections)):
            foreach ($collections as $table => $collections_ids):
                if ($table = 'location'):
                    $repository->remove_items($ids, $table, $collections_ids);
                endif;
                $repository->insert_items(
                    $ids,
                    $table,
                    $collections_ids
                );
            endforeach;
        endif;

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

        $entities = $repository->find_photos($ids);
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