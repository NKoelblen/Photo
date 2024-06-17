<?php
namespace App\Repository;

use App\Entity\PhotoEntity;
use App\Repository\Exception\NotFoundException;
use Exception;
use PDO;

final class PhotoRepository extends PostRepository
{
    protected ?string $table = 'photo';
    protected ?string $entity = PhotoEntity::class;

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
            $i++;
            foreach ($data as $key => $value):
                $key = $key . $i;
                $values[] = ":$key";
                $params[$key] = $value;
            endforeach;
            $values = implode(', ', $values);
            $collection[] = "($values)";
        endforeach;
        $collection = implode(', ', $collection);
        $query = $this->pdo->prepare("INSERT INTO nk_$this->table (title, slug, description, created_at, path) VALUES $collection");
        $create = $query->execute($params);
        if ($create === false):
            throw new Exception("Impossible de créer une publication dans la table $this->table.");
        endif;
        return $this->pdo->lastInsertId();
    }

    public function insert_items(array $this_ids, string $table, array $collection_ids): int
    {
        if ($collection_ids):
            $this_list = implode($this_ids);
            $collection_list = implode($collection_ids);

            $params = [];
            $values = [];
            $i = 0;
            foreach ($this_ids as $this_id):
                foreach ($collection_ids as $collection_id):
                    $i++;
                    $this_key = "{$this->table}_id$i";
                    $collection_key = "{$table}_id$i";
                    $values[] = "(:$this_key, :$collection_key)";
                    $params[$this_key] = $this_id;
                    $params[$collection_key] = $collection_id;
                endforeach;
            endforeach;
            $values = implode(', ', $values);
            $query = $this->pdo->prepare(
                "INSERT INTO nk_{$this->table}_$table ({$this->table}_id, {$table}_id)
                         VALUES $values
                         ON DUPLICATE KEY UPDATE {$this->table}_id = {$this->table}_id, {$table}_id = {$table}_id"
            );
            $create = $query->execute($params);
            if ($create === false):
                throw new Exception("Impossible d'attacher les publications $collection_list dans la table $table aux publications $this_list dans la table $this->table.");
            endif;
            return $query->rowCount();
        endif;
        return 0;
    }

    /**
     * @param ?array $except_ids remove all items except specified items ids
     */
    public function remove_items(array $this_ids, string $table, array $remove_ids): int
    {
        if (empty($this_ids) || empty($remove_ids)):
            return 0;
        endif;

        $this_list = implode($this_ids);

        $params = [];

        $this_in = [];
        foreach ($this_ids as $id):
            $key = ":id" . $id;
            $this_in[] = $key;
            $params[$key] = $id;
        endforeach;
        $this_in = implode(', ', $this_in);

        $remove_in = [];
        foreach ($remove_ids as $remove_id):
            $key = ":id" . $remove_id;
            $remove_in[] = $key;
            $params[$key] = $remove_id;
        endforeach;
        $remove_in = implode(', ', $remove_in);

        $query = $this->pdo->prepare("DELETE FROM nk_{$this->table}_$table WHERE {$this->table}_id IN ($this_in) AND {$table}_id IN ($remove_in)");
        $delete = $query->execute($params);
        if ($delete === false):
            throw new Exception("Impossible de détacher les publications dans la table $table des publications $this_list dans la table $this->table.");
        endif;
        return $query->rowCount();
    }

    /**
     * used for edit photo
     */
    public function find_photo(string $field, mixed $value): PhotoEntity
    {
        $field = htmlentities($field);
        $query = $this->pdo->prepare(
            "SELECT
                 $this->table.path, 
                 MIN($this->table.title) AS title, 
                 MIN($this->table.status) AS status, 
                 MIN($this->table.description) AS description, 
                 MIN($this->table.created_at) AS created_at, 
                 MIN($this->table.status) AS status, 
                 MIN($this->table.album_id) AS album_id, 
                 JSON_ARRAYAGG({$this->table}_location.location_id) AS locations_ids,
                 JSON_ARRAYAGG(JSON_OBJECT('id', {$this->table}_category.category_id)) AS categories
             FROM nk_$this->table $this->table
             LEFT JOIN nk_album album ON $this->table.album_id = album.id
             LEFT JOIN nk_{$this->table}_location {$this->table}_location ON $this->table.id = {$this->table}_location.{$this->table}_id
             LEFT JOIN nk_location location ON {$this->table}_location.location_id = location.id
             LEFT JOIN nk_{$this->table}_category {$this->table}_category ON $this->table.id = {$this->table}_category.{$this->table}_id
             LEFT JOIN nk_category category ON {$this->table}_category.category_id = category.id
             WHERE $this->table.status != 'trashed'
             AND (album.status = 'published' OR album.status IS NULL)
             AND (location.status = 'published' OR location.status IS NULL)
             AND (category.status = 'published' OR category.status IS NULL)
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
     * used for delete photos
     * 
     * @return PhotoEntity[]
     */
    public function find_photos(array $ids): array
    {
        $in = [];
        $params = [];
        foreach ($ids as $id):
            $key = ":id" . $id;
            $in[] = $key;
            $params[$key] = $id;
        endforeach;
        $in = implode(', ', $in);
        $query = $this->pdo->prepare(
            "SELECT path
             FROM nk_$this->table
             WHERE id IN ($in)
             AND status = 'trashed'"
        );
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
    }

    /**
     * used for admin photos index
     */
    public function find_paginated_photos(array $filters = [], string $status = 'published', string $order = 'created_at DESC', int $per_page = 30): array
    {
        $search = '';
        if (!empty($filters)):
            $search = (isset($filters['album_id']) ? " AND $this->table.album_id = :album_id" : '')
                . (isset($filters['category_id']) ? " AND JSON_CONTAINS(category.category_ids, CAST(:category_id AS CHAR))" : '')
                . (isset($filters['location_id']) ? " AND JSON_CONTAINS(location.location_ids, CAST(:location_id AS CHAR))" : '')
                . (isset($filters['year']) ? " AND YEAR($this->table.created_at) = :year" : '')
                . (isset($filters['month']) ? " AND MONTH($this->table.created_at) = :month" : '');
        endif;

        $order = htmlentities($order);
        $pagination = new Pagination(
            "SELECT
                 $this->table.id, 
                 $this->table.path, 
                 $this->table.title,
                 $this->table.description,
                 $this->table.created_at,
                 $this->table.private_ids,
                 album.detail AS album,
                 location.details AS locations,
                 category.details AS categories
             FROM nk_$this->table $this->table
             LEFT JOIN (
                 SELECT
                     id,
                     JSON_OBJECT(
                             'id', id,
                             'title', title
                         ) As detail
                 FROM nk_album
                 WHERE status = 'published'
             ) album ON $this->table.album_id = album.id
             LEFT JOIN (
                 SELECT
                     {$this->table}_location.{$this->table}_id,
                     JSON_ARRAYAGG(location.id) AS location_ids,
                     JSON_ARRAYAGG(
                         JSON_OBJECT(
                             'id', location.id,
                             'title', location.title
                         )
                     ) AS details
                 FROM nk_{$this->table}_location {$this->table}_location
                 LEFT JOIN nk_location location ON {$this->table}_location.location_id = location.id
                 WHERE location.status = 'published'
                 GROUP BY {$this->table}_location.{$this->table}_id
             ) location ON location.{$this->table}_id = $this->table.id
             LEFT JOIN (
                 SELECT
                     {$this->table}_category.{$this->table}_id,
                     JSON_ARRAYAGG(category.id) AS category_ids,
                     JSON_ARRAYAGG(
                         JSON_OBJECT(
                             'id', category.id,
                             'title', category.title
                         )
                     ) AS details
                 FROM nk_{$this->table}_category {$this->table}_category
                 LEFT JOIN nk_category category ON {$this->table}_category.category_id = category.id
                 WHERE category.status = 'published'
                 GROUP BY {$this->table}_category.{$this->table}_id
             ) category ON category.{$this->table}_id = $this->table.id
             WHERE $this->table.status = :status
             $search",
            "SELECT COUNT($this->table.id)
             FROM nk_$this->table $this->table
             LEFT JOIN (
                 SELECT
                     {$this->table}_location.{$this->table}_id,
                     JSON_ARRAYAGG(location.id) AS location_ids
                 FROM nk_{$this->table}_location {$this->table}_location
                 LEFT JOIN nk_location location ON {$this->table}_location.location_id = location.id
                 WHERE location.status = 'published'
                 GROUP BY {$this->table}_location.{$this->table}_id
             ) location ON location.{$this->table}_id = $this->table.id
             LEFT JOIN (
                 SELECT
                     {$this->table}_category.{$this->table}_id,
                     JSON_ARRAYAGG(category.id) AS category_ids
                 FROM nk_{$this->table}_category {$this->table}_category
                 LEFT JOIN nk_category category ON {$this->table}_category.category_id = category.id
                 WHERE category.status = 'published'
                 GROUP BY {$this->table}_category.{$this->table}_id
             ) category ON category.{$this->table}_id = $this->table.id
             WHERE $this->table.status = :status
             $search",
            array_merge(compact('status'), $filters),
            $order,
            $per_page
        );
        $entities = $pagination->get_entities($this->entity, $this->table);
        return [$pagination, $entities];
    }

    /**
     * used for public indexes of photos
     * 
     * @return array[Pagination, PhotoEntity[]]
     */
    public function find_paginated_allowed_photos(array $filters = [], string $order = 'created_at DESC', int $per_page = 20): array
    {
        $search = '';
        if (!empty($filters)):
            $search = (isset($filters['album_id']) ? " AND $this->table.album_id = :album_id" : '')
                . (isset($filters['category_id']) ? " AND JSON_CONTAINS(category.category_ids, CAST(:category_id AS CHAR))" : '')
                . (isset($filters['location_id']) ? " AND JSON_CONTAINS(location.location_ids, CAST(:location_id AS CHAR))" : '')
                . (isset($filters['year']) ? " AND YEAR($this->table.created_at) = :year" : '')
                . (isset($filters['month']) ? " AND MONTH($this->table.created_at) = :month" : '');
        endif;

        $order = htmlentities($order);
        $pagination = new Pagination(
            "SELECT
                 $this->table.id,
                 $this->table.path,
                 $this->table.title, 
                 $this->table.slug, 
                 $this->table.description, 
                 $this->table.created_at,
                 JSON_OBJECT('title', album.title, 'slug', album.slug) AS album,
                 category.details AS categories,
                 location.details AS locations
             FROM nk_$this->table $this->table
             LEFT JOIN nk_album album ON $this->table.album_id = album.id
             LEFT JOIN (
                 SELECT DISTINCT
                     {$this->table}_category.{$this->table}_id,
                     JSON_ARRAYAGG(category.id) AS category_ids,
                     JSON_ARRAYAGG(JSON_OBJECT('title', category.title, 'slug', category.slug)) AS details
                 FROM nk_{$this->table}_category {$this->table}_category
                 LEFT JOIN nk_category category ON {$this->table}_category.category_id = category.id
                 WHERE category.status = 'published'
                 AND category.private = 0
                 GROUP BY {$this->table}_id
             ) category ON category.{$this->table}_id = $this->table.id
             LEFT JOIN (
                 SELECT DISTINCT
                     {$this->table}_location.{$this->table}_id,
                     JSON_ARRAYAGG(location.id) AS location_ids,
                     JSON_ARRAYAGG(JSON_OBJECT('title', location.title, 'slug', location.slug)) AS details
                 FROM nk_{$this->table}_location {$this->table}_location
                 LEFT JOIN nk_location location ON {$this->table}_location.location_id = location.id
                 WHERE location.status = 'published'
                 AND location.private = 0
                 GROUP BY {$this->table}_id
             ) location ON location.{$this->table}_id = $this->table.id
             WHERE $this->table.status = 'published'
             AND $this->table.private_ids IS NULL
             AND (album.status = 'published' OR album.status IS NULL)
             AND album.private = 0
             $search",
            "SELECT COUNT($this->table.id)
             FROM nk_$this->table $this->table
             LEFT JOIN (
                 SELECT DISTINCT
                     {$this->table}_category.{$this->table}_id,
                     JSON_ARRAYAGG(category.id) AS category_ids
                 FROM nk_{$this->table}_category {$this->table}_category
                 LEFT JOIN nk_category category ON {$this->table}_category.category_id = category.id
                 WHERE category.status = 'published'
                 AND category.private = 0
                 GROUP BY {$this->table}_id
             ) category ON category.{$this->table}_id = $this->table.id
             LEFT JOIN (
                 SELECT DISTINCT
                     {$this->table}_location.{$this->table}_id,
                     JSON_ARRAYAGG(location.id) AS location_ids
                 FROM nk_{$this->table}_location {$this->table}_location
                 LEFT JOIN nk_location location ON {$this->table}_location.location_id = location.id
                 WHERE location.status = 'published'
                 AND location.private_ids IS NULL
                 GROUP BY {$this->table}_id
             ) location ON location.{$this->table}_id = $this->table.id
             WHERE $this->table.status = 'published'
             AND $this->table.private_ids IS NULL
             $search",
            $filters,
            $order,
            $per_page
        );
        $entities = $pagination->get_entities($this->entity, $this->table);
        return [$pagination, $entities];
    }

    /**
     * use to update private_ids
     * 
     * @return [$photos_ids, $albums_ids, $locations_ids]
     */
    public function find_categories_photos(array $category_ids): array
    {
        $in = [];
        $params = [];
        foreach ($category_ids as $id):
            $key = ":id" . $id;
            $in[] = $key;
            $params[$key] = $id;
        endforeach;
        $in = implode(', ', $in);
        $query = $this->pdo->prepare(
            "SELECT photo_id
             FROM nk_photo_category
             WHERE category_id IN ($in)"
        );
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }
}