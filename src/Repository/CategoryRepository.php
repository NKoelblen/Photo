<?php
namespace App\Repository;

use App\Entity\CategoryEntity;

final class CategoryRepository extends RecursiveRepository
{
    protected ?string $table = '';
    protected ?string $entity = CategoryEntity::class;
    protected ?string $allowed = null;

    public function __construct()
    {
        parent::__construct();
        $this->table = $this->category_table;
        $this->allowed = str_replace('c.', 't.', $this->category_allowed);
    }

    /********** ADMIN ***********/

    /**
     * used for edit, bulk_edit, trash, bulk trash, restore & bulk restore
     */
    public function update_categories(array $ids, array $datas, ?array $children_ids = null, string $message = null): array
    {
        $this->update_recursives($ids, $datas, $children_ids, $message);

        if ($children_ids && isset($datas['private']) && $datas['private'] == 1):
            $in = [];
            $params = [];
            $i = 0;
            foreach ($children_ids as $child_id):
                $key = "id$i";
                $in[] = ":$key";
                $params[$key] = $child_id;
                $i++;
            endforeach;
            $in = implode(', ', $in);

            $descendants_ids = $this->fetch_column(
                sql_query:
                "WITH RECURSIVE tree AS (
                     SELECT id
                     FROM nk_$this->table
                     WHERE id IN ($in)

                     UNION ALL

                     SELECT t.id
                     FROM nk_$this->table t
                     JOIN tree ON t.parent_id = tree.id
                 )
                 SELECT * FROM tree",

                params: $params
            );

            $ids_list = htmlentities(implode(', ', $descendants_ids));

            $this->update($descendants_ids, ['private' => 1], "Impossible de modifier la visibilité des catégories $ids_list");

            return $descendants_ids;
        endif;

