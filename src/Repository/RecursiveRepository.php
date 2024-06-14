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
            $this->update_recursives($children_ids, ['parent_id' => $id], null, "Impossible de modifier le parent des publications $ids_list dans la table $this->table.");
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
                $this->update_recursives($children_ids, ['parent_id' => $id], null, "Impossible de modifier le parent des publications $ids_list dans la table $this->table.");
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

    /**
     * used for edit photo
     */
    public function list_for_edit_photo(): array
    {
        $query = $this->pdo->prepare(
            "WITH RECURSIVE ascendants AS (
                 SELECT id, title, CAST(id AS CHAR(255)) AS ascendants_ids, title AS path, 0 AS level
                 FROM nk_$this->table
                 WHERE status = 'published'
                 AND parent_id IS NULL
                 UNION ALL
                 SELECT
                     $this->table.id,
                     $this->table.title,
                     CONCAT(ascendants.ascendants_ids, ', ', $this->table.id),
                     CONCAT(ascendants.path, ' > ', $this->table.title),
                     ascendants.level + 1
                 FROM nk_$this->table $this->table
                 JOIN ascendants ON $this->table.parent_id = ascendants.id
                 WHERE $this->table.status = 'published'
             )
             SELECT ascendants.title, MIN(JSON_PRETTY(CONCAT('[', ascendants_ids, ']'))) AS ascendants_ids, MIN(level) AS level, COUNT(children.id) AS children_nb
             FROM ascendants
             LEFT JOIN nk_$this->table children ON ascendants.id = children.parent_id
             GROUP BY ascendants.title
             ORDER BY MIN(path)"
        );
        $query->execute();
        /**
         * @var RecursiveEntity[]
         */
        $entities = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
        $list = [];
        foreach ($entities as $entity):
            $list[implode(',', $entity->get_ascendants_ids())] = [
                'label' => str_repeat('– ', $entity->get_level()) . $entity->get_title(),
                'children' => $entity->has_children()
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
         * @var RecursiveEntity[]
         */
        $entities = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
        $list = [];
        foreach ($entities as $entity):
            $list[$entity->get_id()] = str_repeat('– ', $entity->get_level()) . $entity->get_title();
        endforeach;
        return $list;
    }
}
