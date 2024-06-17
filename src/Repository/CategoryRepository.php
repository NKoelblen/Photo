<?php
namespace App\Repository;

use App\Entity\CategoryEntity;
use App\Repository\Exception\NotFoundException;
use PDO;

final class CategoryRepository extends RecursiveRepository
{
    protected ?string $table = 'category';
    protected ?string $entity = CategoryEntity::class;

    /**
     * used for edit, bulk_edit, trash, bulk trash, restore & bulk restore recursive
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
            endforeach;
            $in = implode(', ', $in);

            $query = $this->pdo->prepare(
                "WITH RECURSIVE descendants AS (
                     SELECT id
                     FROM nk_$this->table
                     WHERE id IN ($in)
                     UNION ALL
                     SELECT $this->table.id
                     FROM nk_$this->table $this->table
                     JOIN descendants ON $this->table.parent_id = descendants.id
                 )
                 SELECT * FROM descendants"
            );
            $query->execute($params);
            $descendants_ids = $query->fetchAll(PDO::FETCH_COLUMN);
            $ids_list = implode(', ', $children_ids);
            if ($descendants_ids === false):
                throw new NotFoundException($this->table, $ids_list);
            endif;

            $ids_list = htmlentities(implode(', ', $descendants_ids));
            $this->update_posts($descendants_ids, ['private' => 1], "Impossible de modifier la visibilité des catégories $ids_list");

            return $descendants_ids;
        endif;

        return [];
    }

    /**
     * used for edit category
     */
    public function find_category(string $field, mixed $value): CategoryEntity
    {
        $field = htmlentities($field);
        $query = $this->pdo->prepare(
            "SELECT
                 $this->table.id,
                 $this->table.title,
                 $this->table.status,
                 $this->table.private,
                 JSON_OBJECT('id', $this->table.parent_id) AS parent,
                 IF(
                     COUNT(children.id) = 0,
                     NULL,
                     JSON_ARRAYAGG(JSON_OBJECT('id', children.id))
                 ) AS children
             FROM nk_$this->table $this->table
             LEFT JOIN nk_$this->table children ON $this->table.id = children.parent_id
             WHERE $this->table.status != 'trashed'
             AND $this->table.$field = :value
             GROUP BY $this->table.id"
        );
        $query->execute(compact('value'));
        $query->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        $result = $query->fetch();
        if ($result === false):
            throw new NotFoundException($this->table, $value);
        endif;
        return $result;
    }

    /**
     * SAME AS FIND_LOCATION
     * 
     * used for trash & bulk_trash categories
     */
    public function find_categories(array $ids): array
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
                 $this->table.parent_id,
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

