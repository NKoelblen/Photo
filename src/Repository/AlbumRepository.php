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

    public function create_album(array $datas): int
    {
        $fields = [];
        foreach ($datas as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $set = implode(', ', $fields);
        $query = $this->pdo->prepare("INSERT INTO nk_$this->table SET $set");
        $create = $query->execute($datas);
        if ($create === false):
            throw new Exception("Impossible de créer une publication dans la table $this->table.");
        endif;
        return $this->pdo->lastInsertId();
    }

    public function update_album(array $ids, array $datas, string $message = null): void
    {
        $fields = [];
        foreach ($datas as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $set = implode(', ', $fields);
        $in = "";
        $ids_params = [];
        foreach ($ids as $id):
            $key = ":id" . $id;
            $in .= ($in ? ", " : "") . $key;
            $ids_params[$key] = $id;
        endforeach;
        $ids_list = implode(', ', $ids);
        $query = $this->pdo->prepare("UPDATE nk_$this->table SET $set WHERE id IN ($in)");
        $edit = $query->execute(array_merge($datas, $ids_params));
        if ($edit === false):
            throw new Exception($message ?: "Impossible de modifier les publications $ids_list dans la table $this->table.");
        endif;
    }

    public function delete_album(array $ids): void
    {
        $in = "";
        $params = [];
        foreach ($ids as $id):
            $key = ":id" . $id;
            $in .= ($in ? ", " : "") . $key;
            $params[$key] = $id;
        endforeach;
        $list = implode(', ', $ids);
        $query = $this->pdo->prepare("DELETE FROM nk_$this->table WHERE id IN ($in)");
        $delete = $query->execute($params);
        if ($delete === false):
            throw new Exception("Impossible de supprimer les publisations $list de la table $this->table.");
        endif;
    }
    public function find_album(string $field, mixed $value): AlbumEntity
    {
        $field = htmlentities($field);
        $query = $this->pdo->prepare(
            "SELECT *
             FROM nk_$this->table
             WHERE status = 'published'
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
    public function find_paginated_albums(string $status = 'published', string $order = 'title ASC', int $per_page = 20): array
    {
        $order = htmlentities($order);
        $pagination = new Pagination(
            "SELECT *
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

    public function find_allowed_album(string $field, mixed $value): AlbumEntity
    {
        $field = htmlentities($field);
        $query = $this->pdo->prepare(
            "SELECT *
             FROM nk_$this->table
             WHERE status = 'published'
             AND private IS NULL
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
    public function find_allowed_albums(string $order = 'title ASC', ?int $per_page = null): array
    {
        $order = htmlentities($order);
        $limit = $per_page === null ? '' : "LIMIT $per_page";
        $query = $this->pdo->prepare(
            "SELECT *
             FROM nk_$this->table
             WHERE status = 'published'
             AND private IS NULL
             ORDER BY $order
             $limit"
        );
        $query->execute();
        $entities = $query->fetchAll(PDO::FETCH_CLASS, $this->entity);
        return $entities;
    }

    /**
     * @return array[Pagination, AlbumEntity[]]
     */
    public function find_paginated_allowed_albums(string $order = 'title ASC', int $per_page = 20): array
    {
        $order = htmlentities($order);
        $pagination = new Pagination(
            "SELECT *
             FROM nk_$this->table
             WHERE status = 'published'
             AND 'private' = 0",
            "SELECT COUNT(id)
             FROM nk_$this->table
             WHERE status = 'published'
             AND 'private' = 0",
            [],
            $order,
            $per_page
        );
        $entities = $pagination->get_entities($this->entity, $this->table);
        return [$pagination, $entities];
    }
}
