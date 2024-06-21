<?php
namespace App\Repository;

use App\Entity\LocationEntity;

final class LocationRepository extends RecursiveRepository
{
    public ?string $table = '';
    protected ?string $entity = LocationEntity::class;

    public function __construct()
    {
        parent::__construct();
        $this->table = $this->location_table;
    }

    /********** ADMIN ***********/

    /**
     * used for new & edit
     */
    public function form_list(): array
    {
        $entities = $this->find_to_list();

        $list = [];
        foreach ($entities as $entity):
            $key = ['id' => $entity->get_id()];
            $list[json_encode($key)] = [
                'label' => str_repeat('– ', $entity->get_level()) . $entity->get_title(),
                'parent_id' => $entity->get_parent_id()
            ];
        endforeach;
        return $list;
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
                     JSON_ARRAY(id) AS ascendants_ids,
                     title AS path,
                     0 AS level
                 FROM nk_$this->table
                 WHERE status = 'published'
                 AND parent_id IS NULL

                 UNION ALL

                 SELECT
                     t.id,
                     t.title,
                     JSON_ARRAY_APPEND(tree.ascendants_ids, '$', t.id),
                     CONCAT(tree.path, ' > ', t.title),
                     tree.level + 1
                 FROM nk_$this->table t
                 JOIN tree ON t.parent_id = tree.id
                 WHERE t.status = 'published'
             )
             SELECT
                 t.title,
                 ascendants_ids,
                 level,
                 COUNT(children.id) OVER (PARTITION BY t.title) AS children_nb
             FROM tree t
             LEFT JOIN nk_$this->table children ON t.id = children.parent_id
             ORDER BY path"
        );
        $list = [];
        foreach ($entities as $entity):
            $list[json_encode($entity->get_ascendants_ids())] = [
                'label' => str_repeat('– ', $entity->get_level()) . $entity->get_title(),
                'children' => $entity->has_children()
            ];
        endforeach;
        return $list;
    }


    /********** PUBLIC ***********/

    /**
     * used for show recursive
     */
    public function find_allowed(string $field, mixed $value): LocationEntity
    {
        $field = htmlentities($field);

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
                         WHERE parent.id = t.parent_id

                         UNION ALL
                         
                         SELECT grandparent.parent_id, grandparent.title, grandparent.slug
                         FROM nk_$this->table grandparent
                         JOIN parent ON grandparent.id = parent.parent_id
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

                 GROUP BY children.id

                 ORDER BY children.title
             ) children ON t.id = children.parent_id
             -- END OF JOIN CHILDREN --

            {$this->join_thumbnail_subquery('t.id')}

             WHERE t.$field = :value

             GROUP BY t.id",

            params: compact('value'),
        );
    }

    /**
     * used for public indexes of recursive
     * 
     * @return LocationEntity[]
     */
    public function find_allowed_roots(): array
    {
        return $this->fetch_entities(
            sql_query:
            "SELECT DISTINCT
                 t.title,
                 t.slug,
                 MIN(p.thumbnail) AS thumbnail,
                 JSON_ARRAYAGG(children.details) AS children
             FROM nk_$this->table t
             {$this->join_thumbnail_subquery('t.id')}
             LEFT JOIN (
                 SELECT DISTINCT parent_id, t.title, JSON_OBJECT('title', t.title, 'slug', t.slug) AS details
                 FROM nk_$this->table t
                 JOIN nk_{$this->photo_table}_$this->table pt ON t.id = pt.{$this->table}_id
                 JOIN nk_$this->photo_table p ON pt.{$this->photo_table}_id = p.id
                 WHERE $this->photo_allowed
                 ORDER BY t.title
             ) children ON t.id = children.parent_id
             WHERE t.parent_id IS NULL
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
            if (isset($filters["{$this->category_table}_id"])):
                $search[] = "pc.{$this->category_table}_id = :{$this->category_table}_id";
            endif;
            if (isset($filters["year"])):
                $search[] = "YEAR(p.created_at) = :year";
            endif;
            if (isset($filters["month"])):
                $search[] = "MONTH(p.created_at) = :month";
            endif;
        endif;
        $search = !empty($search) ? 'AND ' . implode(' AND ', $search) : '';

        $entities = $this->fetch_entities(
            sql_query:
            "WITH RECURSIVE tree AS (
                 SELECT 
                     t.id, 
                     t.title, 
                     t.title AS path, 
                     0 AS level
                 FROM nk_$this->table t
                 WHERE t.parent_id IS NULL

                 UNION ALL

                 SELECT
                     t.id,
                     t.title,
                     CONCAT(tree.path, ' > ', t.title),
                     tree.level + 1
                 FROM nk_$this->table t
                 JOIN tree ON t.parent_id = tree.id
             )
             SELECT tree.id, tree.title, level
             FROM tree
             JOIN nk_{$this->photo_table}_$this->table pt ON tree.id = pt.{$this->table}_id
             JOIN nk_$this->photo_table p ON pt.{$this->photo_table}_id = p.id
             JOIN nk_{$this->photo_table}_$this->category_table pc ON p.id = pc.{$this->photo_table}_id
             WHERE $this->photo_allowed
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

    /**
     * used for public index maps
     */
    public function find_allowed_orphans(): array
    {
        return $this->fetch_array(
            sql_query:
            "SELECT DISTINCT t.title, t.slug, t.coordinates, MIN(p.thumbnail) OVER (PARTITION BY t.title)  AS thumbnail
             FROM nk_$this->table t
             LEFT JOIN nk_$this->table children ON t.id = children.parent_id
             {$this->join_thumbnail_subquery('t.id')}
             WHERE children.id IS NULL",
        );
    }

    /**
     * used for public show map
     * 
     * @return LocationEntity[]
     */
    public function find_allowed_descendant_orphans(int $id): array
    {
        return $this->fetch_array(
            sql_query:
            "WITH RECURSIVE tree AS (
                 SELECT t.id, t.title, t.slug, t.coordinates, children.id AS children
                 FROM nk_$this->table t
                 LEFT JOIN nk_$this->table children ON t.id = children.parent_id
                 WHERE children.parent_id = :id

                 UNION ALL
                 
                 SELECT t.id, t.title, t.slug, t.coordinates, children.id
                 FROM nk_$this->table t
                 JOIN tree ON tree.id = t.parent_id
                 LEFT JOIN nk_$this->table children ON t.id = children.parent_id
             )
             SELECT DISTINCT title, slug, coordinates, p.thumbnail
             FROM tree
             {$this->join_thumbnail_subquery('tree.id')}
             WHERE children IS NULL",

            params: compact('id')
        );
    }
}