    /**
     * SAME AS FIND_PAGINATED_LOCATIONS
     * 
     * used for admin index of published categories
     * 
     * @return array[Pagination, CategoryEntity[]]
     */
    public function find_paginated_categories(string $status = 'published', string $order = 'path ASC', int $per_page = 20): array
    {
        $order = htmlentities($order);
        $pagination = new Pagination(
            "WITH RECURSIVE ascendants AS (
                 SELECT id, title, slug, private, title AS path, 0 AS level
                 FROM nk_$this->table
                 WHERE status = :status
                 AND parent_id IS NULL
                 UNION ALL
                 SELECT
                     $this->table.id,
                     $this->table.title,
                     $this->table.slug,
                     $this->table.private,
                     CONCAT(ascendants.path, ' > ', $this->table.title),
                     ascendants.level + 1
                 FROM nk_$this->table $this->table
                 JOIN ascendants ON $this->table.parent_id = ascendants.id
                 WHERE $this->table.status != 'trashed'
             )
             SELECT * FROM ascendants",
            "SELECT COUNT(id)
             FROM nk_$this->table
             WHERE status = :status",
            compact('status'),
            $order,
            $per_page
        );
        $entities = $pagination->get_entities($this->entity, $this->table);
        return [$pagination, $entities];
    }

    public function find_private_categories_ids(array $ids)
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
            "SELECT id
             FROM nk_$this->table
             WHERE id IN ($in)
             AND private = 1"
        );
        $query->execute($ids_params);
        $result = $query->fetchAll(PDO::FETCH_COLUMN);
        if ($result === false):
            $ids_list = implode(', ', $ids);
            throw new NotFoundException($this->table, $ids_list);
        endif;
        return $result;
    }

    /**
     * SAME AS FIND_PAGINATED_TRASHED_LOCATIONS
     * 
     * used for admin index of trashed categories
     * 
     * @return array[Pagination, CategoryEntity[]]
     */
    public function find_paginated_trashed_categories(string $order = 'title ASC', int $per_page = 20): array
    {
        $order = htmlentities($order);
        $pagination = new Pagination(
            "SELECT id, title, slug, private
             FROM nk_$this->table
             WHERE status = 'trashed'",
            "SELECT COUNT(id)
             FROM nk_$this->table
             WHERE status = 'trashed'",
            [],
            $order,
            $per_page
        );
        $entities = $pagination->get_entities($this->entity, $this->table);
        return [$pagination, $entities];
    }

    /**
     * SAME AS LIST_LOCATIONS
     * 
     * used for new & edit category
     */
    public function list_categories(): array
    {
        $query = $this->pdo->prepare(
            "WITH RECURSIVE ascendants AS (
                 SELECT id, title, private, parent_id, title AS path, 0 AS level
                 FROM nk_$this->table
                 WHERE status = 'published'
                 AND parent_id IS NULL
                 UNION ALL
                 SELECT
                     $this->table.id,
                     $this->table.title,
                     $this->table.private,
                     $this->table.parent_id,
                     CONCAT(ascendants.path, ' > ', $this->table.title),
                     ascendants.level + 1
                 FROM nk_$this->table $this->table
                 JOIN ascendants ON $this->table.parent_id = ascendants.id
                 WHERE $this->table.status = 'published'
             )
             SELECT id, title, private, parent_id, level FROM ascendants ORDER BY path"
        );
        $query->execute();
        $entities = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
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
     * used for edit photo
     */
    public function list_for_edit_photo(): array
    {
        $query = $this->pdo->prepare(
            "WITH RECURSIVE ascendants AS (
                 SELECT id, title, private, JSON_ARRAY(JSON_OBJECT('id', id, 'private', private)) AS ascendants, title AS path, 0 AS level
                 FROM nk_$this->table
                 WHERE status = 'published'
                 AND parent_id IS NULL
                 UNION ALL
                 SELECT
                     $this->table.id,
                     $this->table.title,
                     $this->table.private,
                     JSON_ARRAY_APPEND(ascendants.ascendants, '$', JSON_OBJECT('id', $this->table.id, 'private', $this->table.private)),
                     CONCAT(ascendants.path, ' > ', $this->table.title),
                     ascendants.level + 1
                 FROM nk_$this->table $this->table
                 JOIN ascendants ON $this->table.parent_id = ascendants.id
                 WHERE $this->table.status = 'published'
             )
             SELECT ascendants.title, MIN(ascendants.private) AS private, MIN(ascendants.ascendants) AS ascendants, MIN(level) AS level, COUNT(children.id) AS children_nb
             FROM ascendants
             LEFT JOIN nk_$this->table children ON ascendants.id = children.parent_id
             GROUP BY ascendants.title
             ORDER BY MIN(path)"
        );
        $query->execute();
        /**
         * @var CategoryEntity[]
         */
        $entities = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
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

    /**
     * used for public filters
     */
    public function list_allowed(): array
    {
        $query = $this->pdo->prepare(
            "WITH RECURSIVE ascendants AS (
                 SELECT id, title, title AS path, 0 AS level
                 FROM nk_$this->table
                 WHERE status = 'published'
                 AND (private IS NULL OR private = 0)
                 AND parent_id IS NULL
                 UNION ALL
                 SELECT
                     $this->table.id,
                     $this->table.title,
                     CONCAT(ascendants.path, ' > ', $this->table.title),
                     ascendants.level + 1
                 FROM nk_$this->table $this->table
                 JOIN ascendants ON $this->table.parent_id = ascendants.id
                 WHERE $this->table.status = 'published'
                 AND ($this->table.private IS NULL OR $this->table.private = 0)
             )
             SELECT ascendants.id, ascendants.title, ascendants.level
             FROM ascendants
             LEFT JOIN nk_$this->table children ON ascendants.id = children.parent_id
             ORDER BY ascendants.path"
        );
        $query->execute();
        /**
         * @var CategoryEntity[]
         */
        $entities = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
        $list = [];
        foreach ($entities as $entity):
            $list[$entity->get_id()] = str_repeat('– ', $entity->get_level()) . $entity->get_title();
        endforeach;
        return $list;
    }

    /**
     * used in filters
     */
    public function list_allowed_categories(): array
    {
        $query = $this->pdo->prepare(
            "WITH RECURSIVE ascendants AS (
                 SELECT id, title, parent_id, title AS path, 0 AS level
                 FROM nk_$this->table
                 WHERE status = 'published'
                 AND private = 0
                 AND parent_id IS NULL
                 UNION ALL
                 SELECT
                     $this->table.id,
                     $this->table.title,
                     $this->table.parent_id,
                     CONCAT(ascendants.path, ' > ', $this->table.title),
                     ascendants.level + 1
                 FROM nk_$this->table $this->table
                 JOIN ascendants ON $this->table.parent_id = ascendants.id
                 WHERE $this->table.status = 'published'
                 AND $this->table.private = 0
             )
             SELECT * FROM ascendants ORDER BY path"
        );
        $query->execute();
        $entities = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
        $list = [];
        foreach ($entities as $entity):
            $list[$entity->get_id()] = [
                'label' => str_repeat('–', $entity->get_level()) . ' ' . $entity->get_title(),
                'parent_id' => $entity->get_parent_id()
            ];
        endforeach;
        return $list;
    }

    /**
     * used for show category
     */
    public function find_allowed_category(string $field, mixed $value): CategoryEntity
    {
        $field = htmlentities($field);
        $query = $this->pdo->prepare(
            "SELECT DISTINCT
                 $this->table.id,
                 $this->table.title,
                 $this->table.slug,
                 $this->table.parent_id,
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
                 (WITH RECURSIVE parent AS (
                     SELECT parent.parent_id, parent.title, parent.slug
                     FROM nk_$this->table parent
                     WHERE parent.status = 'published'
                     AND parent.private = 0
                     AND parent.id = $this->table.parent_id
                     UNION ALL
                     SELECT
                         grandparent.parent_id,
                         grandparent.title,
                         grandparent.slug
                     FROM nk_$this->table grandparent
                     JOIN parent ON grandparent.id = parent.parent_id
                     WHERE grandparent.status = 'published'
                     AND grandparent.private = 0
                     )
                 SELECT
                     JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'title', title,
                            'slug', slug
                        )
                    )
                 FROM parent
                 GROUP BY $this->table.id) AS ascendants,
                 (SELECT JSON_OBJECT('path', photo.path, 'description', photo.description)
                  FROM nk_photo_$this->table photo_$this->table
                  JOIN nk_photo photo ON photo_$this->table.photo_id = photo.id
                  WHERE photo_$this->table.{$this->table}_id = $this->table.id
                  AND photo.status = 'published'
                  AND photo.private_ids IS NULL
                  ORDER BY RAND()
                  LIMIT 1) AS thumbnail
             FROM nk_$this->table $this->table
             LEFT JOIN (
                 SELECT DISTINCT
                 children.title,
                 children.slug,
                 children.parent_id,
                 children.status,
                 MIN(photo.thumbnail) AS thumbnail,
                 JSON_PRETTY(
                     CONCAT(
                         '[',
                         GROUP_CONCAT(
                            DISTINCT CONCAT('{\"title\": \"', grandchildren.title, '\", \"slug\": \"', grandchildren.slug, '\"}')
                            ORDER BY grandchildren.title
                            SEPARATOR ', '
                         ),
                         ']'
                     )
                 ) AS children
                 FROM nk_$this->table children
                 LEFT JOIN 
                 (
                     SELECT photo_$this->table.{$this->table}_id, FIRST_VALUE(JSON_OBJECT('path', photo.path, 'description', photo.description)) OVER (PARTITION BY photo_$this->table.{$this->table}_id ORDER BY RAND()) AS thumbnail
                     FROM nk_photo_$this->table photo_$this->table
                     JOIN nk_photo photo ON photo_$this->table.photo_id = photo.id
                     WHERE photo.status = 'published'
                     AND photo.private_ids IS NULL
                 ) photo ON photo.{$this->table}_id = children.id
                 LEFT JOIN nk_$this->table grandchildren ON children.id = grandchildren.parent_id
                 WHERE children.status = 'published'
                 AND (grandchildren.status = 'published' OR grandchildren.status IS NULL)
                 AND (children.private = 0 OR children.private IS NULL)
                 AND (grandchildren.private = 0 OR grandchildren.private IS NULL)
                 GROUP BY children.id
                 ORDER BY children.title
             ) children ON $this->table.id = children.parent_id
             WHERE $this->table.status = 'published'
             AND (children.status = 'published' OR children.status IS NULL)
             AND ($this->table.private = 0 OR $this->table.private IS NULL)
             AND $this->table.$field = :value
             GROUP BY $this->table.id"
        );
        $query->execute(compact('value'));
        $query->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        $result = $query->fetch();
        if ($result === false):
            throw new NotFoundException($this->table, $value);
        endif;
        return $result;
    }

    /**
     * used for public indexes of categories
     * 
     * @return CategoryEntity[]
     */
    public function find_allowed_roots_categories(): array
    {
        $query = $this->pdo->prepare(
            "SELECT DISTINCT
                 $this->table.title,
                 $this->table.slug,
                 JSON_PRETTY(
                     CONCAT(
                         '[',
                         GROUP_CONCAT(
                            CONCAT('{\"title\": \"', children.title, '\", \"slug\": \"', children.slug, '\"}')
                            ORDER BY children.title
                            SEPARATOR ', '
                         ),
                         ']'
                     )
                 ) AS children,
                 (SELECT JSON_OBJECT('path', photo.path, 'description', photo.description)
                  FROM nk_photo_$this->table photo_$this->table
                  JOIN nk_photo photo ON photo_$this->table.photo_id = photo.id
                  WHERE photo_$this->table.{$this->table}_id = $this->table.id
                  AND photo.status = 'published'
                  AND photo.private_ids IS NULL
                  ORDER BY RAND()
                  LIMIT 1) AS thumbnail
             FROM nk_$this->table $this->table
             LEFT JOIN nk_$this->table children ON $this->table.id = children.parent_id
             WHERE $this->table.status = 'published'
             AND (children.status = 'published' OR children.status IS NULL)
             AND $this->table.private = 0
             AND (children.private = 0 OR children.private IS NULL)
             AND $this->table.parent_id IS NULL
             GROUP BY $this->table.id
             ORDER BY $this->table.title"
        );
        $query->execute();
        $entities = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
        return $entities;
    }
}
