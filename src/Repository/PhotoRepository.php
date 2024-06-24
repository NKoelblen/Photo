<?php
namespace App\Repository;

use App\Entity\PhotoEntity;
use App\Repository\Exception\NotFoundException;
use Exception;
use PDO;

final class PhotoRepository extends PostRepository
{
    protected ?string $table = '';
    protected ?string $entity = PhotoEntity::class;
    protected ?string $allowed = null;

    public function __construct()
    {
        parent::__construct();

        $this->table = $this->photo_table;
        $this->allowed = str_replace('p.', 't.', $this->photo_allowed);
    }


    /********** ADMIN **********/

    /**
     * used for new photos
     * 
     * @param array $datas [['title' => $title, 'slug' => $slug, 'path' => $path', 'description' => $description, 'created_at' => $created_at], ...]
     */
    public function create_photos(array $datas): int
    {
        $collection = [];
        $params = [];
        $i = 0;
        foreach ($datas as $data):
            $values = [];
            foreach ($data as $key => $value):
                $key = $key . $i;
                $values[] = ":$key";
                $params[$key] = $value;
            endforeach;
            $values = implode(', ', $values);
            $collection[] = "($values)";
            $i++;
        endforeach;
        $collection = implode(', ', $collection);

        $this->execute_query(
            sql_query:
            "INSERT INTO nk_$this->table (title, slug, description, created_at, path)
             VALUES $collection",
            params: $params,
            method: null,
            message: "Impossible de créer une publication dans la table $this->table."
        );

        return $this->pdo->lastInsertId();
    }

    public function attach_collection(array $this_ids, string $table, ?array $collection_ids): int
    {
        if ($collection_ids):
            $this_list = implode($this_ids);
            $collection_list = implode($collection_ids);

            $params = [];
            $values = [];
            $i = 0;
            foreach ($this_ids as $this_id):
                foreach ($collection_ids as $collection_id):
                    $this_key = "{$this->table}_id$i";
                    $collection_key = "{$table}_id$i";
                    $values[] = "(:$this_key, :$collection_key)";
                    $params[$this_key] = $this_id;
                    $params[$collection_key] = $collection_id;
                    $i++;
                endforeach;
            endforeach;
            $values = implode(', ', $values);

            $query = $this->execute_query(
                sql_query:
                "INSERT INTO nk_{$this->table}_$table ({$this->table}_id, {$table}_id)
                 VALUES $values
                 ON DUPLICATE KEY UPDATE {$this->table}_id = {$this->table}_id, {$table}_id = {$table}_id",
                params: $params,
                method: null,
                message: "Impossible d'attacher les publications $collection_list dans la table $table aux publications $this_list dans la table $this->table."
            );

            return $query->rowCount();
        endif;

        return 0;
    }

    public function detach_collection(array $this_ids, string $table, array $remove_ids = [], array $except_ids = []): int
    {
        if (empty($this_ids) || (empty($remove_ids) && empty($except_ids))):
            return 0;
        endif;

        $this_list = implode($this_ids);

        $params = [];

        $this_in = [];
        $i = 0;
        foreach ($this_ids as $id):
            $key = "id$i";
            $this_in[] = ":$key";
            $params[$key] = $id;
            $i++;
        endforeach;
        $this_in = implode(', ', $this_in);

        $remove_in = [];
        $j = 0;
        foreach ($remove_ids as $remove_id):
            $key = "remove_id$j";
            $remove_in[] = ":$key";
            $params[$key] = $remove_id;
            $j++;
        endforeach;
        $remove_in = implode(', ', $remove_in);

        $except_in = [];
        $k = 0;
        foreach ($except_ids as $except_id):
            $key = "except_id$k";
            $except_in[] = ":$key";
            $params[$key] = $except_id;
            $k++;
        endforeach;
        $except_in = implode(', ', $except_in);

        $query = $this->execute_query(
            sql_query:
            "DELETE FROM nk_{$this->table}_$table
             WHERE {$this->table}_id IN ($this_in)" .
            ($remove_in ? "AND {$table}_id IN ($remove_in)" : '') .
            ($except_in ? "AND {$table}_id NOT IN ($except_in)" : ''),
            params: $params,
            method: null,
            message: "Impossible de détacher les publications dans la table $table des publications $this_list dans la table $this->table."
        );

        return $query->rowCount();
    }

