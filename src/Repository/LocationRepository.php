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
     * used for new location
     */
    public function create_location(array $datas, ?array $children_ids): int
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

        $id = $this->pdo->lastInsertId();

        if ($children_ids):
            $ids_list = htmlentities(implode(', ', $children_ids));
            $this->update_locations($children_ids, ['parent_id' => $id], null, "Impossible de modifier le parent des publications $ids_list dans la table $this->table.");
        endif;

        return $id;
    }

    /**
     * used for edit, trash, bulk trash, restore & bulk restore locations
     */
    public function update_locations(array $ids, array $datas, ?array $children_ids = null, string $message = null): void
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

        if ($children_ids):
            foreach ($ids as $id):
                $ids_list = htmlentities(implode(', ', $children_ids));
                $this->update_locations($children_ids, ['parent_id' => $id], null, "Impossible de modifier le parent des publications $ids_list dans la table $this->table.");
            endforeach;
        endif;
    }

    /**
     * used for delete & bulk delete locations
     */
    public function delete_locations(array $ids): void
    {
        $in = [];
        $params = [];
        foreach ($ids as $id):
            $key = ":id" . $id;
            $in[] = $key;
            $params[$key] = $id;
        endforeach;
        $in = implode(', ', $in);
        $list = implode(', ', $ids);
        $query = $this->pdo->prepare("DELETE FROM nk_$this->table WHERE id IN ($in)");
        $delete = $query->execute($params);
        if ($delete === false):
            throw new Exception("Impossible de supprimer les publications $list de la table $this->table.");
        endif;
    }

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
                 MIN($this->table.coordinates) AS coordinates,
                 MIN($this->table.parent_id) AS parent_id,
                 IF(
                     COUNT(children.id) = 0,
                     NULL,
                     JSON_ARRAYAGG(children.id)
                 ) AS children_ids
             FROM nk_$this->table $this->table
             LEFT JOIN nk_$this->table children ON $this->table.id = children.parent_id
             WHERE $this->table.status = 'published'
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
    public function find_paginated_locations(string $order = 'path ASC', int $per_page = 20): array
    {
        $order = htmlentities($order);
        $pagination = new Pagination(
            "WITH RECURSIVE ascendants AS (
                 SELECT id, title, slug, private, title AS path, 0 AS level
                 FROM nk_$this->table
                 WHERE status = 'published'
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
                 WHERE $this->table.status = 'published'
             )
             SELECT * FROM ascendants",
            "SELECT COUNT(id)
             FROM nk_$this->table
             WHERE status = 'published'",
            [],
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
     * 
     * @return LocationEntity
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
             SELECT * FROM ascendants ORDER BY path"
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
                 AND private IS NULL
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
                 AND $this->table.private IS NULL
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
                             'children', IFNULL(children.children, 'null')
                         )
                     )
                 ) AS children,
                 (WITH RECURSIVE parent AS (
                     SELECT parent.parent_id, parent.title, parent.slug
                     FROM nk_$this->table parent
                     WHERE parent.status = 'published'
                     AND parent.private IS NULL
                     AND parent.id = $this->table.parent_id
                     UNION ALL
                     SELECT
                         grandparent.parent_id,
                         grandparent.title,
                         grandparent.slug
                     FROM nk_$this->table grandparent
                     JOIN parent ON grandparent.id = parent.parent_id
                     WHERE grandparent.status = 'published'
                     AND grandparent.private IS NULL
                     )
                 SELECT
                     JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'title', title,
                            'slug', slug
                        )
                    )
                 FROM parent
                 GROUP BY $this->table.id) AS ascendants
             FROM nk_$this->table $this->table
             LEFT JOIN (
                 SELECT DISTINCT
                 children.title,
                 MIN(children.slug) AS slug,
                 MIN(children.parent_id) AS parent_id,
                 MIN(children.status) AS status,
                 JSON_PRETTY(
                     CONCAT(
                         '[',
                         GROUP_CONCAT(
                            CONCAT('{\"title\": \"', grandchildren.title, '\", \"slug\": \"', grandchildren.slug, '\"}')
                            ORDER BY grandchildren.title
                            SEPARATOR ', '
                         ),
                         ']'
                     )
                 ) AS children
                 FROM nk_$this->table children
                 LEFT JOIN nk_$this->table grandchildren ON children.id = grandchildren.parent_id
                 WHERE children.status = 'published'
                 AND (grandchildren.status = 'published' OR grandchildren.status IS NULL)
                 AND children.private IS NULL
                 AND grandchildren.private IS NULL
                 GROUP BY children.title
             ) children ON $this->table.id = children.parent_id
             WHERE $this->table.status = 'published'
             AND (children.status = 'published' OR children.status IS NULL)
             AND $this->table.private IS NULL
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
                 ) AS children
             FROM nk_$this->table $this->table
             LEFT JOIN nk_$this->table children ON $this->table.id = children.parent_id
             WHERE $this->table.status = 'published'
             AND children.status = 'published'
             AND $this->table.private IS NULL
             AND children.private IS NULL
             AND $this->table.parent_id IS NULL
             GROUP BY $this->table.title
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
                 $this->table.coordinates
             FROM nk_$this->table $this->table
             LEFT JOIN nk_$this->table children ON $this->table.id = children.parent_id
             WHERE children.id IS NULL
             AND $this->table.status = 'published'
             AND $this->table.private IS NULL"
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
                 AND children.private IS NULL
                 AND children.parent_id = :id
                 UNION ALL
                 SELECT descendants.id, descendants.title, descendants.slug, descendants.coordinates, granddescendants.id
                 FROM nk_$this->table descendants
                 JOIN children ON children.id = descendants.parent_id
                 LEFT JOIN nk_$this->table granddescendants ON descendants.id = granddescendants.parent_id
                 WHERE descendants.status = 'published'
                 AND descendants.private IS NULL
             )
             SELECT DISTINCT title, slug, coordinates
             FROM children
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
                 AND siblings.private IS NULL
                 AND siblings.parent_id = :parent_id
                 UNION ALL
                 SELECT descendants.id, descendants.title, descendants.slug, descendants.coordinates, granddescendants.id
                 FROM nk_$this->table descendants
                 JOIN siblings ON siblings.id = descendants.parent_id
                 LEFT JOIN nk_$this->table granddescendants ON descendants.id = granddescendants.parent_id
                 WHERE descendants.status = 'published'
                 AND descendants.private IS NULL
             )
             SELECT DISTINCT title, slug, coordinates
             FROM siblings
             WHERE children IS NULL"
        );
        $query->execute(compact('parent_id'));
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}