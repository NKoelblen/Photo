<?php
namespace App\Controller\Admin;

use App\Repository\AlbumRepository;
use App\Repository\LocationRepository;
use App\Repository\PostRepository;

abstract class PostController extends AdminController
{
    protected array $labels = [];

    public function edit_status(array $ids, string $status, array $datas = []): void
    {
        /**
         * @var PostRepository
         */
        $repository = new $this->repository;
        $list = 'de ' . ($this->labels['start-with-vowel'] ? "l'" : ($this->labels['gender'] === 'masculine' ? 'le ' : 'la ')) . $this->labels['singular'] . ' ' . $ids[0];
        if (count($ids) > 1):
            $ids_list = htmlentities(implode(', ', $ids));
            $list = "des {$this->labels['plural']} $ids_list";
        endif;
        $repository->update_posts(
            $ids,
            array_merge(['status' => $status], $datas),
            "Impossible de modifier l'état $list dans la table $this->table."
        );
        $nb_ids = count($ids) > 1 ? 2 : 1;
        header('Location: ' . $this->router->get_alto_router()->generate("admin-$this->table") . "?$status=$nb_ids" . ($status !== 'published' ? "&index-status=$status" : ''));
    }

    public function delete(): void
    {
        if (isset($this->params['id'])):
            $ids[] = $this->params['id'];
        else:
            $ids = $_POST['bulk'];
        endif;

        /**
         * @var PostRepository
         */
        $repository = new $this->repository;
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

    public function add_private_ids(string $post, array $photos_ids, array $categories_ids)
    {
        $class = 'App\Repository\\' . ucfirst($post) . 'Repository';
        $method = "find_categories_{$post}_ids";
        /**
         * @var LocationRepository|AlbumRepository
         */
        $repository = new $class;
        $ids = $repository->$method($photos_ids, $categories_ids, 0);

        $repository->insert_private_ids($ids, $categories_ids);
    }

    public function remove_private_ids(string $post, array $photos_ids, array $categories_ids)
    {
        $class = 'App\Repository\\' . ucfirst($post) . 'Repository';
        $method = "find_categories_{$post}_ids";
        $repository = new $class;
        $ids = $repository->$method($photos_ids, $categories_ids, 1);

        $repository->remove_private_ids($ids, $categories_ids);
    }

    public function upddate_private(string $post, array $photos_ids)
    {
        $class = 'App\Repository\\' . ucfirst($post) . 'Repository';
        $method = "find_categories_{$post}_visibility";
        $repository = new $class;
        $posts = $repository->$method($photos_ids);
        $private = [];
        $public = [];
        foreach ($posts as $item):
            if ($item['photos'] === $item['private_photos'] && $item['private'] === 0):
                $private[] = $item["id"];
            elseif ($item['photos'] !== $item['private_photos'] && $item['private'] === 1):
                $public[] = $item["id"];
            endif;
        endforeach;

        if (!empty($private)):
            $repository->update_posts($private, ['private' => 1]);
        endif;
        if (!empty($public)):
            $repository->update_posts($public, ['private' => 0]);
        endif;
    }
}