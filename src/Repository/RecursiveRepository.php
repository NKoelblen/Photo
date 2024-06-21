<?php
namespace App\Repository;

use App\Entity\RecursiveEntity;

abstract class RecursiveRepository extends CollectionRepository
{
    /********** ADMIN **********/

    /**
     * used for new recursive
     */
    public function create_recursive(array $datas, ?array $children_ids): int
    {
        $id = $this->create($datas);

        if ($children_ids):
            $ids_list = htmlentities(implode(', ', $children_ids));
            $this->update(
                ids: $children_ids,
                datas: ['parent_id' => $id],
                message: "Impossible de modifier le parent des publications $ids_list dans la table $this->table."
            );
        endif;

        return $id;
    }

    /**
     * used for edit
     */
    public function find(array $columns, string $field, mixed $value): RecursiveEntity
    {
        $t_columns = [];
        foreach ($columns as $column):
            $t_columns[] = "t.$column";
        endforeach;
        $t_columns = implode(', ', $t_columns);
        $field = htmlentities($field);

        return $this->fetch_entity(
            sql_query:
            "SELECT
                 t.id,
                 t.title,
                 t.status,
                 $t_columns,
                 JSON_OBJECT('id', t.parent_id) AS parent,
                 IF(
                     COUNT(children.id) = 0,
                     NULL,
                     JSON_ARRAYAGG(JSON_OBJECT('id', children.id))
                 ) AS children
             FROM nk_$this->table t
             LEFT JOIN nk_$this->table children ON t.id = children.parent_id
             WHERE t.status != 'trashed'
             AND t.$field = :value
             GROUP BY t.id",

            params: compact('value')
        );
    }

    /**
     * used for edit, bulk_edit, trash, bulk trash, restore & bulk restore recursive
     */
    public function update_recursives(array $ids, array $datas, ?array $children_ids = null, string $message = null): void
    {
        $this->update($ids, $datas, $message);

        if ($children_ids):
            foreach ($ids as $id):
                $ids_list = htmlentities(implode(', ', $children_ids));
                $this->update(
                    ids: $children_ids,
                    datas: ['parent_id' => $id],
                    message: "Impossible de modifier le parent des publications $ids_list dans la table $this->table."
                );
            endforeach;
        endif;
    }

    /**
     * @return RecursiveEntity[]
     */
    protected function find_to_list(array $columns = []): array
    {
        $t_columns = [];
        foreach ($columns as $column):
            $t_columns[] = "t.$column";
        endforeach;
        $t_columns = !empty($t_columns) ? implode(', ', $t_columns) . ', ' : '';

        return $this->fetch_entities(
            sql_query:
            "WITH RECURSIVE tree AS (
                 SELECT 
                     id, 
                     title, 
                     parent_id,
                     $t_columns
                     title AS path, 
                     0 AS level
                 FROM nk_$this->table t
                 WHERE status = 'published'
                 AND parent_id IS NULL

                 UNION ALL
                 
                 SELECT
                     t.id,
                     t.title,
                     t.parent_id,
                     $t_columns
                     CONCAT(tree.path, ' > ', t.title),
                     tree.level + 1
                 FROM nk_$this->table t
                 JOIN tree ON t.parent_id = tree.id
                 WHERE t.status = 'published'
             )
             SELECT id, title, parent_id, $t_columns level FROM tree t ORDER BY path"
        );
    }

    /**
     * used for draft & trash recursives
     */
    public function find_all(array $ids): array
    {
        $in = [];
        $params = [];
        foreach ($ids as $id):
            $key = ":id" . $id;
            $in[] = $key;
            $params[$key] = $id;
        endforeach;
        $in = implode(', ', $in);

        return $this->fetch_entities(
            sql_query:
            "SELECT
                 t.id,
                 t.parent_id,
                 IF(
                     COUNT(children.id) = 0,
                     NULL,
                     JSON_ARRAYAGG(children.id)
                 ) AS children_ids
             FROM nk_$this->table t
             JOIN nk_$this->table children ON t.id = children.parent_id
             WHERE t.id IN ($in)
             GROUP BY t.id",

            params: $params
        );
    }

    /**
     * used for admin published index
     * 
     * @param string[] $columns
     * @return array[Pagination, RecursiveEntity[]]
     */
    public function find_paginated_recursives(array $columns = [], string $order = 'path ASC', int $per_page = 20): array
    {
        $t_columns = [];
        foreach ($columns as $column):
            $t_columns[] = "t.$column";
        endforeach;
        $t_columns = !empty($t_columns) ? implode(', ', $t_columns) . ', ' : '';

        return $this->fetch_paginated_entities(
            query:
            "WITH RECURSIVE tree AS (
                 SELECT
                     id,
                     title,
                     slug,
                     $t_columns
                     title AS path,
                     0 AS level
                 FROM nk_$this->table t
                 WHERE status = 'published'
                 AND parent_id IS NULL

                 UNION ALL
                 
                 SELECT
                     t.id,
                     t.title,
                     t.slug,
                     $t_columns
                     CONCAT(tree.path, ' > ', t.title),
                     tree.level + 1
                 FROM nk_$this->table t
                 JOIN tree ON t.parent_id = tree.id
                 WHERE t.status = 'published'
             )
             SELECT * FROM tree",

            query_count:
            "SELECT COUNT(id)
             FROM nk_$this->table
             WHERE status = 'published'",

            order: htmlentities($order),
            per_page: $per_page
        );
    }

    /**
     * used for admin filters
     */
    public function filter(): array
    {
        $entities = $this->fetch_entities(
            sql_query:
            "WITH RECURSIVE tree AS (
                 SELECT 
                     id, 
                     title, 
                     parent_id, 
                     title AS path, 
                     0 AS level
                 FROM nk_$this->table
                 WHERE status = 'published'
                 AND parent_id IS NULL

                 UNION ALL
                 
                 SELECT
                     t.id,
                     t.title,
                     t.parent_id,
                     CONCAT(tree.path, ' > ', t.title),
                     tree.level + 1
                 FROM nk_$this->table t
                 JOIN tree ON t.parent_id = tree.id
                 WHERE t.status = 'published'
             )
             SELECT id, title, parent_id, level FROM tree ORDER BY path"

        );

        $list = [];
        foreach ($entities as $entity):
            $list[$entity->get_id()] = [
                'label' => str_repeat('– ', $entity->get_level()) . $entity->get_title(),
            ];
        endforeach;
        return $list;
    }

    protected function join_thumbnail_subquery($id): string
    {
        return "JOIN 
                (
                    SELECT DISTINCT
                        pt.{$this->table}_id, 
                        FIRST_VALUE(JSON_OBJECT('path', p.path, 'description', p.description)) OVER (PARTITION BY pt.{$this->table}_id ORDER BY RAND()) AS thumbnail
                    FROM nk_{$this->photo_table}_$this->table pt
                    JOIN nk_$this->photo_table p ON pt.{$this->photo_table}_id = p.id
                    WHERE $this->photo_allowed
                ) p ON p.{$this->table}_id = $id";
    }
}
