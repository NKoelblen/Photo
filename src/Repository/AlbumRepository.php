<?php
namespace App\Repository;

use App\Entity\AlbumEntity;
use App\Repository\Exception\NotFoundException;
use Exception;
use PDO;

final class AlbumRepository extends PostRepository
{
    protected ?string $table = 'album';
    protected ?string $entity = AlbumEntity::class;

    /**
     * used for edit album
     */
    public function find_album(string $field, mixed $value): AlbumEntity
    {
        $field = htmlentities($field);
        $query = $this->pdo->prepare(
            "SELECT title, status
             FROM nk_$this->table
             WHERE status != 'trashed'
             AND $field = :value"
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
     * used for admin albums indexes
     * 
     * @return array[Pagination, AlbumEntity[]]
     */
    public function find_paginated_albums(string $status = 'published', string $order = 'title ASC', int $per_page = 20): array
    {
        $order = htmlentities($order);
        $pagination = new Pagination(
            "SELECT id, title, slug, private
             FROM nk_$this->table
             WHERE status = :status",
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
     * used for show album
     */
    public function find_allowed_album(string $field, mixed $value): AlbumEntity
    {
        $field = htmlentities($field);
        $query = $this->pdo->prepare(
            "SELECT id, title, slug
             FROM nk_$this->table
             WHERE status = 'published'
             AND private = 0
             AND $field = :value"
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
     * used for home index of albums
     */
    public function find_allowed_albums(string $order = 'date_from DESC', ?int $per_page = null): array
    {
        $order = htmlentities($order);
        $limit = $per_page === null ? '' : "LIMIT $per_page";
        $query = $this->pdo->prepare(
            "SELECT DISTINCT $this->table.id, $this->table.title, $this->table.slug, photo.thumbnail, photo.date_from, photo.date_to
             FROM nk_$this->table $this->table
             JOIN 
             (
                 SELECT
                     photo.{$this->table}_id, 
                     MIN(photo.created_at) OVER (PARTITION BY photo.{$this->table}_id) AS date_from,
                     MAX(photo.created_at) OVER (PARTITION BY photo.{$this->table}_id) AS date_to,
                     FIRST_VALUE(
                         JSON_OBJECT(
                             'path', photo.path, 
                             'description', photo.description
                         )
                     ) OVER (PARTITION BY photo.{$this->table}_id ORDER BY RAND()) AS thumbnail
                 FROM nk_photo photo
                 WHERE photo.status = 'published'
                 AND photo.private_ids IS NULL
             ) photo ON $this->table.id = photo.{$this->table}_id
             WHERE $this->table.status = 'published'
             AND $this->table.private = 0
             ORDER BY $order
             $limit"
        );
        $query->execute();
        $entities = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
        return $entities;
    }

    /**
     * used for public index of albums
     * 
     * @return array[Pagination, AlbumEntity[]]
     */
    public function find_paginated_allowed_albums(string $order = 'date_from DESC', int $per_page = 20): array
    {
        $order = htmlentities($order);
        $pagination = new Pagination(
            "SELECT DISTINCT $this->table.id, $this->table.title, $this->table.slug, photo.thumbnail, photo.date_from, photo.date_to
             FROM nk_$this->table $this->table
             JOIN 
             (
                 SELECT DISTINCT
                     photo.{$this->table}_id, 
                     MIN(photo.created_at) OVER (PARTITION BY photo.{$this->table}_id) AS date_from,
                     MAX(photo.created_at) OVER (PARTITION BY photo.{$this->table}_id) AS date_to,
                     FIRST_VALUE(
                         JSON_OBJECT(
                             'path', photo.path, 
                             'description', photo.description
                         )
                     ) OVER (PARTITION BY photo.{$this->table}_id ORDER BY RAND()) AS thumbnail
                 FROM nk_photo photo
                 WHERE photo.status = 'published'
                 AND photo.private_ids IS NULL
             ) photo ON $this->table.id = photo.{$this->table}_id
             WHERE $this->table.status = 'published'
             AND $this->table.private = 0",
            "SELECT COUNT(id)
             FROM nk_$this->table
             WHERE status = 'published'
             AND private = 0",
            [],
            $order,
            $per_page
        );
        $entities = $pagination->get_entities($this->entity, $this->table);
        return [$pagination, $entities];
    }

    /**
     * used for edit photo
     */
    public function list_albums(): array
    {
        $query = $this->pdo->prepare(
            "SELECT id, title
             FROM nk_$this->table
             WHERE status = 'published'
             ORDER BY title"
        );
        $query->execute();
        $entities = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
        $list = [];
        foreach ($entities as $entity):
            $list[$entity->get_id()] = $entity->get_title();
        endforeach;
        return $list;
    }

    /**
     * use to update private_ids & private
     */
    public function find_categories_album_ids(array $photo_ids, array $categories_ids, int $contains): array
    {
        if (empty($photo_ids) || empty($categories_ids)):
            return [];
        endif;
        $params = compact('contains');

        $i = 0;
        $in = [];
        foreach ($photo_ids as $photo_id):
            $key = "photo_id$i";
            $in[] = ":$key";
            $params[$key] = $photo_id;
            $i++;
        endforeach;
        $in = implode(', ', $in);

        foreach ($categories_ids as $category_id):
            $categories_list[] = (string) $category_id;
        endforeach;
        $categories_list = json_encode($categories_list);

        $query = $this->pdo->prepare(
            "SELECT DISTINCT album.id
             FROM nk_photo photo
             JOIN nk_album album ON photo.album_id = album.id
             WHERE photo.id IN ($in)
             AND JSON_CONTAINS(JSON_PRETTY(COALESCE(album.private_ids, '[]')), '$categories_list') = :contains
             GROUP BY album.id
             HAVING SUM(JSON_CONTAINS(JSON_PRETTY(COALESCE(photo.private_ids, '[]')), '$categories_list')) < 2"
        );
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * use to update private
     */
    public function find_categories_album_visibility(array $photo_ids): array
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
            "SELECT DISTINCT album.id, album.private, photo_nb.photos, photo_nb.private_photos
             FROM nk_photo photo
             JOIN nk_album album ON photo.album_id = album.id
             JOIN (
                 SELECT album_id, COUNT(id) AS photos, COUNT(CASE WHEN private_ids IS NOT NULL THEN id END) AS private_photos
                 FROM nk_photo
                 GROUP BY album_id
             ) photo_nb ON album.id = photo_nb.album_id
             WHERE photo.id IN ($in)"
        );
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}