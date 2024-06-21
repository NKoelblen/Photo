<?php
namespace App\Repository;

use App\Entity\YearEntity;

class YearRepository extends CollectionRepository
{
    protected ?string $table = '';
    protected ?string $entity = YearEntity::class;

    public function __construct()
    {
        parent::__construct();
        $this->table = $this->photo_table;
    }


    /********** PUBLIC **********/

    /**
     * @return YearEntity[]
     */
    public function find_years(): array
    {
        return $this->fetch_entities(
            sql_query:
            "SELECT DISTINCT
                 YEAR(created_at) AS title,
                 FIRST_VALUE(JSON_OBJECT('path', path, 'description', description)) OVER (PARTITION BY YEAR(created_at) ORDER BY RAND()) AS thumbnail
             FROM nk_$this->table p
             WHERE $this->photo_allowed
             ORDER BY YEAR(created_at) DESC
             LIMIT 8",
        );
    }
}