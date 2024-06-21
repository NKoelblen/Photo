<?php

namespace App\Repository;

use App\DataBase\DBConnection;
use App\Entity\AbstractEntity;
use App\URL;
use PDO;

class Pagination
{
    private PDO $pdo;
    private string $table;
    private object $repository;
    private string $query;
    private string $query_count;
    private array $params;
    private string $order;
    private int $per_page;
    private ?int $count = null;
    private ?array $entities = null;

    public function __construct(
        string $table,
        string $query,
        string $query_count,
        array $params,
        string $order = 'id ASC',
        int $per_page = 20
    ) {
        $this->pdo = DBConnection::get_pdo();
        $this->table = $table;
        $repository = 'App\Repository\\' . ucfirst($table) . 'Repository';
        $this->repository = new $repository();
        $this->query = $query;
        $this->query_count = $query_count;
        $this->params = $params;
        $this->order = htmlentities($order);
        $this->per_page = $per_page;
        if ($this->count === null):
            $this->count = (int) $this->repository->execute_query(
            sql_query: $this->query_count,
            params: $this->params,
            method: 'fetch',
            mode: [PDO::FETCH_NUM]
            )[0];
        endif;

    }

    /**
     * @return AbstractEntity[]
     */
    public function get_entities(string $entity, string $table, ?string $message = null): ?array
    {
        if ($this->entities === null):
            if ($this->get_current_page() > $this->get_pages() && $this->get_pages() !== 0):
                if ($this->get_pages() > 1):
                    $_GET['page'] = $this->get_pages();
                else:
                    unset($_GET['page']);
                endif;
                header('Location: ' . $_SERVER['REDIRECT_URL'] . (!empty($_GET) ? '?' . http_build_query($_GET) : ''));
            endif;

            $offset = $this->per_page * ($this->get_current_page() - 1);
            $this->query .= " ORDER BY $this->order LIMIT $this->per_page OFFSET $offset";
            $this->entities = $this->repository->fetch_entities(
                sql_query: $this->query,
                params: $this->params
            );
        endif;
        return $this->entities;
    }

    public function get_count(): ?int
    {
        return $this->count;
    }

    public function get_per_page(): ?int
    {
        return $this->per_page;
    }

    public function get_current_page(): int
    {
        return URL::get_positif_int('page', 1);
    }

    public function get_pages(): int
    {
        return ceil($this->count / $this->per_page);
    }

    public function previous_link(string $link): ?string
    {
        $link .= '?' . http_build_query(array_merge($this->query_string(), $this->get_current_page() > 2 ? ['page' => $this->get_current_page() - 1] : []));
        return $link;
    }

    public function number_link(string $link, int $number): ?string
    {
        $query_string = http_build_query(array_merge($this->query_string(), $number !== 1 ? ['page' => $number] : []));
        return "$link?$query_string";
    }

    public function next_link(string $link): ?string
    {
        $query_string = http_build_query(array_merge($this->query_string(), ['page' => $this->get_current_page() + 1]));
        return "$link?$query_string";
    }

    private function query_string(): array
    {
        $datas = $_GET;
        if (isset($datas['page'])):
            unset($datas['page']);
        endif;
        $status = ['published', 'draft', 'trashed', 'delete'];
        foreach ($status as $stat):
            if (isset($datas[$stat])):
                unset($datas[$stat]);
            endif;
        endforeach;
        return $datas;
    }
}