<?php
namespace App\Repository;

use App\Entity\CategoryEntity;
use PDO;

final class CategoryRepository extends RecursiveRepository
{
    protected ?string $table = 'category';
    protected ?string $entity = CategoryEntity::class;

    public function create()
    {

    }
    public function update()
    {

    }
    public function delete()
    {

    }
    public function find()
    {

    }
    public function find_all()
    {

    }
}
