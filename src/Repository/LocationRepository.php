<?php
namespace App\Repository;

use App\Entity\LocationEntity;
use App\Repository\Exception\NotFoundException;
use Exception;
use PDO;

final class LocationRepository extends RecursiveRepository
{
    protected ?string $table = 'location';
    protected ?string $entity = LocationEntity::class;

    /**
     * used for edit location
     */
    public function find_location(string $field, mixed $value): LocationEntity
    {
        $field = htmlentities($field);
        $query = $this->pdo->prepare(
            "SELECT
                 $this->table.id,
                 MIN($this->table.title) AS title,
                 MIN($this->table.status) AS status,
                 MIN($this->table.coordinates) AS coordinates,
                 MIN($this->table.parent_id) AS parent_id,
                 IF(
                     COUNT(children.id) = 0,
                     NULL,
                     JSON_ARRAYAGG(children.id)
                 ) AS children_ids
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
     * used for trash & bulk_trash locations
     */
    public function find_locations(array $ids): array
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

    /**
     * used for admin index of published locations
     * 
     * @return array[Pagination, LocationEntity[]]
     */
    public function find_paginated_locations(string $status = 'published', string $order = 'path ASC', int $per_page = 20): array
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

    /**
     * used for admin index of trashed locations
     * 
     * @return array[Pagination, LocationEntity[]]
     */
    public function find_paginated_trashed_locations(string $order = 'title ASC', int $per_page = 20): array
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
     * used for new & edit location
     */
    public function list_locations(): array
    {
        $query = $this->pdo->prepare(
            "WITH RECURSIVE ascendants AS (
                 SELECT id, title, parent_id, title AS path, 0 AS level
                 FROM nk_$this->table
                 WHERE status = 'published'
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
             )
             SELECT id, title, parent_id, level FROM ascendants ORDER BY path"
        );
        $query->execute();
        $entities = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
        $list = [];
        foreach ($entities as $entity):
            $list[$entity->get_id()] = [
                'label' => str_repeat('– ', $entity->get_level()) . $entity->get_title(),
                'parent_id' => $entity->get_parent_id()
            ];
        endforeach;
        return $list;
    }

    /**
     * used in filters
     */
    public function list_allowed_locations(): array
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
     * used for public filters
     */
    public function list_allowed(): array
    {
        $query = $this->pdo->prepare(
            "WITH RECURSIVE ascendants AS (
                 SELECT id, title, title AS path, 0 AS level
                 FROM nk_$this->table
                 WHERE status = 'published'
                 AND private = 0
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
                 AND $this->table.private = 0
             )
             SELECT ascendants.id, ascendants.title, ascendants.level
             FROM ascendants
             LEFT JOIN nk_$this->table children ON ascendants.id = children.parent_id
             ORDER BY ascendants.path"
        );
        $query->execute();
        /**
         * @var LocationEntity[]
         */
        $entities = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
        $list = [];
        foreach ($entities as $entity):
            $list[$entity->get_id()] = str_repeat('– ', $entity->get_level()) . $entity->get_title();
        endforeach;
        return $list;
    }

