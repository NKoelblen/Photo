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
        $i = 0;
        foreach ($ids as $id):
            $key = "id$i";
            $in[] = ":$key";
            $datas[$key] = $id;
            $i++;
        endforeach;
        $in = implode(', ', $in);
        $ids_list = implode(', ', $ids);

        $query = $this->pdo->prepare("UPDATE nk_$this->table SET $set WHERE id IN ($in)");
        $edit = $query->execute($datas);
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
    public function insert_private_ids(array $ids, array $private_ids, string $message = ''): void
    {
        if (empty($ids) || empty($private_ids)):
            return;
        endif;

        $params = [];

        $in = [];
        $j = 0;
        foreach ($ids as $id):
            $key = "id$j";
            $in[] = ":$key";
            $params[$key] = $id;
            $j++;
        endforeach;
        $in = implode(', ', $in);
        $ids_list = implode(', ', $ids);

        foreach ($private_ids as $private_id):
            $contains = htmlentities($private_id);
            $params['private_id'] = $private_id;

            $query = $this->pdo->prepare(
                "UPDATE nk_$this->table
                 SET private_ids = JSON_ARRAY_APPEND(COALESCE(private_ids, JSON_PRETTY('[]')), '$', :private_id) 
                 WHERE id IN ($in)
                 AND JSON_CONTAINS(JSON_PRETTY(COALESCE(private_ids, '[]')), '$contains') = 0"
            );
            $edit = $query->execute($params);
            if ($edit === false):
                throw new Exception($message ?: "Impossible de modifier la visibilité des publications $ids_list dans la table $this->table.");
            endif;
        endforeach;
    }
    public function remove_private_ids(array $ids, array $private_ids, string $message = ''): void
    {
        if (empty($ids) || empty($private_ids)):
            return;
        endif;
        $params = [];
        $remove = [];
        $i = 0;
        foreach ($private_ids as $private_id) {
            $key = "private_id$i";
            $remove[] = "COALESCE(REPLACE(JSON_SEARCH(private_ids, 'one', :$key), '\"', ''), '$.a')";
            $params[$key] = $private_id;
            $i++;
        }
        $remove = implode(', ', $remove);
        $in = [];
        $j = 0;
        foreach ($ids as $id):
            $key = "id$j";
            $in[] = ":$key";
            $params[$key] = $id;
            $j++;
        endforeach;
        $in = implode(', ', $in);
        $ids_list = implode(', ', $ids);
        $query = $this->pdo->prepare(
            "UPDATE nk_$this->table 
             SET private_ids = JSON_REMOVE(private_ids, $remove)
             WHERE id IN ($in)
             AND private_ids IS NOT NULL"
        );
        $edit = $query->execute($params);
        if ($edit === false):
            throw new Exception($message ?: "Impossible de modifier la visibilité des publications $ids_list dans la table $this->table.");
        endif;
    }
}
