<?php
namespace App\Controller\Admin;

use App\Entity\RecursiveEntity;
use App\Repository\RecursiveRepository;

abstract class RecursiveController extends PostController
{
    public function draft(array $ids, array $datas = []): void
    {
        /**
         * @var RecursiveRepository
         */
        $repository = new $this->repository;
        /**
         * @var RecursiveEntity[]
         */
        $posts = $repository->find_all($ids);
        foreach ($posts as $post):
            if ($post->get_children_ids()):
                $this->draft($post->get_children_ids());
            endif;
        endforeach;
        $this->edit_status(
            ids: $ids,
            status: 'draft',
            datas: $datas
        );
    }

    public function trash(array $ids): void
    {
        /**
         * @var RecursiveRepository
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
        $this->edit_status(
            ids: $ids,
            status: 'trashed',
            datas: ['parent_id' => null]
        );
    }
}