<?php
namespace App\Controller\Admin;

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
}