<?php
namespace App\Repository;

use Exception;
use PDO;

abstract class PostRepository extends AppRepository
{
    /**
     * used for new post
     */
    public function create_post(array $datas): int
    {
        $fields = [];
        foreach ($datas as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $set = implode(', ', $fields);
        $query = $this->pdo->prepare("INSERT INTO nk_$this->table SET $set");
        $create = $query->execute($datas);
        if ($create === false):
            throw new Exception("Impossible de créer une publication dans la table $this->table.");
        endif;
        return $this->pdo->lastInsertId();
    }

    /**
     * used for edit, bulk_edit trash, bulk trash, restore & bulk restore albums
     */
    public function update_posts(array $ids, array $datas, string $message = null): void
    {
        $fields = [];
        foreach ($datas as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $set = implode(', ', $fields);
        $in = [];
        $ids_params = [];
        foreach ($ids as $id):
            $key = ":id" . $id;
            $in[] = $key;
            $ids_params[$key] = $id;
        endforeach;
        $in = implode(', ', $in);
        $ids_list = implode(', ', $ids);
        $query = $this->pdo->prepare("UPDATE nk_$this->table SET $set WHERE id IN ($in)");
        $edit = $query->execute(array_merge($datas, $ids_params));
        if ($edit === false):
            throw new Exception($message ?: "Impossible de modifier les publications $ids_list dans la table $this->table.");
        endif;
    }

    /**
     * used for delete & bulk delete posts
     */
    public function delete_posts(array $ids): void
    {
        $in = [];
        $params = [];
        foreach ($ids as $id):
            $key = ":id" . $id;
            $in[] = $key;
            $params[$key] = $id;
        endforeach;
        $in = implode(', ', $in);
        $list_label = "la publication {$ids[0]}";
        if (count($ids) > 1):
            $list = implode(', ', $ids);
            $list_label = "les publications $list";
        endif;
        $query = $this->pdo->prepare("DELETE FROM nk_$this->table WHERE id IN ($in) AND status = 'trashed'");
        $delete = $query->execute($params);
        if ($delete === false):
            throw new Exception("Impossible de supprimer $list_label de la table $this->table.");
        endif;
    }

    /**
     * used for admin post indexes
     */
    public function count_by_status(): array
    {
        $query = $this->pdo->prepare(
            "SELECT status, COUNT(id) as nb
             FROM nk_$this->table
             GROUP BY status"
        );
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $table = [];
        if (!empty($results)):
            foreach ($results as $result):
                $table[$result['status']] = $result['nb'];
            endforeach;
        endif;
        return $table;
    }
}