    /**
     * used for show location
     */
    public function find_allowed_location(string $field, mixed $value): LocationEntity
    {
        $field = htmlentities($field);
        $query = $this->pdo->prepare(
            "SELECT DISTINCT
                 $this->table.id,
                 MIN($this->table.title) AS title,
                 MIN($this->table.slug) AS slug,
                 MIN($this->table.parent_id) AS parent_id,
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
                 AND children.private = 0
                 AND (grandchildren.private = 0 OR grandchildren.private IS NULL)
                 GROUP BY children.id
                 ORDER BY children.title
             ) children ON $this->table.id = children.parent_id
             WHERE $this->table.status = 'published'
             AND (children.status = 'published' OR children.status IS NULL)
             AND $this->table.private = 0
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
     * used for public indexes of locations
     * 
     * @return LocationEntity[]
     */
    public function find_allowed_roots_locations(): array
    {
        $query = $this->pdo->prepare(
            "SELECT DISTINCT
                 $this->table.title,
                 MIN($this->table.slug) AS slug,
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
             AND children.status = 'published'
             AND $this->table.private = 0
             AND children.private = 0
             AND $this->table.parent_id IS NULL
             GROUP BY $this->table.id
             ORDER BY $this->table.title"
        );
        $query->execute();
        $entities = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
        return $entities;
    }

    /**
     * used for public index maps
     */
    public function find_allowed_orphans_locations(): array
    {
        $query = $this->pdo->prepare(
            "SELECT DISTINCT
                 $this->table.title,
                 $this->table.slug,
                 $this->table.coordinates,
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
             WHERE children.id IS NULL
             AND $this->table.status = 'published'
             AND $this->table.private = 0"
        );
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * used for public show map
     * 
     * @return LocationEntity[]
     */
    public function find_allowed_descendant_orphans_locations(int $id): array
    {
        $query = $this->pdo->prepare(
            "WITH RECURSIVE children AS (
                 SELECT children.id, children.title, children.slug, children.coordinates, grandchildren.id AS children
                 FROM nk_$this->table children
                 LEFT JOIN nk_$this->table grandchildren ON children.id = grandchildren.parent_id
                 WHERE children.status = 'published'
                 AND children.private = 0
                 AND children.parent_id = :id
                 UNION ALL
                 SELECT descendants.id, descendants.title, descendants.slug, descendants.coordinates, granddescendants.id
                 FROM nk_$this->table descendants
                 JOIN children ON children.id = descendants.parent_id
                 LEFT JOIN nk_$this->table granddescendants ON descendants.id = granddescendants.parent_id
                 WHERE descendants.status = 'published'
                 AND descendants.private = 0
             )
             SELECT DISTINCT children.title, children.slug, children.coordinates, FIRST_VALUE(photo.thumbnail) OVER (PARTITION BY children.id ORDER BY RAND()) AS thumbnail
             FROM children
             JOIN (SELECT photo_$this->table.{$this->table}_id, JSON_OBJECT('path', photo.path, 'description', photo.description) AS thumbnail
                   FROM nk_photo_$this->table photo_$this->table
                   JOIN nk_photo photo ON photo_$this->table.photo_id = photo.id
                   WHERE photo.status = 'published'
                   AND photo.private_ids IS NULL) photo ON photo.{$this->table}_id = children.id
             WHERE children IS NULL"
        );
        $query->execute(compact('id'));
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * used for public show map
     * 
     * @return LocationEntity[]
     */
    public function find_allowed_siblings_orphans_locations(int $parent_id): array
    {
        $query = $this->pdo->prepare(
            "WITH RECURSIVE siblings AS (
                 SELECT siblings.id, siblings.title, siblings.slug, siblings.coordinates, children.id AS children
                 FROM nk_$this->table siblings
                 LEFT JOIN nk_$this->table children ON siblings.id = children.parent_id
                 WHERE siblings.status = 'published'
                 AND siblings.private = 0
                 AND siblings.parent_id = :parent_id
                 UNION ALL
                 SELECT descendants.id, descendants.title, descendants.slug, descendants.coordinates, granddescendants.id
                 FROM nk_$this->table descendants
                 JOIN siblings ON siblings.id = descendants.parent_id
                 LEFT JOIN nk_$this->table granddescendants ON descendants.id = granddescendants.parent_id
                 WHERE descendants.status = 'published'
                 AND descendants.private = 0
             )
             SELECT DISTINCT title, slug, coordinates,
                 (SELECT JSON_OBJECT('path', photo.path, 'description', photo.description)
                  FROM nk_photo_$this->table photo_$this->table
                  JOIN nk_photo photo ON photo_$this->table.photo_id = photo.id
                  WHERE photo_$this->table.{$this->table}_id = siblings.id
                  AND photo.status = 'published'
                  AND photo.private_ids IS NULL
                  ORDER BY RAND()
                  LIMIT 1) AS thumbnail
             FROM siblings
             WHERE children IS NULL"
        );
        $query->execute(compact('parent_id'));
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * use to update private_ids
     */
    public function find_categories_location_ids(array $photo_ids, array $categories_ids, int $contains): array
    {
        if (empty($photo_ids) || empty($categories_ids)):
            return [];
        endif;

        $params = compact('contains');

        $i = 0;
        $in = [];
        foreach ($photo_ids as $photo_id):
            $key = ":id" . $i;
            $in[] = $key;
            $params[$key] = $photo_id;
            $i++;
        endforeach;
        $in = implode(', ', $in);

        foreach ($categories_ids as $category_id):
            $categories_list[] = (string) $category_id;
        endforeach;
        $categories_list = json_encode($categories_list);

        $query = $this->pdo->prepare(
            "SELECT DISTINCT location.id
             FROM nk_photo photo
             JOIN nk_photo_location photo_location ON photo.id = photo_location.photo_id
             JOIN nk_location location ON photo_location.location_id = location.id
             WHERE photo.id IN ($in)
             AND JSON_CONTAINS(JSON_PRETTY(COALESCE(location.private_ids, '[]')), '$categories_list') = :contains
             GROUP BY location.id
             HAVING SUM(JSON_CONTAINS(JSON_PRETTY(COALESCE(photo.private_ids, '[]')), '$categories_list')) < 2"
        );
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * use to update private
     */
    public function find_categories_location_visibility(array $photo_ids): array
    {
        if (empty($photo_ids)):
            return [];
        endif;

        $params = [];

        $i = 0;
        $in = [];
        foreach ($photo_ids as $photo_id):
            $key = ":id" . $i;
            $in[] = $key;
            $params[$key] = $photo_id;
            $i++;
        endforeach;
        $in = implode(', ', $in);

        $query = $this->pdo->prepare(
            "SELECT DISTINCT location.id, location.private, photo_nb.photos, photo_nb.private_photos
             FROM nk_photo photo
             JOIN nk_photo_location photo_location ON photo.id = photo_location.photo_id
             JOIN nk_location location ON photo_location.location_id = location.id
             JOIN (
                 SELECT photo_location.location_id, COUNT(photo.id) AS photos, COUNT(CASE WHEN photo.private_ids IS NOT NULL THEN photo.id END) AS private_photos
                 FROM nk_photo photo
                 JOIN nk_photo_location photo_location ON photo.id = photo_location.photo_id
                 GROUP BY photo_location.location_id
             ) photo_nb ON photo_location.location_id = photo_nb.location_id
             WHERE photo.id IN ($in)"
        );
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * used for edit photo
     */
    public function list_for_edit_photo(): array
    {
        $query = $this->pdo->prepare(
            "WITH RECURSIVE ascendants AS (
                 SELECT id, title, JSON_ARRAY(id) AS ascendants_ids, title AS path, 0 AS level
                 FROM nk_$this->table
                 WHERE status = 'published'
                 AND parent_id IS NULL
                 UNION ALL
                 SELECT
                     $this->table.id,
                     $this->table.title,
                     JSON_ARRAY_APPEND(ascendants.ascendants_ids, '$', $this->table.id),
                     CONCAT(ascendants.path, ' > ', $this->table.title),
                     ascendants.level + 1
                 FROM nk_$this->table $this->table
                 JOIN ascendants ON $this->table.parent_id = ascendants.id
                 WHERE $this->table.status = 'published'
             )
             SELECT ascendants.title, MIN(ascendants_ids) AS ascendants_ids, MIN(level) AS level, COUNT(children.id) AS children_nb
             FROM ascendants
             LEFT JOIN nk_$this->table children ON ascendants.id = children.parent_id
             GROUP BY ascendants.title
             ORDER BY MIN(path)"
        );
        $query->execute();
        /**
         * @var LocationEntity[]
         */
        $entities = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
        $list = [];
        foreach ($entities as $entity):
            $list[json_encode($entity->get_ascendants_ids())] = [
                'label' => str_repeat('– ', $entity->get_level()) . $entity->get_title(),
                'children' => $entity->has_children()
            ];
        endforeach;
        return $list;
    }
}