    /**
     * used for edit photo
     */
    public function find(string $field, mixed $value): PhotoEntity
    {
        $field = htmlentities($field);

        return $this->fetch_entity(
            sql_query:
            "SELECT
                 t.path, 
                 t.title, 
                 t.status, 
                 t.description, 
                 t.created_at, 
                 t.status, 
                 t.album_id, 
                 tl.locations_ids,
                 tc.categories

             FROM nk_$this->table t

             LEFT JOIN nk_$this->album_table a ON t.{$this->album_table}_id = a.id
             LEFT JOIN (
                 SELECT DISTINCT photo_id, MIN(location_id) AS location_id, JSON_ARRAYAGG(location_id) AS locations_ids
                 FROM nk_{$this->table}_$this->location_table
                 GROUP BY photo_id
             ) tl ON t.id = tl.{$this->table}_id
             LEFT JOIN nk_$this->location_table l ON tl.{$this->location_table}_id = l.id
             LEFT JOIN (
                 SELECT DISTINCT photo_id, MIN(category_id) AS category_id, JSON_ARRAYAGG(JSON_OBJECT('id', category_id)) AS categories
                 FROM nk_{$this->table}_$this->category_table
                 GROUP BY photo_id
             ) tc ON t.id = tc.{$this->table}_id
             LEFT JOIN nk_$this->category_table c ON tc.{$this->category_table}_id = c.id

             WHERE t.status != 'trashed'
             AND (a.status = 'published' OR a.status IS NULL)
             AND (l.status = 'published' OR l.status IS NULL)
             AND (c.status = 'published' OR c.status IS NULL)
             AND t.$field = :value",

            params: compact('value')
        );
    }

    /**
     * used for delete photos
     * 
     * @return PhotoEntity[]
     */
    public function find_all(array $ids): array
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