        return [];
    }


    /**
     * used for new & edit
     */
    public function form_list(): array
    {
        $entities = $this->find_to_list(['private']);

        $list = [];
        foreach ($entities as $entity):
            $key = ['id' => $entity->get_id(), 'private' => $entity->get_private()];
            $list[json_encode($key)] = [
                'label' => str_repeat('– ', $entity->get_level()) . $entity->get_title(),
                'parent_id' => $entity->get_parent_id()
            ];
        endforeach;
        return $list;
    }

    /**
     * used to update photo private_ids
     */
    public function private_ids_list(array $ids)
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

        return $this->fetch_column(
            sql_query:
            "SELECT id
             FROM nk_$this->table
             WHERE id IN ($in)
             AND private = 1",

            params: $params
        );
    }

    /**
     * used for edit photo
     */
    public function edit_photo_list(): array
    {
        $entities = $this->fetch_entities(
            "WITH RECURSIVE tree AS (
                 SELECT
                     id,
                     title,
                     private,
                     JSON_ARRAY(JSON_OBJECT('id', id, 'private', private)) AS ascendants,
                     title AS path,
                     0 AS level
                 FROM nk_$this->table
                 WHERE status = 'published'
                 AND parent_id IS NULL

                 UNION ALL

                 SELECT
                     t.id,
                     t.title,
                     t.private,
                     JSON_ARRAY_APPEND(tree.ascendants, '$', JSON_OBJECT('id', t.id, 'private', t.private)),
                     CONCAT(tree.path, ' > ', t.title),
                     tree.level + 1
                 FROM nk_$this->table t
                 JOIN tree ON t.parent_id = tree.id
                 WHERE t.status = 'published'
             )
             SELECT
                 t.title,
                 t.private,
                 ascendants,
                 level,
                 COUNT(children.id) OVER (PARTITION BY t.title) AS children_nb
             FROM tree t
             LEFT JOIN nk_$this->table children ON t.id = children.parent_id
             ORDER BY path"
        );
        $list = [];
        foreach ($entities as $entity):
            $key = [];
            $i = 0;
            foreach ($entity->get_ascendants() as $ascendant):
                $key[$i]['id'] = $ascendant->get_id();
                $key[$i]['private'] = $ascendant->get_private();
                $i++;
            endforeach;
            $list[json_encode($key)] = [
                'label' => str_repeat('– ', $entity->get_level()) . $entity->get_title(),
                'children' => $entity->has_children(),
                'private' => $entity->get_private()
            ];
        endforeach;
        return $list;
    }


    /********** PUBLIC **********/

    /**
     * used for show
     */
    public function find_allowed(string $field, mixed $value): CategoryEntity
    {
        $field = htmlentities($field);

        $parent_allowed = str_replace('t.', 'parent.', $this->allowed);
        $grandparent_allowed = str_replace('t.', 'grandparent.', $this->allowed);
        $children_allowed = str_replace('t.', 'children.', $this->allowed);
        $grandchildren_allowed = str_replace('t.', 'grandchildren.', $this->allowed);

        return $this->fetch_entity(
            sql_query:
            "SELECT DISTINCT
                 t.id,
                 t.title,
                 t.slug,
                 t.parent_id,
                 MIN(p.thumbnail) AS thumbnail,

                 -- GET CHILDREN DETAILS --
                 IF(
                     COUNT(children.title) = 0,
                     NULL,
                     JSON_ARRAYAGG(
                         JSON_OBJECT(
                             'title', children.title,
                             'slug', children.slug,
                             'children', IFNULL(children.children, 'null'),
                             'thumbnail', children.thumbnail
                         )
                     )
                 ) AS children,
                 -- END OF CHILDREN DETAILS --

                 -- GET ASCENDANTS DETAILS --
                 (
                     WITH RECURSIVE parent AS (
                         SELECT parent.parent_id, parent.title, parent.slug
                         FROM nk_$this->table parent
                         WHERE $parent_allowed
                         AND parent.id = t.parent_id

                         UNION ALL
                         
                         SELECT grandparent.parent_id, grandparent.title, grandparent.slug
                         FROM nk_$this->table grandparent
                         JOIN parent ON grandparent.id = parent.parent_id
                         WHERE $grandparent_allowed
                     )
                     SELECT DISTINCT JSON_ARRAYAGG(JSON_OBJECT('title', title, 'slug', slug))
                     FROM parent
                     GROUP BY t.id) AS ascendants
                 -- END OF ASCENDANTS DETAILS --

             FROM nk_$this->table t
             
             -- JOIN CHILDREN --
             LEFT JOIN (
                 SELECT DISTINCT
                     children.title,
                     children.slug,
                     children.parent_id,
                     children.status,
                     MIN(p.thumbnail) AS thumbnail,

                     -- GRANDCHILDREN DETAILS --
                     IF(
                         COUNT(grandchildren.title) = 0,
                         NULL,
                         JSON_PRETTY(
                             CONCAT(
                                 '[',
                                 GROUP_CONCAT(
                                    DISTINCT JSON_OBJECT('title', grandchildren.title, 'slug', grandchildren.slug)
                                    ORDER BY grandchildren.title
                                    SEPARATOR ', '
                                 ),
                                 ']'
                             )
                         )
                     ) AS children
                     -- END OF GRANDCHILDREN DETAILS --

                 FROM nk_$this->table children

                 {$this->join_thumbnail_subquery('children.id')}

                 LEFT JOIN nk_$this->table grandchildren ON children.id = grandchildren.parent_id

                 WHERE $children_allowed
                 AND (($grandchildren_allowed) OR grandchildren.id IS NULL)

                 GROUP BY children.id

                 ORDER BY children.title
             ) children ON t.id = children.parent_id
             -- END OF JOIN CHILDREN --

            {$this->join_thumbnail_subquery('t.id')}

             WHERE $this->allowed
             AND t.$field = :value

             GROUP BY t.id",

            params: compact('value'),
        );
    }
    /**
     * used for public indexes
     * 
     * @return CategoryEntity[]
     */
    public function find_allowed_roots(): array
    {
        $children_allowed = str_replace('t.', 'children.', $this->allowed);

        return $this->fetch_entities(
            sql_query:
            "SELECT DISTINCT
                 t.title,
                 t.slug,
                 MIN(p.thumbnail) AS thumbnail,

                 -- CHILDREN DETAILS --
                 JSON_PRETTY(
                     CONCAT(
                         '[',
                         GROUP_CONCAT(DISTINCT
                            CONCAT('{\"title\": \"', children.title, '\", \"slug\": \"', children.slug, '\"}')
                            ORDER BY children.title
                            SEPARATOR ', '
                         ),
                         ']'
                     )
                 ) AS children
                --  END OF CHILDREN DETAILS --

             FROM nk_$this->table t
             LEFT JOIN nk_$this->table children ON t.id = children.parent_id
             {$this->join_thumbnail_subquery('t.id')}
             WHERE $this->allowed
             AND (($children_allowed) OR children.id IS NULL)
             AND t.parent_id IS NULL
             GROUP BY t.id
             ORDER BY t.title",
        );
    }

    /**
     * used for public filters
     */
    public function filter_allowed(array $filters = []): array
    {
        $search = [];
        if (!empty($filters)):
            if (isset($filters["{$this->album_table}_id"])):
                $search[] = "p.{$this->album_table}_id = :{$this->album_table}_id";
            endif;
            if (isset($filters["{$this->location_table}_id"])):
                $search[] = "pl.{$this->location_table}_id = :{$this->location_table}_id";
            endif;
            if (isset($filters["year"])):
                $search[] = "YEAR(p.created_at) = :year";
            endif;
            if (isset($filters["month"])):
                $search[] = "MONTH(p.created_at) = :month";
            endif;
        endif;
        $search = !empty($search) ? 'WHERE ' . implode(' AND ', $search) : '';

        $entities = $this->fetch_entities(
            sql_query:
            "WITH RECURSIVE tree AS (
                 SELECT 
                     id, 
                     title, 
                     title AS path, 
                     0 AS level
                 FROM nk_$this->table t
                 WHERE $this->allowed
                 AND parent_id IS NULL

                 UNION ALL

                 SELECT
                     t.id,
                     t.title,
                     CONCAT(tree.path, ' > ', t.title),
                     tree.level + 1
                 FROM nk_$this->table t
                 JOIN tree ON t.parent_id = tree.id
                 WHERE $this->allowed
             )
             SELECT tree.id, tree.title, level
             FROM tree
             JOIN nk_{$this->photo_table}_$this->table pt ON tree.id = pt.{$this->table}_id
             JOIN nk_$this->photo_table p ON pt.{$this->photo_table}_id = p.id
             JOIN nk_{$this->photo_table}_$this->location_table pl ON p.id = pl.{$this->photo_table}_id
             $search
             ORDER BY tree.path",

            params: $filters
        );
        $list = [];
        foreach ($entities as $entity):
            $list[$entity->get_id()] = str_repeat('– ', $entity->get_level()) . $entity->get_title();
        endforeach;
        return $list;
    }
}