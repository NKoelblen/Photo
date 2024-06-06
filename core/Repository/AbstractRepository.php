<?php

namespace App\Repository;

use App\DataBase\DBConnection;
use App\Entity\AbstractEntity;
use Exception;
use PDO;

class AbstractRepository
{
    protected PDO $pdo;
    protected ?string $table = null;
    protected ?string $entity = null;

    public function __construct()
    {
        if ($this->table === null):
            throw new Exception("La class " . get_class($this) . " n'a pas de propriété \$table.");
        endif;
        if ($this->entity === null):
            throw new Exception("La class " . get_class($this) . " n'a pas de propriété \$entity.");
        endif;
        $this->pdo = DBConnection::get_pdo();
    }

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

    public function hydrate(AbstractEntity $entity, array $datas, array $keys): void
    {
        foreach ($keys as $key):
            $method = 'set_' . $key;
            $entity->$method($datas[$key]);
        endforeach;
    }
    /**
     * Check if value exist in table.
     * @param string $field Table column
     * @param mixed $value Field value to check
     */
    public function exist(string $field, mixed $value, ?int $exception = null): bool
    {
        $sql = "SELECT COUNT(id) FROM nk_$this->table WHERE $field = :value";
        $params = ['value' => $value];
        if ($exception !== null):
            $sql .= ' AND id != :exception';
            $params['exception'] = $exception;
        endif;
        $query = $this->pdo->prepare($sql);
        $query->execute($params);
        return $query->fetch(PDO::FETCH_NUM)[0] > 0;
    }
}