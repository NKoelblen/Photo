<?php
namespace App\Repository;

use App\Entity\CollectionEntity;

abstract class CollectionRepository extends PostRepository
{
    /********** ADMIN **********/

    /**
     * used for admin indexes
     * 
     * @return array[Pagination, CollectionEntity[]]
     */
    public function find_paginated(string $status, array $columns = ['id', 'title', 'slug'], string $order = 'title ASC', int $per_page = 20): array
    {
        $columns = implode(', ', $columns);
        $clauses = "FROM nk_$this->table WHERE status = :status";

        return $this->fetch_paginated_entities(
            query: "SELECT $columns $clauses",
            query_count: "SELECT COUNT(id) $clauses",
            params: compact('status'),
            order: htmlentities($order),
            per_page: $per_page
        );
    }
}