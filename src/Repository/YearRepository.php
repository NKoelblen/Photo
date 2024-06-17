<?php
namespace App\Repository;

use App\Entity\YearEntity;
use PDO;

class YearRepository extends PostRepository
{
    protected ?string $table = 'photo';
    protected ?string $entity = YearEntity::class;

    public function find_years()
    {
        return $this->pdo->query(
            "SELECT DISTINCT
                 YEAR(created_at) AS title,
                 FIRST_VALUE(JSON_OBJECT('path', path, 'description', description)) OVER (PARTITION BY YEAR(created_at) ORDER BY RAND()) AS thumbnail
             FROM nk_photo
             WHERE status = 'published'
             AND private_ids IS NULL
             ORDER BY YEAR(created_at) DESC
             LIMIT 8"
        )->fetchAll(PDO::FETCH_CLASS, $this->entity);
    }
}