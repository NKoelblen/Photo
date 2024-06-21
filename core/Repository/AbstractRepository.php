<?php

namespace App\Repository;

use App\DataBase\DBConnection;
use App\Entity\AbstractEntity;
use Exception;
use PDO;
use PDOStatement;

class AbstractRepository
{
    protected PDO $pdo;
    protected ?string $table = null;
    protected ?string $entity = null;

    public function __construct()
    {
        $this->pdo = DBConnection::get_pdo();
    }

    public function create(array $datas): int
    {
        $fields = [];
        foreach ($datas as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $set = implode(', ', $fields);

        $this->execute_query(
            sql_query: "INSERT INTO nk_$this->table SET $set",
            params: $datas,
            method: null,
            message: "Impossible de créer un enregistrement dans la table $this->table."
        );

        return $this->pdo->lastInsertId();
    }

    public function update(array $ids, array $datas, string $message = null): void
    {
        $fields = [];
        foreach ($datas as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $set = implode(', ', $fields);
        $in = [];
        $i = 0;
        foreach ($ids as $id):
            $key = "id$i";
            $in[] = ":$key";
            $datas[$key] = $id;
            $i++;
        endforeach;
        $in = implode(', ', $in);
        $ids_list = implode(', ', $ids);

        $this->execute_query(
            sql_query: "UPDATE nk_$this->table SET $set WHERE id IN ($in)",
            params: $datas,
            method: null,
            message: "Impossible de modifier $ids_list dans la table $this->table."
        );
    }

    public function delete(array $ids): void
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

        $list_label = "la publication {$ids[0]}";
        if (count($ids) > 1):
            $list = implode(', ', $ids);
            $list_label = "les publications $list";
        endif;

        $this->execute_query(
            sql_query: "DELETE FROM nk_$this->table WHERE id IN ($in)",
            params: $params,
            method: null,
            message: "Impossible de supprimer $list_label de la table $this->table."
        );
    }

    /**
     * @return AbstractEntity
     */
    public function fetch_entity(string $sql_query, array $params = [], string $message = "Aucun enregistrement n'a été trouvé"): object
    {
        return $this->execute_query($sql_query, $params, $message, 'fetch');
    }

    /**
     * @return AbstractEntity[]
     */
    public function fetch_entities(string $sql_query, array $params = [], string $message = "Aucun enregistrement n'a été trouvé"): array
    {
        return $this->execute_query($sql_query, $params, $message);
    }

    public function fetch_array(string $sql_query, array $params = [], string $message = "Aucun enregistrement n'a été trouvé"): array
    {
        return $this->execute_query($sql_query, $params, $message, 'fetchAll', [PDO::FETCH_ASSOC]);
    }

    public function fetch_column(string $sql_query, array $params = [], string $message = "Aucun enregistrement n'a été trouvé"): array
    {
        return $this->execute_query($sql_query, $params, $message, 'fetchAll', [PDO::FETCH_COLUMN, 0]);
    }

    /**
     * @return array[Pagination, AbstractEntity[]]
     */
    public function fetch_paginated_entities(string $query, string $query_count, array $params = [], string $order = 'id ASC', int $per_page = 20): array
    {
        $pagination = new Pagination(
            $this->table,
            $query,
            $query_count,
            $params,
            $order,
            $per_page
        );
        $entities = $pagination->get_entities($this->entity, $this->table);
        return [$pagination, $entities];
    }

    /**
     * @return PDOStatement|AbstractEntity|array|bool
     */
    public function execute_query(
        string $sql_query,
        array $params = [],
        string $message = "Aucun enregistrement n'a été trouvé",
        ?string $method = 'fetchAll',
        ?array $mode = []
    ): object|array|bool {
        if (empty($mode)):
            $mode = [PDO::FETCH_CLASS, $this->entity];
        endif;
        $query = $this->pdo->prepare($sql_query);
        $query->execute($params);
        if (!is_null($method) && !is_null($mode)):
            call_user_func_array([$query, 'setFetchMode'], $mode);
            $result = call_user_func_array([$query, $method], []);
            if ($result === false):
                throw new Exception($message);
            endif;
            return $result;
        endif;
        return $query;
    }

    public function hydrate(AbstractEntity $entity, array $datas, array $keys): void
    {
        foreach ($keys as $key):
            $method = 'set_' . $key;
            $entity->$method($datas[$key]);
        endforeach;
    }

    /**
     * Check if value exist in table to avoid duplicate
     * @param string $field Table column
     * @param mixed $value Field value to check
     */
    public function exist(string $field, mixed $value, mixed $exception = null): bool
    {
        $sql = "SELECT COUNT($field) FROM nk_$this->table WHERE $field = :value";
        $params = compact('value');
        if ($exception !== null):
            $sql .= " AND $field != :exception";
            $params['exception'] = $exception;
        endif;
        $query = $this->pdo->prepare($sql);
        $query->execute($params);
        return $query->fetchColumn() > 0;
    }
}