<?php
namespace App\Repository;

use App\Entity\PostEntity;

abstract class PostRepository extends AppRepository
{
    protected string $album_table = 'album';
    protected string $category_table = 'category';
    protected string $location_table = 'location';
    protected string $category_allowed = "c.status = 'published'
                                            AND c.private = 0";
    protected string $photo_table = 'photo';
    protected string $photo_allowed = "p.status = 'published'
                                       AND (p.private_ids IS NULL OR json_length(p.private_ids) = 0)";

    /********** ADMIN **********/

    /**
     * used for delete & bulk delete posts
     */
    public function delete_posts(array $ids): void
    {
        $in = [];
        $params = [];
        $i = 0;
        foreach ($ids as $id):
            $key = "id$i";
            $in[] = ":$key";
            $params[$key] = $id;
            $i++;
        endforeach;
        $in = implode(', ', $in);

        $list_label = "la publication {$ids[0]}";
        if (count($ids) > 1):
            $list = implode(', ', $ids);
            $list_label = "les publications $list";
        endif;

        $this->execute_query(
            sql_query: "DELETE FROM nk_$this->table WHERE id IN ($in) AND status = 'trashed'",
            params: $params,
            method: null,
            message: "Impossible de supprimer $list_label de la table $this->table."
        );
    }

    /**
     * used for admin post indexes
     */
    public function count_by_status(): array
    {
        $results = $this->fetch_array(
            "SELECT status, COUNT(id) as nb
             FROM nk_$this->table
             GROUP BY status"
        );

        $table = [];
        if (!empty($results)):
            foreach ($results as $result):
                $table[$result['status']] = $result['nb'];
            endforeach;
        endif;
        return $table;
    }

    /**
     * used for update categories & photos
     */
    public function insert_private_ids(array $ids, array $private_ids, string $message = ''): void
    {
        if (empty($ids) || empty($private_ids)):
            return;
        endif;

        $params = [];

        $in = [];
        $i = 0;
        foreach ($ids as $id):
            $key = "id$i";
            $in[] = ":$key";
            $params[$key] = $id;
            $i++;
        endforeach;
        $in = implode(', ', $in);
        $ids_list = implode(', ', $ids);

        foreach ($private_ids as $private_id):
            $private_id = htmlentities($private_id);

            $this->execute_query(
                sql_query:
                "UPDATE nk_$this->table t
                 JOIN nk_{$this->table}_$this->category_table tc ON t.id = tc.{$this->table}_id
                 SET private_ids = JSON_ARRAY_APPEND(COALESCE(private_ids, JSON_PRETTY('[]')), '$', '$private_id') 
                 WHERE id IN ($in)
                 AND ('$private_id' MEMBER OF (private_ids) = 0 OR private_ids IS NULL)
                 AND tc.{$this->category_table}_id = $private_id",

                params: $params,
                method: null,
                message: "Impossible de modifier la visibilité des publications $ids_list dans la table $this->table."
            );
        endforeach;
    }
    public function remove_private_ids(array $ids, array $private_ids, string $message = ''): void
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
            $private_id = htmlentities($private_id);

            $this->execute_query(
                sql_query:
                "UPDATE nk_$this->table 
             SET private_ids = JSON_REMOVE(private_ids, REPLACE(JSON_SEARCH(private_ids, 'one', '$private_id'), '\"', ''))
             WHERE id IN ($in)
             AND ('$private_id' MEMBER OF (private_ids) = 1 OR private_ids IS NOT NULL)",

                params: $params,
                method: null,
                message: "Impossible de modifier la visibilité des publications $ids_list de la table $this->table liées à la publication $private_id de la table $this->category_table."
            );
        endforeach;

    }
}
