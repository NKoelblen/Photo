<?php
namespace App\Repository;

use App\Entity\RecursiveEntity;
use App\Repository\Exception\NotFoundException;
use PDO;

abstract class RecursiveRepository extends PostRepository
{
    /**
     * used for new recursive
     */
    public function create_recursive(array $datas, ?array $children_ids): int
    {
        $id = $this->create_post($datas);

        if ($children_ids):
            $ids_list = htmlentities(implode(', ', $children_ids));
            $this->update_posts($children_ids, ['parent_id' => $id], "Impossible de modifier le parent des publications $ids_list dans la table $this->table.");
        endif;

        return $id;
    }

    /**
     * used for edit, bulk_edit, trash, bulk trash, restore & bulk restore recursive
     */
    public function update_recursives(array $ids, array $datas, ?array $children_ids = null, string $message = null): void
    {
        $this->update_posts($ids, $datas, $message);

        if ($children_ids):
            foreach ($ids as $id):
                $ids_list = htmlentities(implode(', ', $children_ids));
                $this->update_posts($children_ids, ['parent_id' => $id], "Impossible de modifier le parent des publications $ids_list dans la table $this->table.");
            endforeach;
        endif;
    }

    /**
     * used for trash recursives
     */
    public function find_recursives(array $ids): array
    {
        $in = [];
        $ids_params = [];
        foreach ($ids as $id):
            $key = ":id" . $id;
            $in[] = $key;
            $ids_params[$key] = $id;
        endforeach;
        $in = implode(', ', $in);
        $query = $this->pdo->prepare(
            "SELECT
                 $this->table.id,
                 MIN($this->table.parent_id) AS parent_id,
                 IF(
                     COUNT(children.id) = 0,
                     NULL,
                     JSON_ARRAYAGG(children.id)
                 ) AS children_ids
             FROM nk_$this->table $this->table
             JOIN nk_$this->table children ON $this->table.id = children.parent_id
             WHERE $this->table.id IN ($in)
             GROUP BY $this->table.id"
        );
        $query->execute($ids_params);
        $result = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
        if ($result === false):
            $ids_list = implode(', ', $ids);
            throw new NotFoundException($this->table, $ids_list);
        endif;
        return $result;
    }
}
