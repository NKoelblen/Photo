<?php
namespace App\Repository;

use App\Entity\AlbumEntity;
use App\Repository\Pagination;

final class AlbumRepository extends CollectionRepository
{
    protected ?string $table = '';
    protected ?string $entity = AlbumEntity::class;

    public function __construct()
    {
        parent::__construct();
        $this->table = $this->album_table;
    }

    /********** ADMIN ***********/

    /**
     * used for edit album
     */
    public function find(string $field, mixed $value): AlbumEntity
    {
        return $this->fetch_entity(
            "SELECT title, status
             FROM nk_$this->table
             WHERE status != 'trashed'
             AND $field = :value",
            compact('value'),
        );
    }

    /**
     * used for admin indexes
     * 
     * @return array[Pagination, AlbumEntity[]]
     */
    public function find_paginated(string $status, array $columns = ['id', 'title', 'slug'], string $order = 'title ASC', int $per_page = 20): array
    {
        foreach ($columns as $key => $value):
            $columns[$key] = "t.$value";
        endforeach;
        $columns = implode(', ', $columns);

        return $this->fetch_paginated_entities(
            query:
            "SELECT 
                 $columns, 
                 JSON_OBJECTAGG(p.visibility, p.photos_nb) AS photos_nb
             FROM nk_$this->table t
             LEFT JOIN (
                 SELECT 
                    CASE 
                         WHEN private_ids IS NULL OR private_ids = '[]' 
                         THEN 'public' 
                         ELSE 'private' 
                    END AS visibility, 
                    COUNT(p.id) AS photos_nb,
                    JSON_ARRAYAGG(p.{$this->table}_id) AS t_ids
                 FROM nk_$this->table t
                 LEFT JOIN nk_photo p ON t.id = p.{$this->table}_id
                 GROUP BY
                     t.id,
                     CASE 
                         WHEN private_ids IS NULL OR private_ids = '[]' 
                         THEN 'public' 
                         ELSE 'private' 
                     END
             ) p ON t.id MEMBER OF (p.t_ids) 
             WHERE t.status = :status
             GROUP BY t.id",
            query_count:
            "SELECT COUNT(id)
             FROM nk_$this->table
             WHERE status = :status",
            params: compact('status'),
            order: htmlentities($order),
            per_page: $per_page
        );
    }

    /**
     * used for edit photo
     */
    public function edit_photo_list(): array
    {
        $entities = $this->fetch_entities(
            "SELECT id, title
             FROM nk_$this->table
             WHERE status = 'published'
             ORDER BY title",
        );

        $list = [];
        foreach ($entities as $entity):
            $list[$entity->get_id()] = $entity->get_title();
        endforeach;
        return $list;
    }


    /********** PUBLIC ***********/

    /**
     * used for home index of albums
     */
    public function find_home_allowed(string $order = 'date_from DESC', ?int $per_page = null): array
    {
        $order = htmlentities($order);
        $limit = $per_page === null ? '' : "LIMIT $per_page";
        return $this->fetch_entities(
            sql_query: "{$this->allowed_query()} ORDER BY $order $limit",
        );
    }

    /**
     * used for public index of albums
     * 
     * @return array[Pagination, AlbumEntity[]]
     */
    public function find_paginated_allowed(string $order = 'date_from DESC', int $per_page = 20): array
    {
        return $this->fetch_paginated_entities(
            query: $this->allowed_query(),

            query_count:
            "SELECT COUNT(DISTINCT t.id)
             FROM nk_$this->table t
             JOIN nk_$this->photo_table p
             WHERE $this->photo_allowed",

            order: htmlentities($order),
            per_page: $per_page
        );
    }

    /**
     * used for show album
     */
    public function find_allowed(string $field, mixed $value): AlbumEntity
    {
        return $this->fetch_entity(
            sql_query:
            "SELECT t.id, t.title, t.slug
             FROM nk_$this->table t
             JOIN nk_$this->photo_table p ON t.id = p.{$this->table}_id
             WHERE t.status = 'published'
             AND t.$field = :value
             AND $this->photo_allowed",

            params: compact('value')
        );
    }

    private function allowed_query(): string
    {
        return
            "SELECT DISTINCT t.id, t.title, t.slug, p.thumbnail, p.date_from, p.date_to
             FROM nk_$this->table t
             JOIN 
             (
                 SELECT
                     {$this->table}_id, 
                     MIN(created_at) OVER (PARTITION BY {$this->table}_id) AS date_from,
                     MAX(created_at) OVER (PARTITION BY {$this->table}_id) AS date_to,
                     FIRST_VALUE(
                         JSON_OBJECT(
                             'path', path, 
                             'description', description
                         )
                     ) OVER (PARTITION BY {$this->table}_id ORDER BY RAND()) AS thumbnail
                 FROM nk_$this->photo_table p
                 WHERE $this->photo_allowed
             ) p ON t.id = p.{$this->table}_id";
    }
}