        return $this->fetch_entities(
            sql_query:
            "SELECT path
             FROM nk_$this->table
             WHERE id IN ($in)
             AND status = 'trashed'",

            params: $params
        );
    }

    /**
     * used for admin index
     */
    public function find_paginated(array $filters = [], string $status = 'published', string $order = 'created_at DESC', int $per_page = 30): array
    {
        $search = '';
        if (!empty($filters)):
            $search = (isset($filters["{$this->album_table}_id"]) ? " AND t.{$this->album_table}_id = :{$this->album_table}_id" : '')
                . (isset($filters["{$this->category_table}_id"]) ? " AND JSON_CONTAINS(c.ids, CAST(:{$this->category_table}_id AS CHAR))" : '')
                . (isset($filters["{$this->location_table}_id"]) ? " AND JSON_CONTAINS(l.ids, CAST(:{$this->location_table}_id AS CHAR))" : '')
                . (isset($filters['year']) ? " AND YEAR(t.created_at) = :year" : '')
                . (isset($filters['month']) ? " AND MONTH(t.created_at) = :month" : '');
        endif;

        $many_to_many_collections = [$this->category_table, $this->location_table];
        foreach ($many_to_many_collections as $table):
            $join_many_to_many[] = "LEFT JOIN (
                                        SELECT DISTINCT
                                            {$this->table}_id,
                                            JSON_ARRAYAGG(id) AS ids, -- FOR FILTER
                                            JSON_ARRAYAGG(JSON_OBJECT('id', id, 'title', title)) AS details -- TO DISPLAY
                                        FROM nk_{$this->table}_$table t{$table[0]}
                                        LEFT JOIN nk_$table {$table[0]} ON t{$table[0]}.{$table}_id = {$table[0]}.id
                                        WHERE {$table[0]}.status = 'published'
                                        GROUP BY t{$table[0]}.{$this->table}_id
                                    ) {$table[0]} ON {$table[0]}.{$this->table}_id = t.id";
            $join_many_to_many_filter[] = "LEFT JOIN (
                                               SELECT DISTINCT
                                                   {$this->table}_id,
                                                   JSON_ARRAYAGG(id) AS ids
                                               FROM nk_{$this->table}_$table t{$table[0]}
                                               LEFT JOIN nk_$table {$table[0]} ON t{$table[0]}.{$table}_id = {$table[0]}.id
                                               WHERE {$table[0]}.status = 'published'
                                               GROUP BY t{$table[0]}.{$this->table}_id
                                           ) {$table[0]} ON {$table[0]}.{$this->table}_id = t.id";
        endforeach;
        $join_many_to_many = implode("\n", $join_many_to_many);
        $join_many_to_many_filter = implode("\n", $join_many_to_many_filter);

        return $this->fetch_paginated_entities(
            query:
            "SELECT
                 t.id, 
                 t.path, 
                 t.title,
                 t.description,
                 t.created_at,
                 t.private_ids,
                 IF(
                     a.id IS NULL,
                     NULL,
                     JSON_OBJECT('title', a.title, 'slug', a.slug)
                 ) AS album,
                 l.details AS locations,
                 c.details AS categories

             FROM nk_$this->table t

             LEFT JOIN nk_$this->album_table a ON t.{$this->album_table}_id = a.id

             $join_many_to_many

             WHERE t.status = :status
             AND (a.status = 'published' OR a.status IS NULL)
             $search",

            query_count:
            "SELECT COUNT(t.id)

             FROM nk_$this->table t

             $join_many_to_many_filter

             WHERE t.status = :status
             $search",

            params: array_merge(compact('status'), $filters),
            order: htmlentities($order),
            per_page: $per_page

        );
    }

    /**
     * use to update private_ids
     * 
     * @return [$photos_ids, $albums_ids, $locations_ids]
     */
    public function list_by_categories(array $category_ids): array
    {
        $in = [];
        $params = [];
        $i = 0;
        foreach ($category_ids as $id):
            $key = "id$i";
            $in[] = ":$key";
            $params[$key] = $id;
            $i++;
        endforeach;
        $in = implode(', ', $in);

        return $this->fetch_column(
            sql_query:
            "SELECT {$this->table}_id
             FROM nk_{$this->table}_{$this->category_table}
             WHERE {$this->category_table}_id IN ($in)",

            params: $params
        );
    }


    /********** PUBLIC **********/

    /**
     * used for public indexes of photos
     * 
     * @return array[Pagination, PhotoEntity[]]
     */
    public function find_paginated_allowed(array $filters = [], string $order = 'created_at DESC', int $per_page = 20): array
    {
        $search = [];
        if (!empty($filters)):
            if (isset($filters["{$this->album_table}_id"])):
                $search[] = "t.{$this->album_table}_id = :{$this->album_table}_id";
            endif;
            if (isset($filters["{$this->category_table}_id"])):
                $search[] = "JSON_CONTAINS(c.ids, CAST(:{$this->category_table}_id AS CHAR))";
            endif;
            if (isset($filters["{$this->location_table}_id"])):
                $search[] = "JSON_CONTAINS(l.ids, CAST(:{$this->location_table}_id AS CHAR))";
            endif;
            if (isset($filters["year"])):
                $search[] = "YEAR(t.created_at) = :year";
            endif;
            if (isset($filters["month"])):
                $search[] = "MONTH(t.created_at) = :month";
            endif;
        endif;
        $search = !empty($search) ? 'AND ' . implode(' AND ', $search) : '';

        $many_to_many_collections = [$this->category_table, $this->location_table];
        foreach ($many_to_many_collections as $table):
            $join_many_to_many[] = "LEFT JOIN (
                                        SELECT DISTINCT
                                            {$this->table}_id,
                                            JSON_ARRAYAGG(id) AS ids, -- FOR FILTERS
                                            JSON_ARRAYAGG(JSON_OBJECT('title', title, 'slug', slug)) AS details -- TO DISPLAY
                                        FROM nk_{$this->table}_$table t{$table[0]}
                                        LEFT JOIN nk_$table {$table[0]} ON t{$table[0]}.{$table}_id = {$table[0]}.id
                                        GROUP BY {$this->table}_id
                                    ) {$table[0]} ON {$table[0]}.{$this->table}_id = t.id";
            $join_many_to_many_filter[] = "LEFT JOIN (
                                               SELECT DISTINCT
                                                   {$this->table}_id,
                                                   JSON_ARRAYAGG(id) AS ids
                                               FROM nk_{$this->table}_$table t{$table[0]}
                                               LEFT JOIN nk_$table {$table[0]} ON t{$table[0]}.{$table}_id = {$table[0]}.id
                                               GROUP BY {$this->table}_id
                                           ) {$table[0]} ON {$table[0]}.{$this->table}_id = t.id";
        endforeach;
        $join_many_to_many = implode("\n", $join_many_to_many);
        $join_many_to_many_filter = implode("\n", $join_many_to_many_filter);

        return $this->fetch_paginated_entities(
            query:
            "SELECT
                 t.id,
                 path,
                 t.title, 
                 t.slug, 
                 description, 
                 created_at,
                 IF(
                     a.id IS NULL,
                     NULL,
                     JSON_OBJECT('title', a.title, 'slug', a.slug)
                 ) AS album,
                 c.details AS categories,
                 l.details AS locations

             FROM nk_$this->table t

             LEFT JOIN nk_$this->album_table a ON t.{$this->album_table}_id = a.id
             
             $join_many_to_many

             WHERE $this->allowed
             $search",

            query_count:
            "SELECT COUNT(t.id)

             FROM nk_$this->table t

             $join_many_to_many_filter

             WHERE $this->allowed
             $search
             
             LIMIT 100",

            params: $filters,
            order: htmlentities($order),
            per_page: $per_page
        );
    }
}